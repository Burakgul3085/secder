<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\Setting;
use App\Support\Mailer;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnforceAdminOtpVerification
{
    public function handle(Request $request, Closure $next): Response
    {
        // Local geliştirme ortamında OTP e-posta bağımlılığını devre dışı bırak.
        if (! filter_var((string) env('ADMIN_OTP_ENABLED', true), FILTER_VALIDATE_BOOL)) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $loginNonce = (string) $request->session()->get('admin_otp_login_nonce', '');
        if ($loginNonce === '') {
            $request->session()->put('admin_otp_login_nonce', bin2hex(random_bytes(16)));
            $loginNonce = (string) $request->session()->get('admin_otp_login_nonce', '');
        }

        $verifiedNonce = (string) $request->session()->get('admin_otp_verified_nonce', '');
        if ($verifiedNonce !== '' && hash_equals($verifiedNonce, $loginNonce)) {
            return $next($request);
        }

        $pendingNonce = (string) $request->session()->get('admin_otp_pending_nonce', '');
        if (! hash_equals($pendingNonce, $loginNonce)) {
            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $codeHash = hash('sha256', $code);
            $expiresAt = now()->addMinutes(10)->toIso8601String();

            $request->session()->put([
                'admin_otp_code_hash' => $codeHash,
                'admin_otp_expires_at' => $expiresAt,
                'admin_otp_user_id' => $user->id,
                'admin_otp_pending_nonce' => $loginNonce,
                'admin_otp_intended_url' => $request->fullUrl(),
                'admin_otp_email_hint' => $user->email,
            ]);

            $settings = Setting::current();
            $otpTargetEmail = (string) (
                $settings->mailer_notification_email
                ?: $settings->mailer_from_address
                ?: $settings->email
            );

            if ($otpTargetEmail === '') {
                Auth::logout();

                return redirect()
                    ->route('filament.admin.auth.login')
                    ->withErrors([
                        'email' => 'OTP için hedef e-posta tanımlı değil. Mailer Ayarları bölümünde "Bildirim Alıcı E-posta" alanını doldurun.',
                    ]);
            }

            try {
                $html = view('emails.admin-login-otp', [
                    'user' => $user,
                    'code' => $code,
                ])->render();

                Mailer::send(
                    $otpTargetEmail,
                    (string) ($settings->mailer_from_name ?: 'Yönetim'),
                    'Yönetim Paneli Giriş Doğrulama Kodu',
                    $html,
                );
            } catch (\Throwable $exception) {
                Log::error('Admin OTP mail gönderilemedi.', [
                    'user_id' => $user->id,
                    'email' => $otpTargetEmail,
                    'error' => $exception->getMessage(),
                ]);

                $request->session()->forget([
                    'admin_otp_code_hash',
                    'admin_otp_expires_at',
                    'admin_otp_user_id',
                    'admin_otp_pending_nonce',
                    'admin_otp_intended_url',
                    'admin_otp_email_hint',
                ]);

                Auth::logout();

                return redirect()
                    ->route('filament.admin.auth.login')
                    ->withErrors([
                        'email' => 'Doğrulama kodu e-postası gönderilemedi. Mailer Ayarları bilgilerini kontrol edin (SMTP doğrulaması başarısız).',
                    ]);
            }
        }

        Auth::logout();

        return redirect()->route('admin.otp.form');
    }
}

