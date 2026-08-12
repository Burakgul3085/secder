<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'site_title',
        'site_description',
        'header_tagline',
        'logo',
        'favicon',
        'phone',
        'email',
        'address',
        'google_maps_embed_url',
        'website_url',
        'donation_page_url',
        'legal_kvkk_url',
        'legal_privacy_url',
        'legal_terms_url',
        'volunteer_preferences',
        'kvkk_text',
        'volunteer_clarification_text',
        'privacy_policy_text',
        'cookie_policy_text',
        'home_focus_1_title',
        'home_focus_1_text',
        'home_focus_2_title',
        'home_focus_2_text',
        'home_focus_3_title',
        'home_focus_3_text',
        'home_about_badge',
        'home_about_title',
        'home_about_intro',
        'home_about_body',
        'home_about_items',
        'home_about_button_text',
        'home_about_image',
        'header_panel_volunteer_text',
        'social_section_title',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'tiktok_url',
        'x_url',
        'linkedin_url',
        'whatsapp_url',
        'telegram_url',
        'mailer_host',
        'mailer_port',
        'mailer_encryption',
        'mailer_username',
        'mailer_password',
        'mailer_from_address',
        'mailer_from_name',
        'mailer_notification_email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getMailerPasswordAttribute($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            // Local ortamlarda farkli APP_KEY ile gelen veriler cozulemeyebilir.
            return null;
        }
    }

    public function setMailerPasswordAttribute($value): void
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === null || $value === '') {
            $this->attributes['mailer_password'] = null;
            return;
        }

        $this->attributes['mailer_password'] = Crypt::encryptString((string) $value);
    }

    public static function current(): self
    {
        return static::query()->where('is_active', true)->latest('id')->first() ?? new self([
            'site_title' => 'Birlikte Kardeşlik Derneği',
            'site_description' => 'Birlikte iyiliği büyütüyoruz.',
            'volunteer_preferences' => "Sosyal Medya\nSaha Görevlisi\nGenel Gönüllü",
            'kvkk_text' => "Bu metin, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında gönüllü başvuru sürecinde toplanan kişisel verilerin işlenmesine ilişkin bilgilendirme amacı taşır.\n\nForm üzerinden paylaştığınız ad, soyad, e-posta, telefon ve başvuru içeriği; başvurunuzu değerlendirmek, sizinle iletişime geçmek, gönüllülük süreçlerini planlamak ve gerektiğinde yasal yükümlülükleri yerine getirmek amacıyla işlenir.\n\nKişisel verileriniz yalnızca yetkili dernek birimleri tarafından erişilebilir şekilde korunur, üçüncü kişilerle yalnızca hukuki zorunluluk veya açık rızanız bulunan hallerde paylaşılır.\n\nKVKK kapsamındaki erişim, düzeltme, silme, işleme itiraz ve benzeri taleplerinizi derneğimizin iletişim e-posta adresi üzerinden iletebilirsiniz.",
            'volunteer_clarification_text' => "Gönüllü başvuru formunu doldurarak paylaştığınız bilgilerin doğru ve güncel olduğunu kabul etmiş olursunuz.\n\nBaşvurunuz, dernek faaliyet alanları ve ihtiyaçları doğrultusunda değerlendirilir. Uygun görülen adaylarla e-posta veya telefon üzerinden iletişime geçilir.\n\nGönüllülük başvurusu bir istihdam taahhüdü niteliği taşımaz; başvuru sonucu, faaliyet takvimi ve kontenjan durumuna göre değişiklik gösterebilir.\n\nBaşvuru sürecinde paylaştığınız içerik yalnızca gönüllülük değerlendirmesi amacıyla kullanılır ve dernek gizlilik politikası çerçevesinde saklanır.",
            'privacy_policy_text' => "Bu gizlilik politikası, derneğimizin web sitesi üzerinden toplanan kişisel verilerin hangi amaçlarla işlendiğini, nasıl korunduğunu ve ilgili kişilerin haklarını açıklar.\n\nToplanan veriler; iletişim taleplerini yanıtlamak, gönüllü başvurularını değerlendirmek, e-bülten gönderimlerini yönetmek ve yasal yükümlülükleri yerine getirmek amacıyla kullanılabilir.\n\nKişisel veriler, yalnızca yetkili kişiler tarafından erişilebilir şekilde korunur; açık rıza veya yasal zorunluluk bulunmadıkça üçüncü kişilerle paylaşılmaz.\n\nKişisel verilerinizle ilgili taleplerinizi derneğimizin iletişim e-posta adresi üzerinden bize iletebilirsiniz.",
            'cookie_policy_text' => "Bu çerez politikası, web sitemizde kullanılan çerez türlerini ve çerezlerin hangi amaçlarla işlendiğini açıklar.\n\nSitemizde kullanıcı deneyimini iyileştirmek, güvenlik sağlamak, oturum yönetimini sürdürmek ve site performansını analiz etmek amacıyla çerezler kullanılabilir.\n\nTarayıcı ayarlarınız üzerinden çerez tercihlerinizi değiştirebilir veya çerezleri devre dışı bırakabilirsiniz. Ancak bazı çerezlerin kapatılması, sitenin belirli özelliklerinin düzgün çalışmamasına neden olabilir.\n\nSitemizi kullanmaya devam ederek, çerezlerin bu politika kapsamında kullanılmasını kabul etmiş olursunuz.",
            'home_focus_1_title' => 'Acil Gıda Desteği',
            'home_focus_1_text' => 'Afrika’da açlık riski altındaki ailelere temel gıda kolileri ulaştırıyoruz.',
            'home_focus_2_title' => 'Temiz Su Erişimi',
            'home_focus_2_text' => 'Susuzlukla mücadele eden bölgelerde temiz suya erişimi destekliyoruz.',
            'home_focus_3_title' => 'Beslenme Dayanışması',
            'home_focus_3_text' => 'Yemek ve içme suyu odağında düzenli insani yardım çalışmaları yürütüyoruz.',
            'home_about_badge' => 'SECDER',
            'home_about_title' => 'Biz Kimiz!',
            'home_about_intro' => 'Selahaddin Eyyubi Cami Derneği olarak Gaziantep\'te ilim, sosyal dayanışma ve cami merkezli hizmetlerle toplumumuza katkı sunuyoruz.',
            'home_about_body' => 'SECDER, Gaziantep\'te Selahaddin Eyyubi Camii etrafında şekillenen bir dernektir. İlmi eğitimler, sosyal projeler ve kardeşlik bilinciyle; gençlerin, ailelerin ve ihtiyaç sahibi komşularımızın yanında yer alıyoruz. Amacımız, kalıcı iyilik ve güçlü bir nesil inşasına katkı sağlamaktır.',
            'home_about_items' => "İlmi ve fikri eğitim çalışmaları\nCami ve sosyal dayanışma hizmetleri\nGençlik ve aile odaklı projeler\nYerel yardımlaşma ve gönüllülük",
            'home_about_button_text' => 'Hakkımızda',
            'header_panel_volunteer_text' => 'Faaliyetlerimizde sizinle birlikte hareket etmek ister misiniz? Gönüllü olarak zamanınızı ve emeğinizi paylaşarak toplumsal faydaya katkı sağlayabilirsiniz. Başvuru formu üzerinden bize ulaşın, birlikte iyiliği büyütelim.',
            'social_section_title' => 'Sosyal medyada bizi takip edin',
            'mailer_encryption' => 'tls',
            'mailer_port' => 587,
        ]);
    }

    public function volunteerPreferenceOptions(): array
    {
        $raw = (string) ($this->volunteer_preferences ?? '');

        $items = collect(preg_split('/[\r\n,]+/', $raw))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();

        if (empty($items)) {
            $items = ['Sosyal Medya', 'Saha Görevlisi', 'Genel Gönüllü'];
        }

        return $items;
    }

    public function legalText(string $field, string $fallback): string
    {
        $value = trim((string) ($this->{$field} ?? ''));

        return $value !== '' ? $value : $fallback;
    }

    /**
     * Sadece dolu sosyal medya linklerini döner.
     * Boş bırakılan platformlar sitede ikon olarak gösterilmez.
     *
     * @return array<string, array{platform: string, url: string}>
     */
    public function activeSocialLinks(): array
    {
        $map = [
            'instagram_url' => 'instagram',
            'youtube_url' => 'youtube',
            'tiktok_url' => 'tiktok',
            'facebook_url' => 'facebook',
            'x_url' => 'x',
            'linkedin_url' => 'linkedin',
            'whatsapp_url' => 'whatsapp',
            'telegram_url' => 'telegram',
            'website_url' => 'website',
        ];

        $links = [];

        foreach ($map as $field => $platform) {
            $url = trim((string) ($this->{$field} ?? ''));

            if ($url === '') {
                continue;
            }

            $links[$field] = [
                'platform' => $platform,
                'url' => $url,
            ];
        }

        return $links;
    }
}
