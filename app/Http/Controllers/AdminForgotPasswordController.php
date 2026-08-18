<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Support\Mailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('filament.admin.auth.forgot-password');
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Bu e-posta ile aktif bir admin hesabı bulunamadı.',
            ])->onlyInput('email');
        }

        $settings = Setting::current();
        $targetEmail = (string) (
            $settings->mailer_notification_email
            ?: $settings->mailer_from_address
            ?: $settings->email
        );

        if ($targetEmail === '') {
            return back()->withErrors([
                'email' => 'Mailer hedef e-postası tanımlı değil. Önce Mailer Ayarları bölümünü doldurun.',
            ])->onlyInput('email');
        }

        $newPassword = Str::upper(Str::random(10));
        $user->password = Hash::make($newPassword);
        $user->save();

        try {
            $html = view('emails.admin-password-reset', [
                'user' => $user,
                'requestedEmail' => $validated['email'],
                'newPassword' => $newPassword,
            ])->render();

            Mailer::send(
                $targetEmail,
                (string) ($settings->mailer_from_name ?: 'Yönetim'),
                'Admin Şifre Sıfırlama Bilgisi',
                $html,
            );
        } catch (\Throwable $exception) {
            Log::error('Admin şifre sıfırlama maili gönderilemedi.', [
                'user_id' => $user->id,
                'target_email' => $targetEmail,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Yeni şifre oluşturuldu ancak e-posta gönderilemedi. Mailer ayarlarınızı kontrol edin.',
            ])->onlyInput('email');
        }

        return redirect()
            ->route('filament.admin.auth.login')
            ->with('status', 'Yeni şifre oluşturuldu. Bilgiler mail adresinize gönderildi.');
    }
}

