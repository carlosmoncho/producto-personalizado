<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderMessage\IncomingEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para recibir emails entrantes via webhook
 *
 * Soporta:
 * - Amazon SES via SNS (Simple Notification Service)
 * - Mailgun Inbound Routing
 */
class IncomingEmailController extends Controller
{
    protected IncomingEmailService $emailService;

    public function __construct(IncomingEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Recibir notificaciones de Amazon SNS (para SES)
     */
    public function handleSns(Request $request)
    {
        $payload = $request->all();

        Log::info('SNS webhook received', ['type' => $payload['Type'] ?? 'unknown']);

        // Verificar tipo de mensaje SNS
        $messageType = $request->header('x-amz-sns-message-type') ?? ($payload['Type'] ?? null);

        // Confirmación de suscripción
        if ($messageType === 'SubscriptionConfirmation') {
            return $this->confirmSnsSubscription($payload);
        }

        // Notificación de email
        if ($messageType === 'Notification') {
            return $this->processSnsNotification($payload);
        }

        return response()->json(['status' => 'ignored', 'type' => $messageType]);
    }

    /**
     * Confirmar suscripción SNS automáticamente
     */
    protected function confirmSnsSubscription(array $payload): \Illuminate\Http\JsonResponse
    {
        $subscribeUrl = $payload['SubscribeURL'] ?? null;

        if ($subscribeUrl) {
            // Confirmar la suscripción haciendo GET a la URL
            $response = file_get_contents($subscribeUrl);
            Log::info('SNS subscription confirmed', ['url' => $subscribeUrl]);
            return response()->json(['status' => 'subscription_confirmed']);
        }

        return response()->json(['status' => 'error', 'message' => 'No SubscribeURL found'], 400);
    }

    /**
     * Procesar notificación SNS con email
     */
    protected function processSnsNotification(array $payload): \Illuminate\Http\JsonResponse
    {
        try {
            $message = $payload['Message'] ?? null;

            if (is_string($message)) {
                $message = json_decode($message, true);
            }

            if (!$message) {
                Log::warning('SNS notification without message', $payload);
                return response()->json(['status' => 'error', 'message' => 'No message found'], 400);
            }

            // Procesar según el tipo de notificación SES
            $notificationType = $message['notificationType'] ?? $message['eventType'] ?? null;

            if ($notificationType === 'Received') {
                // Email recibido - procesar
                $result = $this->emailService->processFromSes($message);
                return response()->json(['status' => 'processed', 'result' => $result]);
            }

            // Otros tipos: Bounce, Complaint, Delivery
            Log::info('SES notification', ['type' => $notificationType]);
            return response()->json(['status' => 'acknowledged', 'type' => $notificationType]);

        } catch (\Exception $e) {
            Log::error('Error processing SNS notification', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Recibir emails de Mailgun Inbound
     */
    public function handleMailgun(Request $request)
    {
        try {
            // Verificar firma de Mailgun
            if (!$this->verifyMailgunSignature($request)) {
                Log::warning('Invalid Mailgun signature');
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }

            $result = $this->emailService->processFromMailgun($request->all());

            return response()->json(['status' => 'processed', 'result' => $result]);

        } catch (\Exception $e) {
            Log::error('Error processing Mailgun webhook', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verificar firma de Mailgun
     */
    protected function verifyMailgunSignature(Request $request): bool
    {
        $apiKey = config('services.mailgun.secret');
        if (!$apiKey) {
            return true; // Sin API key, no verificar (desarrollo)
        }

        $timestamp = $request->input('timestamp');
        $token = $request->input('token');
        $signature = $request->input('signature');

        $expectedSignature = hash_hmac('sha256', $timestamp . $token, $apiKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Endpoint de prueba para desarrollo local
     */
    public function handleTest(Request $request)
    {
        $validated = $request->validate([
            'from_email' => 'required|email',
            'from_name' => 'nullable|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'in_reply_to' => 'nullable|string',
        ]);

        try {
            $result = $this->emailService->processManual(
                $validated['from_email'],
                $validated['from_name'] ?? null,
                $validated['subject'],
                $validated['body'],
                $validated['in_reply_to'] ?? null
            );

            return response()->json(['status' => 'processed', 'result' => $result]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
