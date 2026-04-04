<?php

namespace App\Services\Hrm;

use App\Models\Hrm\QrToken;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use InvalidArgumentException;

class QrTokenService
{
    /**
     * Generate a new QR token and return SVG + metadata.
     */
    public function generate(int $storeId): array
    {
        $companyId = Auth::user()->company_id;
        $expiry = (int) Setting::get('attendance_qr_expiry_seconds', 30, $companyId);

        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addSeconds($expiry);

        QrToken::create([
            'company_id' => $companyId,
            'store_id' => $storeId,
            'token' => $token,
            'generated_by' => Auth::id(),
            'expires_at' => $expiresAt,
        ]);

        $payload = json_encode([
            'token' => $token,
            'cid' => $companyId,
            'sid' => $storeId,
            'ts' => now()->timestamp,
        ]);

        $signature = hash_hmac('sha256', $payload, config('app.key'));
        $qrContent = base64_encode($payload . '|' . $signature);

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrContent);

        return [
            'qr_svg'     => (string) $qrSvg,
            'token'      => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => $expiry,
        ];
    }

    /**
     * Generate a permanent printable QR that encodes a URL to the mobile scan page.
     * No expiry — security is handled by employee login + GPS validation.
     */
    public function generatePrintable(string $scanUrl): string
    {
        $svg = QrCode::format('svg')
            ->size(400)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($scanUrl);

        return (string) $svg;
    }

    /**
     * Validate scanned QR data and return the QrToken model.
     *
     * @throws InvalidArgumentException
     */
    public function validate(string $qrData): QrToken
    {
        $decoded = base64_decode($qrData, true);
        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid QR code format.');
        }

        $parts = explode('|', $decoded, 2);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Malformed QR code data.');
        }

        [$payload, $providedSig] = $parts;

        $expectedSig = hash_hmac('sha256', $payload, config('app.key'));
        if (!hash_equals($expectedSig, $providedSig)) {
            throw new InvalidArgumentException('QR code signature verification failed.');
        }

        $data = json_decode($payload, true);
        if (!$data || !isset($data['token'], $data['cid'])) {
            throw new InvalidArgumentException('Invalid QR code payload.');
        }

        // Cross-tenant check
        if ((int) $data['cid'] !== (int) Auth::user()->company_id) {
            throw new InvalidArgumentException('This QR code does not belong to your organization.');
        }

        $qrToken = QrToken::where('token', $data['token'])
            ->where('company_id', $data['cid'])
            ->first();

        if (!$qrToken) {
            throw new InvalidArgumentException('Invalid QR code.');
        }

        if ($qrToken->is_used) {
            throw new InvalidArgumentException('This QR code has already been used.');
        }

        if ($qrToken->expires_at->isPast()) {
            throw new InvalidArgumentException('This QR code has expired. Please scan the latest one.');
        }

        return $qrToken;
    }
}
