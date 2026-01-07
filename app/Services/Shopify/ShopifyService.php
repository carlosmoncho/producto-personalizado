<?php

namespace App\Services\Shopify;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $domain;
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiVersion;
    protected string $baseUrl;

    public function __construct()
    {
        $this->domain = config('shopify.domain');
        $this->clientId = config('shopify.client_id');
        $this->clientSecret = config('shopify.client_secret');
        $this->apiVersion = config('shopify.api_version');
        $this->baseUrl = "https://{$this->domain}/admin/api/{$this->apiVersion}";
    }

    /**
     * Get access token using client credentials (fresh token each time)
     */
    protected function getAccessToken(): string
    {
        $response = Http::asForm()->post("https://{$this->domain}/admin/oauth/access_token", [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            Log::error('Failed to obtain Shopify access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('No se pudo obtener el token de acceso de Shopify');
        }

        $data = $response->json();

        return $data['access_token'];
    }

    /**
     * Create a Draft Order in Shopify for the given order
     */
    public function createDraftOrder(Order $order): array
    {
        $accessToken = $this->getAccessToken();
        $order->load('items.product');
        $lineItems = $this->buildLineItems($order);

        $draftOrderData = [
            'draft_order' => [
                'line_items' => $lineItems,
                'customer' => [
                    'email' => $order->customer_email,
                ],
                'billing_address' => $this->buildAddress($order, 'billing'),
                'shipping_address' => $this->buildAddress($order, 'shipping'),
                'email' => $order->customer_email,
                'note' => "Pedido personalizado #{$order->order_number}",
                'tags' => 'personalizados,auto-generated',
                'use_customer_default_address' => false,
            ],
        ];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/draft_orders.json", $draftOrderData);

        if (!$response->successful()) {
            Log::error('Shopify Draft Order creation failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Error al crear el Draft Order en Shopify: ' . $response->body());
        }

        $data = $response->json();
        $draftOrder = $data['draft_order'];

        // Update order with Shopify data
        $order->update([
            'shopify_draft_order_id' => (string) $draftOrder['id'],
            'shopify_invoice_url' => $draftOrder['invoice_url'],
        ]);

        Log::info('Shopify Draft Order created', [
            'order_id' => $order->id,
            'draft_order_id' => $draftOrder['id'],
            'invoice_url' => $draftOrder['invoice_url'],
        ]);

        return [
            'draft_order_id' => $draftOrder['id'],
            'invoice_url' => $draftOrder['invoice_url'],
        ];
    }

    /**
     * Send the invoice email to the customer via Shopify
     */
    public function sendInvoice(Order $order): bool
    {
        if (!$order->shopify_draft_order_id) {
            throw new \Exception('El pedido no tiene un Draft Order de Shopify asociado');
        }

        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/draft_orders/{$order->shopify_draft_order_id}/send_invoice.json", [
            'draft_order_invoice' => [
                'to' => $order->customer_email,
                'subject' => "Enlace de pago para tu pedido #{$order->order_number}",
                'custom_message' => "Hola {$order->customer_name},\n\nTu pedido personalizado está listo. Por favor, utiliza el enlace de pago para completar la compra.\n\nGracias por confiar en Hostelking.",
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Shopify invoice send failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Error al enviar el email de factura: ' . $response->body());
        }

        $order->update([
            'shopify_invoice_sent_at' => now(),
        ]);

        Log::info('Shopify invoice sent', [
            'order_id' => $order->id,
            'email' => $order->customer_email,
        ]);

        return true;
    }

    /**
     * Build line items from order items
     */
    protected function buildLineItems(Order $order): array
    {
        $lineItems = [];
        $taxRate = $order->tax_rate ?? 21;

        foreach ($order->items as $item) {
            $title = $this->buildItemTitle($item);

            // Calculate total with VAT based on item total_price (includes extras)
            $totalWithVat = round($item->total_price * (1 + $taxRate / 100), 2);

            // Pass total as single item to avoid Shopify rounding issues
            $productName = $item->product_name ?? $item->product->name ?? 'Producto';
            $lineItems[] = [
                'title' => $productName . ' (' . number_format($item->quantity, 0, ',', '.') . ' uds) - Personalizado',
                'price' => number_format($totalWithVat, 2, '.', ''),
                'quantity' => 1,
                'requires_shipping' => true,
                'taxable' => false, // Already includes VAT
            ];
        }

        return $lineItems;
    }

    /**
     * Build a descriptive title for the item
     */
    protected function buildItemTitle($item): string
    {
        $productName = $item->product_name ?? $item->product->name ?? 'Producto personalizado';
        $parts = [$productName];

        // Add configuration details if available
        if ($item->configuration) {
            $config = is_array($item->configuration) ? $item->configuration : json_decode($item->configuration, true);

            if ($config) {
                $configParts = [];
                foreach ($config as $key => $value) {
                    if (is_array($value) && isset($value['value'])) {
                        $configParts[] = $value['value'];
                    } elseif (is_string($value)) {
                        $configParts[] = $value;
                    }
                }
                if (!empty($configParts)) {
                    $parts[] = implode(', ', array_slice($configParts, 0, 3));
                }
            }
        }

        // Add ink information if present
        if ($item->has_custom_ink && $item->custom_inks) {
            $customInks = is_array($item->custom_inks) ? $item->custom_inks : json_decode($item->custom_inks, true);
            if ($customInks && count($customInks) > 0) {
                $parts[] = count($customInks) . ' tinta(s) personalizada(s)';
            }
        }

        return implode(' - ', $parts);
    }

    /**
     * Build address array for Shopify
     */
    protected function buildAddress(Order $order, string $type): array
    {
        $addressField = $type === 'billing' ? 'billing_address' : 'shipping_address';
        $address = $order->$addressField ?? $order->customer_address;

        // Parse address string or use structured data
        if (is_array($address)) {
            return [
                'first_name' => $order->customer_name,
                'address1' => $address['street'] ?? $address['address1'] ?? '',
                'city' => $address['city'] ?? '',
                'province' => $address['province'] ?? $address['state'] ?? '',
                'zip' => $address['zip'] ?? $address['postal_code'] ?? '',
                'country' => $address['country'] ?? 'Spain',
                'phone' => $order->customer_phone,
            ];
        }

        // Simple string address
        return [
            'first_name' => $order->customer_name,
            'address1' => $address ?: 'No especificada',
            'city' => '',
            'province' => '',
            'zip' => '',
            'country' => 'Spain',
            'phone' => $order->customer_phone,
        ];
    }

    /**
     * Get draft order details from Shopify
     */
    public function getDraftOrder(string $draftOrderId): ?array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->get("{$this->baseUrl}/draft_orders/{$draftOrderId}.json");

        if (!$response->successful()) {
            return null;
        }

        return $response->json()['draft_order'] ?? null;
    }

    /**
     * Delete a draft order from Shopify
     */
    public function deleteDraftOrder(string $draftOrderId): bool
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->delete("{$this->baseUrl}/draft_orders/{$draftOrderId}.json");

        return $response->successful();
    }

    /**
     * Check if Shopify is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->domain);
    }

}
