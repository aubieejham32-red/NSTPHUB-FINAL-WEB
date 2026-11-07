<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Render first, then deliver the credential PDF without Content-Length.
 * This avoids length-mismatch issues seen with binary PDF downloads
 * behind some reverse proxies and keeps sensitive files off disk.
 */
class StaffCredentialsPdfResponse
{
    public function download(array $data, string $filename): StreamedResponse
    {
        try {
            $bytes = Pdf::loadView('pdf.staff_credentials', $data)
                ->setPaper('letter', 'portrait')
                ->output();
        } catch (\Throwable $exception) {
            Log::error('Staff credentials PDF could not be rendered.', [
                'role' => $data['roleTitle'] ?? 'unknown',
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);

            abort(500, 'PDF generation failed. Check Railway deploy logs for Staff credentials PDF.');
        }

        // Never return HTML or error output with a .pdf extension.
        if (!is_string($bytes) || !str_starts_with($bytes, '%PDF-') ||
            !str_contains(substr($bytes, -1024), '%%EOF')) {
            Log::error('Staff credentials PDF output did not have valid PDF markers.', [
                'role' => $data['roleTitle'] ?? 'unknown',
                'size' => is_string($bytes) ? strlen($bytes) : null,
            ]);

            abort(500, 'The generated PDF is invalid. Check Railway deploy logs.');
        }

        Log::info('Staff credentials PDF rendered successfully.', [
            'role' => $data['roleTitle'] ?? 'unknown',
            'bytes' => strlen($bytes),
        ]);

        // DomPDF's download() sets Content-Length. Use a streamed response
        // so the proxy does not have to reconcile a hard-coded byte count.
        return response()->streamDownload(
            static function () use ($bytes): void {
                echo $bytes;
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }
}
