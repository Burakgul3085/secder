<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminOtpController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $pendingUserId = (int) $request->session()->get('admin_otp_user_id');

        if ($pendingUserId <= 0) {
            return redirect()->route('filament.admin.auth.login');
        }

        return view('filament.admin.auth.verify-otp');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:4'],
        ], [
            'code.required' => 'Doğrulama kodu zorunludur.',
            'code.digits' => 'Kod 4 haneli olmalıdır.',
        ]);

        $pendingUserId = (int) $request->session()->get('admin_otp_user_id');
        $codeHash = (string) $request->session()->get('admin_otp_code_hash', '');
        $expiresAt = (string) $request->session()->get('admin_otp_expires_at', '');
        $pendingNonce = (string) $request->session()->get('admin_otp_pending_nonce', '');

        if ($pendingUserId <= 0 || $codeHash === '' || $expiresAt === '' || $pendingNonce === '') {
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['code' => 'Doğrulama oturumu bulunamadı. Lütfen tekrar giriş yapın.']);
        }

        if (now()->greaterThan(Carbon::parse($expiresAt))) {
            $request->session()->forget([
                'admin_otp_code_hash',
                'admin_otp_expires_at',
                'admin_otp_user_id',
                'admin_otp_pending_nonce',
                'admin_otp_intended_url',
                'admin_otp_email_hint',
            ]);

            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['code' => 'Doğrulama kodunun süresi doldu. Lütfen tekrar giriş yapın.']);
        }

        if (! hash_equals($codeHash, hash('sha256', (string) $request->string('code')))) {
            return back()->withErrors(['code' => 'Doğrulama kodu hatalı.'])->onlyInput('code');
        }

        $user = User::query()->find($pendingUserId);
        if (! $user) {
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['code' => 'Kullanıcı bulunamadı. Lütfen tekrar giriş yapın.']);
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        $request->session()->put('admin_otp_verified_nonce', $pendingNonce);

        $intendedUrl = (string) $request->session()->pull('admin_otp_intended_url', route('filament.admin.pages.dashboard'));

        $request->session()->forget([
            'admin_otp_code_hash',
            'admin_otp_expires_at',
            'admin_otp_user_id',
            'admin_otp_pending_nonce',
            'admin_otp_email_hint',
        ]);

        return redirect()->to($intendedUrl);
    }
}

