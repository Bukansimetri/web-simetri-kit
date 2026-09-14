<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Penyaring submit otomatis (bot/skrip) untuk form publik, tanpa CAPTCHA dan
 * tanpa gesekan apa pun untuk pengunjung asli.
 *
 * Dua sinyal yang dipakai:
 *
 *  1. HONEYPOT — field yang disembunyikan lewat CSS dan tidak bisa di-tab
 *     manusia. Pengisi-otomatis/bot cenderung mengisi semua input yang
 *     ditemukannya. Terisi = hampir pasti bukan manusia.
 *
 *  2. TOKEN WAKTU — waktu halaman dirender, dienkripsi server (tidak bisa
 *     dipalsukan client). Submit < MIN_FILL_SECONDS detik setelah halaman
 *     dirender berarti form tidak benar-benar dibaca/diisi manusia. Token
 *     yang hilang/rusak berarti request tidak datang dari form kami sama
 *     sekali (mis. curl langsung ke endpoint).
 *
 * Sengaja TIDAK ada batas atas umur token: pengunjung yang membiarkan tab
 * terbuka semalaman lalu submit adalah pelanggan asli, dan menolak dia jauh
 * lebih mahal daripada meloloskan bot yang memakai ulang token lama —
 * volume bot sudah ditahan rate limit per IP & per nomor telepon.
 */
class SubmissionGuard
{
    /**
     * Nama field honeypot. Terlihat wajar bagi bot, tapi tidak pernah
     * ditampilkan ke manusia.
     */
    public const HONEYPOT_FIELD = 'website';

    public const TOKEN_FIELD = 'form_token';

    /**
     * Manusia butuh lebih dari ini untuk membaca & mengisi form. Ditetapkan
     * longgar supaya pengunjung yang sangat cepat pun tidak ikut tertolak.
     */
    public const MIN_FILL_SECONDS = 3;

    /**
     * Token untuk disematkan ke form saat halaman dirender.
     */
    public static function issueToken(): string
    {
        return Crypt::encryptString((string) now()->timestamp);
    }

    /**
     * True bila submit ini kemungkinan besar bukan dari manusia. Pemanggil
     * sebaiknya membalas seolah sukses (tanpa menyimpan apa pun) agar bot
     * tidak tahu dia terdeteksi lalu mencoba variasi lain.
     */
    public function looksAutomated(Request $request): bool
    {
        if (filled($request->input(self::HONEYPOT_FIELD))) {
            $this->log($request, 'honeypot terisi');

            return true;
        }

        $token = $request->input(self::TOKEN_FIELD);

        if (blank($token) || ! is_string($token)) {
            $this->log($request, 'token waktu tidak ada');

            return true;
        }

        try {
            $renderedAt = (int) Crypt::decryptString($token);
        } catch (DecryptException) {
            $this->log($request, 'token waktu tidak valid');

            return true;
        }

        $elapsed = now()->timestamp - $renderedAt;

        if ($elapsed < self::MIN_FILL_SECONDS) {
            $this->log($request, "diisi terlalu cepat ({$elapsed} detik)");

            return true;
        }

        return false;
    }

    private function log(Request $request, string $reason): void
    {
        Log::info('Submit form ditolak sebagai otomatis', [
            'reason' => $reason,
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
