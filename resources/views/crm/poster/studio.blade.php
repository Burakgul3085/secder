<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Afiş Düzenle - {{ $poster->type_label }}</title>
    @vite(['resources/css/app.css', 'resources/js/poster/poster-editor.js'])
    <style>
        body { background:#0f172a; margin:0; font-family: 'Inter', system-ui, sans-serif; }
        .studio-bar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 12px 18px;
            background: #0b1220;
            color: #e2e8f0;
            border-bottom: 1px solid #1e293b;
        }
        .studio-bar h1 {
            font-size: 15px;
            margin: 0;
            font-weight: 700;
            flex: 1 1 200px;
            min-width: 0;
            word-break: break-word;
        }
        .studio-bar a {
            color: #aebfda;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }
        .studio-wrap { padding: 18px; }
        [data-poster-toolbar] { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
        .studio-grid { display: flex; gap: 18px; align-items: flex-start; flex-wrap: wrap; }
        [data-poster-stage] {
            flex: 1 1 560px;
            min-width: 0;
            width: 100%;
            background: #f1f5f9;
            border-radius: 14px;
            padding: 14px;
            overflow: auto;
            display: flex;
            justify-content: center;
            -webkit-overflow-scrolling: touch;
        }
        [data-poster-props] {
            width: 300px;
            flex: 0 0 300px;
            background: #fff;
            border-radius: 14px;
            padding: 16px;
        }
        @media (max-width: 768px) {
            .studio-wrap { padding: 12px; }
            .studio-bar { padding: 10px 12px; }
            .studio-bar h1 { font-size: 14px; }
            [data-poster-props] { width: 100%; flex: 1 1 100%; }
            [data-poster-stage] { flex: 1 1 100%; }
        }
    </style>
</head>
<body>
    <div class="studio-bar">
        <h1>Afiş Stüdyosu — {{ $poster->type_label }} ({{ $poster->donation?->donor?->full_name }})</h1>
        <a href="{{ $config['returnUrl'] }}">Bağışa geri dön</a>
    </div>

    <div class="studio-wrap">
        <div data-poster-editor>
            <script type="application/json" data-poster-config>@json($config)</script>
            <div data-poster-toolbar></div>
            <div class="studio-grid">
                <div data-poster-stage></div>
                <div data-poster-props></div>
            </div>
        </div>
    </div>
</body>
</html>
