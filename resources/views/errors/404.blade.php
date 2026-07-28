<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sayfa Bulunamadı</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background:
                radial-gradient(1200px 500px at 20% -10%, rgba(95, 111, 155, 0.16), transparent 60%),
                radial-gradient(900px 400px at 100% 0%, rgba(51, 60, 85, 0.12), transparent 55%),
                #f8fafc;
            color: #0f172a;
            display: grid;
            place-items: center;
            padding: 1.5rem;
        }

        .card {
            width: min(680px, 100%);
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.11);
            padding: clamp(1.5rem, 4vw, 2.5rem);
            text-align: center;
        }

        .code {
            margin: 0;
            font-size: clamp(3rem, 10vw, 5.5rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #4d5c83;
            line-height: 1;
        }

        .title {
            margin: 0.75rem 0 0;
            font-size: clamp(1.35rem, 3.3vw, 2rem);
            font-weight: 700;
            color: #0f172a;
        }

        .desc {
            margin: 0.8rem auto 0;
            max-width: 44ch;
            color: #475569;
            font-size: clamp(0.95rem, 2.3vw, 1.05rem);
            line-height: 1.65;
        }

        .actions {
            margin-top: 1.4rem;
            display: flex;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #3f4c6b, #5f6f9b);
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.8rem 1.2rem;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
            box-shadow: 0 12px 26px rgba(77, 92, 131, 0.32);
        }

        .btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 16px 32px rgba(77, 92, 131, 0.38);
        }

        .btn:focus-visible {
            outline: 3px solid rgba(77, 92, 131, 0.34);
            outline-offset: 3px;
        }
    </style>
</head>
<body>
    <main class="card" role="main" aria-labelledby="not-found-title">
        <p class="code">404</p>
        <h1 id="not-found-title" class="title">Aradığınız sayfa bulunamadı</h1>
        <p class="desc">
            Bağlantı hatalı olabilir veya sayfa taşınmış olabilir. Ana sayfaya dönerek gezinmeye devam edebilirsiniz.
        </p>
        <div class="actions">
            <a href="{{ route('home') }}" class="btn">
                Ana Sayfaya Dön
            </a>
        </div>
    </main>
</body>
</html>
