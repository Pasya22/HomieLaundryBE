<?php
// app/Services/WhatsAppService.php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // Configure your WhatsApp API (contoh: WhatsApp Business API, Twilio, dll)
        $this->apiUrl = config('services.whatsapp.url');
        $this->apiKey = config('services.whatsapp.key');
    }

    /**
     * Send invoice to customer via WhatsApp
     */
    public function sendInvoice(Order $order)
    {
        try {
            $customer = $order->customer;
            $phone    = $this->formatPhoneNumber($customer->phone);

            if (! $phone) {
                Log::error('WhatsApp: Nomor telepon tidak valid', ['order_id' => $order->id]);
                return false;
            }

            $message = $this->buildInvoiceMessage($order);

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->asForm()->post($this->apiUrl, [
                'target'  => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $order->update(['whatsapp_sent' => true]);
                Log::info('WhatsApp invoice sent successfully', ['order_id' => $order->id]);
                return true;
            }

            Log::error('WhatsApp API error', ['response' => $response->body()]);
            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp service error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send ready for pickup notification
     */
    public function sendReadyNotification(Order $order)
    {
        try {
            $customer = $order->customer;
            $phone    = $this->formatPhoneNumber($customer->phone);

            if (! $phone) {
                return false;
            }

            $message = $this->buildReadyMessage($order);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post($this->apiUrl, [
                'to'      => $phone,
                'message' => $message,
                'type'    => 'text',
            ]);

            if ($response->successful()) {
                $order->update(['ready_notification_sent' => true]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp ready notification error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Build invoice message
     */
    private function buildInvoiceMessage(Order $order)
    {
        $customer = $order->customer;

        $message = "🚀 *HOMIE LAUNDRY - INVOICE* 🚀\n";
        $message .= "No. Order: *{$order->order_number}*\n";
        $message .= "Customer: *{$customer->name}*\n";
        $message .= "Tanggal: *{$order->created_at->format('d/m/Y H:i')}*\n\n";

        $message .= "*Detail Layanan:*\n";
        foreach ($order->orderItems as $item) {
            $service = $item->service;
            $message .= "• {$service->name} ({$service->duration})";

            if ($item->weight) {
                $message .= " - {$item->weight} kg";
            } else {
                $message .= " - {$item->quantity}x";
            }

            $message .= " - Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $message .= "\n*Total: Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n";
        $message .= "Status Bayar: *" . ($order->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR') . "*\n";

        if ($order->estimated_completion) {
            $message .= "Estimasi Selesai: *{$order->estimated_completion->format('d/m/Y H:i')}*\n";
        }

        $message .= "\nTerima kasih atas kepercayaan Anda! 🙏\n";
        $message .= "Simpan pesan ini sebagai bukti order.";

        return $message;
    }

    /**
     * Build ready for pickup message
     */
    private function buildReadyMessage(Order $order)
    {
        $message = "🎉 *HOMIE LAUNDRY - SIAP DIAMBIL* 🎉\n";
        $message .= "No. Order: *{$order->order_number}*\n";
        $message .= "Customer: *{$order->customer->name}*\n\n";

        $message .= "Laundry Anda sudah siap untuk diambil!\n";
        $message .= "Total: *Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n\n";

        $message .= "📍 *Alamat Pickup:*\n";
        $message .= "Jalan Juyagalin J Alpol No 4\n";
        $message .= "Jam Operasional: 08:00 - 20:00\n\n";

        $message .= "Terima kasih! 😊";

        return $message;
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatPhoneNumber($phone)
    {
        if (! $phone) {
            return null;
        }

        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with country code (Indonesia)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}
