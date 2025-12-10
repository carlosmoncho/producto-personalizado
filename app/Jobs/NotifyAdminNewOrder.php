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
 * Job para notificar al administrador de un nuevo pedido
 *
 * Se ejecuta en segundo plano via Redis queue
 */
class NotifyAdminNewOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos antes de fallar
     */
    public int $tries = 3;

    /**
     * Segundos a esperar antes de reintentar
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@hostelking.es');

            // TODO: Implementar el Mailable cuando se configure el servicio de email
            // Mail::to($adminEmail)
            //     ->send(new NewOrderNotificationMail($this->order));

            Log::info('Admin notification for new order queued', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'total_amount' => $this->order->total_amount,
                'admin_email' => $adminEmail,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Admin notification job failed permanently', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
