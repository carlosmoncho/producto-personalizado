<?php

namespace App\Services\OrderMessage;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio para procesar emails entrantes y asociarlos a pedidos
 */
class IncomingEmailService
{
    /**
     * Procesar email desde Amazon SES
     */
    public function processFromSes(array $sesMessage): array
    {
        $mail = $sesMessage['mail'] ?? [];
        $content = $sesMessage['content'] ?? null;

        // Extraer datos del email
        $fromAddress = $mail['source'] ?? null;
        $fromName = null;

        // Buscar nombre en commonHeaders
        if (isset($mail['commonHeaders']['from'][0])) {
            $from = $mail['commonHeaders']['from'][0];
            if (preg_match('/^(.+?)\s*<(.+)>$/', $from, $matches)) {
                $fromName = trim($matches[1], '"\'');
                $fromAddress = $matches[2];
            }
        }

        $subject = $mail['commonHeaders']['subject'] ?? 'Sin asunto';
        $messageId = $mail['commonHeaders']['messageId'] ?? null;

        // Buscar In-Reply-To o References para threading
        $inReplyTo = null;
        foreach ($mail['headers'] ?? [] as $header) {
            if (strtolower($header['name']) === 'in-reply-to') {
                $inReplyTo = trim($header['value'], '<>');
                break;
            }
            if (strtolower($header['name']) === 'references') {
                // Tomar el primer reference
                $refs = explode(' ', $header['value']);
                $inReplyTo = trim($refs[0], '<>');
            }
        }

        // Extraer cuerpo del email
        $body = $this->extractBodyFromSes($content, $sesMessage);

        return $this->processEmail(
            $fromAddress,
            $fromName,
            $subject,
            $body,
            $messageId,
            $inReplyTo
        );
    }

