<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ trim($__env->yieldContent('title')) ? config('app.name', 'Laravel').' | '.trim($__env->yieldContent('title')) : config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
      @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-jakarta">
        @yield('content')
</body>
</html>
