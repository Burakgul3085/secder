param(
    [string]$ProjectRoot = "C:\Users\90542\OneDrive\Desktop\secder\birliktekardeslik",
    [string]$DumpFile = "C:\Users\90542\OneDrive\Desktop\birliktekardeslik_live_full_2026-07-28_13-45.sql",
    [string]$DbHost = "127.0.0.1",
    [int]$DbPort = 3306,
    [string]$DbName = "birliktekardeslik",
    [string]$DbUser = "root",
    [string]$DbPassword = ""
)

$ErrorActionPreference = "Stop"

function Find-MySqlCli {
    $candidates = @(
        "mysql",
        "C:\xampp\mysql\bin\mysql.exe",
        "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe",
        "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe"
    )

    foreach ($candidate in $candidates) {
        if ($candidate -eq "mysql") {
            $cmd = Get-Command mysql -ErrorAction SilentlyContinue
            if ($cmd) {
                return $cmd.Source
            }
        } elseif (Test-Path $candidate) {
            return $candidate
        }
    }

    throw "MySQL istemcisi bulunamadi. XAMPP/MySQL kurup tekrar deneyin."
}

function Update-EnvFile {
    param(
        [string]$EnvPath
    )

    $content = Get-Content $EnvPath -Raw
    $content = [regex]::Replace($content, "(?m)^DB_CONNECTION=.*$", "DB_CONNECTION=mysql")
    $content = [regex]::Replace($content, "(?m)^#?\s*DB_HOST=.*$", "DB_HOST=$DbHost")
    $content = [regex]::Replace($content, "(?m)^#?\s*DB_PORT=.*$", "DB_PORT=$DbPort")
    $content = [regex]::Replace($content, "(?m)^#?\s*DB_DATABASE=.*$", "DB_DATABASE=$DbName")
    $content = [regex]::Replace($content, "(?m)^#?\s*DB_USERNAME=.*$", "DB_USERNAME=$DbUser")
    $content = [regex]::Replace($content, "(?m)^#?\s*DB_PASSWORD=.*$", "DB_PASSWORD=$DbPassword")
    Set-Content -Path $EnvPath -Value $content -Encoding UTF8
}

if (!(Test-Path $ProjectRoot)) {
    throw "Proje yolu bulunamadi: $ProjectRoot"
}

if (!(Test-Path $DumpFile)) {
    throw "SQL dump dosyasi bulunamadi: $DumpFile"
}

$mysqlCli = Find-MySqlCli
$envPath = Join-Path $ProjectRoot ".env"

if (!(Test-Path $envPath)) {
    Copy-Item (Join-Path $ProjectRoot ".env.example") $envPath -Force
}

Write-Host "MySQL istemcisi: $mysqlCli"
Write-Host "Dump dosyasi: $DumpFile"
Write-Host "Veritabani hazirlaniyor: $DbName"

& $mysqlCli --host="$DbHost" --port="$DbPort" --user="$DbUser" --password="$DbPassword" --execute="CREATE DATABASE IF NOT EXISTS \`$DbName\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cmd /c "`"$mysqlCli`" --host=$DbHost --port=$DbPort --user=$DbUser --password=$DbPassword $DbName < `"$DumpFile`""

Update-EnvFile -EnvPath $envPath

Set-Location $ProjectRoot
php artisan config:clear | Out-Null
php artisan cache:clear | Out-Null

Write-Host ""
Write-Host "Tamamlandi."
Write-Host "Proje artik MySQL veritabani ile calisacak."
Write-Host "Test icin: php artisan serve"
