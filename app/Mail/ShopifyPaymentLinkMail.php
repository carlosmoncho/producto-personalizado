<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopifyPaymentLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $paymentUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $paymentUrl)
    {
        $this->order = $order;
        $this->paymentUrl = $paymentUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Enlace de pago para tu pedido #{$this->order->order_number} - Hostelking",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.shopify-payment-link',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
