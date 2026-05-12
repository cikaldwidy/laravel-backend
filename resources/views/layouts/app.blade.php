<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ trim($__env->yieldContent('title')) ? config('app.name', 'Laravel').' | '.trim($__env->yieldContent('title')) : config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      <script src="https://cdn.tailwindcss.com"></script>
      <style>
        .user-page {
            min-height: 100dvh;
            background: #ecfeff;
            display: flex;
            justify-content: center;
        }

        .user-phone {
            width: 100%;
            max-width: 24rem;
            min-height: 100dvh;
            position: relative;
            padding-bottom: calc(6rem + env(safe-area-inset-bottom));
            background: #dffcff;
            box-shadow: 0 25px 50px -12px rgb(15 23 42 / 0.25);
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
            border: 1px solid rgb(255 255 255 / 0.7);
            border-radius: 1rem;
            background: rgb(255 255 255 / 0.85);
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.06);
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
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgb(16 185 129 / 0.18);
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
            background: #047857;
            color: #fff;
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.08);
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
            max-width: 24rem;
            height: 4rem;
            display: flex;
            align-items: center;
            justify-content: space-around;
            border-top: 1px solid #e5e7eb;
            border-radius: 1rem 1rem 0 0;
            background: #fff;
            box-shadow: 0 -10px 25px rgb(15 23 42 / 0.14);
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
            color: #047857;
        }
      </style>
</head>
<body class="bg-gray-100">
        @yield('content')
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
</body>
</html>
