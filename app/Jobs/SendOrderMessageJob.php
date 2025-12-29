<?php

namespace App\Jobs;

use App\Mail\OrderMessageMail;
use App\Models\OrderMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job para enviar mensajes de pedido al cliente
 *
 * Se ejecuta en segundo plano via Redis queue para no bloquear la respuesta
 */
class SendOrderMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos antes de fallar
     */
    public int $tries = 3;

    /**
     * Segundos a esperar antes de reintentar
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public OrderMessage $message
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $order = $this->message->order;

            Log::info('Sending order message email', [
                'message_id' => $this->message->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'to_email' => $this->message->to_email,
                'subject' => $this->message->subject,
            ]);

            // Enviar el email
            Mail::to($this->message->to_email, $this->message->to_name)
                ->send(new OrderMessageMail($this->message));

            // Actualizar estado del mensaje
            $this->message->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            Log::info('Order message email sent successfully', [
                'message_id' => $this->message->id,
                'order_number' => $order->order_number,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order message email', [
                'message_id' => $this->message->id,
                'order_id' => $this->message->order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Actualizar estado con error (pero solo si es el último intento)
            if ($this->attempts() >= $this->tries) {
                $this->message->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e; // Re-throw para que el job se reintente
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Order message job failed permanently', [
            'message_id' => $this->message->id,
            'order_id' => $this->message->order_id,
            'order_number' => $this->message->order->order_number ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);

        $this->message->update([
            'status' => 'failed',
            'error_message' => 'Error permanente: ' . $exception->getMessage(),
        ]);
    }
}
