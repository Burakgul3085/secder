<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscribeRequest;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Support\Mailer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\Exception as MailException;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        $existing = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($existing) {
            if ($existing->is_active) {
                return back()->with('newsletter_info', 'Bu adres ile zaten e-bülten aboneliğiniz bulunmaktadır.');
            }
            $existing->is_active = true;
            $existing->subscribed_at = now();
            if (empty($existing->unsubscribe_token)) {
                $existing->unsubscribe_token = Str::random(40);
            }
            $existing->save();
            $subscriber = $existing;
        } else {
            try {
                $subscriber = NewsletterSubscriber::query()->create([
                    'email' => $email,
                    'is_active' => true,
                    'subscribed_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                return back()->with('newsletter_info', 'Bu e-posta adresi zaten kayıtlı. Aktif aboneliğiniz varsa tekrar kayıt gerekmez.');
            }
        }

        $this->sendWelcomeEmail($subscriber);

        return back()->with('newsletter_success', __('app.messages.newsletter_success'));
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        $sub = NewsletterSubscriber::query()->where('unsubscribe_token', $token)->first();

        if (! $sub) {
            return redirect()->route('home')->with('newsletter_info', 'Geçersiz veya kullanılmış bağlantı.');
        }

        $sub->is_active = false;
        $sub->save();

        return redirect()->route('home')->with('newsletter_success', __('app.messages.newsletter_unsubscribe'));
    }

    private function sendWelcomeEmail(NewsletterSubscriber $subscriber): void
    {
        $settings = Setting::current();
        $notify = $settings->mailer_notification_email
            ?: $settings->email
            ?: $settings->mailer_from_address
            ?: (string) env('PHPMAILER_FROM_ADDRESS');
        if (! $notify) {
            return;
        }

        $unsubscribeUrl = route('newsletter.unsubscribe', ['token' => $subscriber->unsubscribe_token]);

        try {
            $html = view('emails.newsletter-welcome', [
                'subscriber' => $subscriber,
                'unsubscribeUrl' => $unsubscribeUrl,
            ])->render();

            Mailer::send(
                $subscriber->email,
                'Abone',
                'E-bülten kaydınız alındı',
                $html,
                $notify
            );
        } catch (MailException $e) {
            Log::error('E-bülten hoş geldin e-postası gönderilemedi.', [
                'error' => $e->getMessage(),
                'subscriber_id' => $subscriber->id,
            ]);
        }
    }
}
