<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

/**
 * Wrapper resmi midtrans/midtrans-php.
 * Server Key hanya dipakai di sisi server — TIDAK pernah dikirim ke frontend.
 */
class MidtransService
{
    /**
     * Inisialisasi konfigurasi global midtrans-php dari config/midtrans.php.
     */
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key', '');
        Config::$clientKey    = config('midtrans.client_key', '');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$is3ds        = true;
    }

    /**
     * True jika server & client key sudah diisi di .env.
     */
    public function isConfigured(): bool
    {
        return config('midtrans.server_key', '') !== ''
            && config('midtrans.client_key', '') !== '';
    }

    /**
     * Minta snap_token ke Midtrans Snap API.
     *
     * @param array $transactionDetails ['order_id' => ..., 'gross_amount' => ...]
     * @param array $itemDetails        item_details Snap
     * @param string|null $customerName
     * @return string snap token
     */
    public function createSnapToken(array $transactionDetails, array $itemDetails, ?string $customerName = null): string
    {
        $params = [
            'transaction_details' => $transactionDetails,
            'item_details'        => $itemDetails,
        ];

        if ($customerName) {
            $params['customer_details'] = [
                'first_name' => $customerName,
            ];
        }

        $snap = Snap::createTransaction($params);

        return $snap->token;
    }

    /**
     * Re-check status transaksi langsung ke Midtrans API.
     *
     * @return array response Midtrans (transaction_status, status_code, gross_amount, payment_type, transaction_id, fraud_status, dll)
     */
    public function getStatus(string $orderId): array
    {
        return (array) Transaction::status($orderId);
    }

    /**
     * Verifikasi signature webhook Midtrans.
     * rumus: hash('sha512', order_id . status_code . gross_amount . server_key)
     */
    public function verifySignature(array $payload, ?string $signatureHeader): bool
    {
        if (!$signatureHeader) {
            return false;
        }

        $orderId     = (string) ($payload['order_id'] ?? '');
        $statusCode  = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key', ''));

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * Map transaction_status Midtrans ke status lokal transaksi.
     */
    public function mapTransactionStatus(string $status): string
    {
        return match ($status) {
            'settlement', 'capture' => 'selesai',
            'deny', 'cancel', 'expire', 'refund' => 'dibatalkan',
            default => 'pending',
        };
    }
}
