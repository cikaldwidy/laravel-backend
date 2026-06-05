<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ trim($__env->yieldContent('title')) ? config('app.name', 'Laravel').' | '.trim($__env->yieldContent('title')) : config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Presensi">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-precomposed.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      <script src="https://cdn.tailwindcss.com"></script>
      <style>
        .user-page {
            min-height: 100dvh;
            background: linear-gradient(135deg, #f8fafc 0%, #eef6ff 48%, #f8fafc 100%);
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .user-phone {
            width: 100%;
            min-height: 100dvh;
            position: relative;
            padding-bottom: calc(6rem + env(safe-area-inset-bottom));
            background: transparent;
            overflow-x: hidden;
        }

        .user-phone > main {
            width: 100%;
            max-width: none;
            margin-left: auto;
            margin-right: auto;
            padding-left: clamp(0.75rem, 3vw, 3rem) !important;
            padding-right: clamp(0.75rem, 3vw, 3rem) !important;
        }

        .user-phone > header {
            width: 100%;
            max-width: none;
            margin-left: auto;
            margin-right: auto;
            padding-left: clamp(1rem, 3vw, 3rem) !important;
            padding-right: clamp(1rem, 3vw, 3rem) !important;
        }

        @media (max-width: 480px) {
            .user-phone main .grid.grid-cols-4,
            .user-phone main .grid.grid-cols-5 {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }

        @media (min-width: 1024px) {
            .user-phone,
            .user-bottom-nav-inner {
                max-width: none;
            }
        }

        .user-header-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-card {
            border: 1px solid rgb(226 232 240 / 0.85);
            border-radius: 1rem;
            background: rgb(255 255 255 / 0.92);
            box-shadow: 0 12px 28px rgb(15 23 42 / 0.08);
            backdrop-filter: blur(8px);
        }

        .user-soft-card {
            border-radius: 0.75rem;
            background: #f8fafc;
            padding: 0.75rem;
        }

        .user-field {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #fff;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            outline: none;
        }

        .user-field:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.18);
        }

        .user-btn-primary,
        .user-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 700;
            transition: 150ms ease;
        }

        .user-btn-primary {
            background: #1d4ed8;
            color: #fff;
            box-shadow: 0 10px 22px rgb(29 78 216 / 0.18);
        }

        .user-btn-secondary {
            background: rgb(255 255 255 / 0.8);
            color: #334155;
            border: 1px solid #fff;
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.08);
        }

        .user-bottom-nav {
            position: fixed;
            left: 0;
            bottom: 0;
            z-index: 50;
            width: 100%;
            display: flex;
            justify-content: center;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .user-bottom-nav-inner {
            width: 100%;
            height: 4rem;
            display: flex;
            align-items: center;
            justify-content: space-around;
            border-top: 1px solid rgb(226 232 240 / 0.95);
            border-radius: 1rem 1rem 0 0;
            background: rgb(255 255 255 / 0.96);
            box-shadow: 0 -12px 30px rgb(15 23 42 / 0.12);
            backdrop-filter: blur(14px);
        }

        .user-nav-link,
        .user-nav-link-active {
            text-align: center;
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .user-nav-link {
            color: #6b7280;
        }

        .user-nav-link-active {
            color: #1d4ed8;
        }
      </style>
</head>
<body class="bg-gray-100">
        @yield('content')
        @include('partials.logout-confirm-modal')
        <script>
            document.querySelectorAll('[data-auto-filter]').forEach((form) => {
                let timer;
                let isSubmitting = false;

                const submitForm = (delay = 0) => {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        if (isSubmitting) return;
                        isSubmitting = true;
                        form.requestSubmit();
                    }, delay);
                };

                form.querySelectorAll('input, select').forEach((field) => {
                    if (field.type === 'hidden') return;

                    if (field.tagName === 'SELECT' || ['date', 'month', 'checkbox', 'radio'].includes(field.type)) {
                        field.addEventListener('change', () => submitForm());
                        return;
                    }

                    field.addEventListener('input', () => submitForm(450));
                });
            });
        </script>
        @include('partials.pwa-service-worker')
</body>
</html>