    /**
     * Extraer cuerpo del email de SES
     */
    protected function extractBodyFromSes(?string $content, array $sesMessage): string
    {
        // Si tenemos el contenido raw del email
        if ($content) {
            return $this->parseEmailContent($content);
        }

        // Si el email está en S3 (configuración común)
        if (isset($sesMessage['receipt']['action']['bucketName'])) {
            $bucket = $sesMessage['receipt']['action']['bucketName'];
            $key = $sesMessage['receipt']['action']['objectKey'];

            try {
                $rawEmail = Storage::disk('s3')->get($key);
                return $this->parseEmailContent($rawEmail);
            } catch (\Exception $e) {
                Log::warning('Could not fetch email from S3', [
                    'bucket' => $bucket,
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return '[Contenido del email no disponible]';
    }

    /**
     * Procesar email desde Mailgun
     */
    public function processFromMailgun(array $mailgunData): array
    {
        $fromAddress = $mailgunData['sender'] ?? $mailgunData['from'] ?? null;
        $fromName = null;

        // Mailgun puede enviar "Name <email>"
        if (isset($mailgunData['from'])) {
            if (preg_match('/^(.+?)\s*<(.+)>$/', $mailgunData['from'], $matches)) {
                $fromName = trim($matches[1], '"\'');
                $fromAddress = $matches[2];
            }
        }

        $subject = $mailgunData['subject'] ?? 'Sin asunto';
        $messageId = $mailgunData['Message-Id'] ?? null;
        $inReplyTo = $mailgunData['In-Reply-To'] ?? null;

        // Preferir texto plano para evitar HTML complejo
        $body = $mailgunData['body-plain'] ?? $mailgunData['body-html'] ?? $mailgunData['stripped-text'] ?? '';

        // Limpiar el cuerpo
        $body = $this->cleanEmailBody($body);

        return $this->processEmail(
            $fromAddress,
            $fromName,
            $subject,
            $body,
            $messageId,
            $inReplyTo ? trim($inReplyTo, '<>') : null
        );
    }

    /**
     * Procesar email manual (para pruebas o entrada manual)
     */
    public function processManual(
        string $fromEmail,
        ?string $fromName,
        string $subject,
        string $body,
        ?string $inReplyTo = null
    ): array {
        return $this->processEmail(
            $fromEmail,
            $fromName,
            $subject,
            $body,
            null,
            $inReplyTo
        );
    }

    /**
     * Procesar email y asociarlo a un pedido
     */
    protected function processEmail(
        ?string $fromAddress,
        ?string $fromName,
        string $subject,
        string $body,
        ?string $messageId,
        ?string $inReplyTo
    ): array {
        if (!$fromAddress) {
            throw new \InvalidArgumentException('Email address is required');
        }

        Log::info('Processing incoming email', [
            'from' => $fromAddress,
            'subject' => $subject,
            'in_reply_to' => $inReplyTo,
        ]);

        // Buscar el pedido asociado
        $order = $this->findAssociatedOrder($fromAddress, $subject, $inReplyTo);

        if (!$order) {
            Log::warning('Could not associate email with order', [
                'from' => $fromAddress,
                'subject' => $subject,
            ]);

            return [
                'success' => false,
                'reason' => 'no_order_found',
                'from' => $fromAddress,
                'subject' => $subject,
            ];
        }

        // Crear el mensaje entrante
        $message = OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => null,
            'direction' => 'incoming',
            'subject' => $subject,
            'body' => nl2br(e($body)), // Escapar HTML y convertir saltos de línea
            'body_format' => 'html',
            'from_email' => $fromAddress,
            'from_name' => $fromName ?? $order->customer_name,
            'to_email' => config('mail.from.address'),
            'to_name' => config('mail.from.name'),
            'message_id' => $messageId ? trim($messageId, '<>') : OrderMessage::generateMessageId(),
            'in_reply_to' => $inReplyTo,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        Log::info('Incoming email saved', [
            'message_id' => $message->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        return [
            'success' => true,
            'message_id' => $message->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ];
    }

    /**
     * Encontrar el pedido asociado al email
     */
    protected function findAssociatedOrder(
        string $fromEmail,
        string $subject,
        ?string $inReplyTo
    ): ?Order {
        // 1. Buscar por In-Reply-To (threading)
        if ($inReplyTo) {
            $originalMessage = OrderMessage::where('message_id', $inReplyTo)->first();
            if ($originalMessage) {
                return $originalMessage->order;
            }
        }

        // 2. Buscar número de pedido en el asunto (ORD-2025-000001)
        if (preg_match('/ORD-\d{4}-\d{6}/', $subject, $matches)) {
            $order = Order::where('order_number', $matches[0])->first();
            if ($order) {
                return $order;
            }
        }

        // 3. Buscar por email del cliente (último pedido)
        $order = Order::where('customer_email', $fromEmail)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($order) {
            return $order;
        }

        // 4. Buscar email similar (case insensitive)
        $order = Order::whereRaw('LOWER(customer_email) = ?', [strtolower($fromEmail)])
            ->orderBy('created_at', 'desc')
            ->first();

        return $order;
    }

    /**
     * Parsear contenido raw del email
     */
    protected function parseEmailContent(string $rawEmail): string
    {
        // Separar headers del body
        $parts = preg_split('/\r?\n\r?\n/', $rawEmail, 2);
        $body = $parts[1] ?? $rawEmail;

        // Detectar si es multipart
        if (preg_match('/Content-Type:\s*multipart/i', $parts[0] ?? '')) {
            // Extraer boundary
            if (preg_match('/boundary="?([^";\r\n]+)"?/i', $parts[0], $matches)) {
                $boundary = $matches[1];
                return $this->extractTextFromMultipart($body, $boundary);
            }
        }

        // Detectar encoding
        if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $parts[0] ?? '')) {
            $body = base64_decode($body);
        } elseif (preg_match('/Content-Transfer-Encoding:\s*quoted-printable/i', $parts[0] ?? '')) {
            $body = quoted_printable_decode($body);
        }

        return $this->cleanEmailBody($body);
    }

    /**
     * Extraer texto de email multipart
     */
    protected function extractTextFromMultipart(string $body, string $boundary): string
    {
        $parts = explode('--' . $boundary, $body);

        foreach ($parts as $part) {
            // Buscar text/plain primero
            if (preg_match('/Content-Type:\s*text\/plain/i', $part)) {
                $content = preg_split('/\r?\n\r?\n/', $part, 2);
                $text = $content[1] ?? '';

                if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $part)) {
                    $text = base64_decode($text);
                } elseif (preg_match('/Content-Transfer-Encoding:\s*quoted-printable/i', $part)) {
                    $text = quoted_printable_decode($text);
                }

                return $this->cleanEmailBody($text);
            }
        }

        // Si no hay text/plain, buscar text/html
        foreach ($parts as $part) {
            if (preg_match('/Content-Type:\s*text\/html/i', $part)) {
                $content = preg_split('/\r?\n\r?\n/', $part, 2);
                $html = $content[1] ?? '';

                if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $part)) {
                    $html = base64_decode($html);
                }

                return $this->cleanEmailBody(strip_tags($html));
            }
        }

        return $this->cleanEmailBody($body);
    }

    /**
     * Limpiar cuerpo del email
     */
    protected function cleanEmailBody(string $body): string
    {
        // Eliminar firmas de email comunes
        $body = preg_replace('/^--\s*$/m', '', $body);
        $body = preg_replace('/^Enviado desde mi .+$/im', '', $body);
        $body = preg_replace('/^Sent from .+$/im', '', $body);
        $body = preg_replace('/^Get Outlook for .+$/im', '', $body);

        // Eliminar respuestas citadas (líneas que empiezan con >)
        $lines = explode("\n", $body);
        $cleanLines = [];
        $inQuote = false;

        foreach ($lines as $line) {
            // Detectar inicio de cita
            if (preg_match('/^(>|El .+ escribió:|On .+ wrote:)/i', trim($line))) {
                $inQuote = true;
                continue;
            }

            if (!$inQuote) {
                $cleanLines[] = $line;
            }
        }

        $body = implode("\n", $cleanLines);

        // Limpiar espacios en blanco excesivos
        $body = preg_replace('/\n{3,}/', "\n\n", $body);
        $body = trim($body);

        return $body;
    }
}
