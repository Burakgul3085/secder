# Canli Veritabaniyla Yerel Calisma

Bu proje varsayilan olarak SQLite ile acilir. Canli dump dosyanizla calismak icin MySQL kullanmaniz gerekir.

## 1) MySQL kur

Windows icin en pratik secenekler:
- XAMPP (Apache + MySQL + phpMyAdmin)
- Tek basina MySQL Server 8.x

Kurulumdan sonra MySQL servisini calistirin.

## 2) SQL dump dosyasini hazirla

Masaustunde tam dump dosyaniz bulundu:

`C:\Users\90542\OneDrive\Desktop\birliktekardeslik_live_full_2026-07-28_13-45.sql`

## 3) Otomatik import scriptini calistir

Proje klasorunde PowerShell acin ve su komutu calistirin:

```powershell
powershell -ExecutionPolicy Bypass -File ".\scripts\import-live-db.ps1" -DbUser "root" -DbPassword "SIFRENIZ"
```

Notlar:
- Sifreniz yoksa `-DbPassword ""` kullanin.
- Farkli host/port varsa ekleyin: `-DbHost "127.0.0.1" -DbPort 3306`
- Farkli veritabani adi istiyorsaniz: `-DbName "secder_local"`

Script su islemleri yapar:
- MySQL'de veritabanini olusturur
- SQL dump dosyasini import eder
- `.env` dosyasini MySQL'e gore gunceller
- Laravel config/cache temizligi yapar

## 4) Uygulamayi ac

```powershell
php artisan serve
```

Gerekirse frontend:

```powershell
npm run dev
```

## 5) Dogrulama

```powershell
php artisan tinker
```

Ardindan:

```php
\App\Models\Setting::count();
```

0'dan buyuk donuyorsa import basarilidir.
