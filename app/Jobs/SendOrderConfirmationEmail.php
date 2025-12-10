<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Job para enviar email de confirmación de pedido
 *
 * Se ejecuta en segundo plano via Redis queue para no bloquear la respuesta API
 */
class SendOrderConfirmationEmail implements ShouldQueue
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
        public Order $order
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // TODO: Implementar el Mailable cuando se configure el servicio de email
            // Mail::to($this->order->customer_email)
            //     ->send(new OrderConfirmationMail($this->order));

            Log::info('Order confirmation email queued', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'customer_email' => $this->order->customer_email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw para que el job se reintente
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Order confirmation email job failed permanently', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
