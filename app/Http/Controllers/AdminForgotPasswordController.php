<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Support\Mailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('filament.admin.auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
        ]);

        $genericStatus = 'Bu e-posta kayıtlıysa şifre yenileme bağlantısı gönderildi.';

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return back()->with('status', $genericStatus);
        }

        $settings = Setting::current();
        $token = Password::broker('users')->createToken($user);
        $resetUrl = route('admin.password.confirm', [
            'token' => $token,
            'email' => $user->email,
        ]);

        try {
            $html = view('emails.admin-password-reset', [
                'user' => $user,
                'resetUrl' => $resetUrl,
            ])->render();

            Mailer::send(
                $user->email,
                (string) ($settings->mailer_from_name ?: 'Yönetim'),
                'Admin Şifre Yenileme Bağlantısı',
                $html,
            );
        } catch (\Throwable $exception) {
            Log::error('Admin şifre sıfırlama maili gönderilemedi.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Bağlantı oluşturuldu ancak e-posta gönderilemedi. Mailer ayarlarınızı kontrol edin.',
            ])->onlyInput('email');
        }

        return back()->with('status', $genericStatus);
    }

    public function showReset(Request $request, string $token): View|RedirectResponse
    {
        $email = (string) $request->query('email', '');

        if ($email === '' || $token === '') {
            return redirect()
                ->route('admin.password.forgot')
                ->withErrors(['email' => 'Geçersiz veya eksik şifre yenileme bağlantısı.']);
        }

        return view('filament.admin.auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifre tekrarı eşleşmiyor.',
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->password = Hash::make($password);
                $user->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => 'Bağlantı geçersiz veya süresi dolmuş. Lütfen yeniden talep edin.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('filament.admin.auth.login')
            ->with('status', 'Şifreniz güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
    }
}
