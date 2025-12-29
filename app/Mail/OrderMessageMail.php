<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public OrderMessage $message
    ) {
        $this->order = $message->order;
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                $this->message->from_email,
                $this->message->from_name ?? config('mail.from.name')
            ),
            replyTo: [
                new Address(
                    config('mail.from.address'),
                    config('mail.from.name')
                ),
            ],
            subject: $this->message->subject,
        );
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        $headers = new Headers(
            messageId: $this->message->message_id,
        );

        // Añadir In-Reply-To si es una respuesta
        if ($this->message->in_reply_to) {
            $headers->text([
                'In-Reply-To' => $this->message->in_reply_to,
                'References' => $this->message->in_reply_to,
            ]);
        }

        return $headers;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-message',
            with: [
                'messageBody' => $this->message->body,
                'order' => $this->order,
                'customerName' => $this->order->customer_name,
                'orderNumber' => $this->order->order_number,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->message->hasAttachments()) {
            foreach ($this->message->attachments as $file) {
                if (Storage::disk('public')->exists($file['path'])) {
                    $attachments[] = Attachment::fromStorageDisk('public', $file['path'])
                        ->as($file['name'])
                        ->withMime($file['mime'] ?? 'application/octet-stream');
                }
            }
        }

        return $attachments;
    }
}
