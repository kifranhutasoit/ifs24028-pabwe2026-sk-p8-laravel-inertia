<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="BlogVerse Pro adalah platform blog untuk membaca, menulis, dan menerbitkan artikel dengan editor Markdown dan bantuan AI.">
    <meta name="theme-color" content="#4f46e5">
    <title data-inertia>{{ config('app.name', 'BlogVerse Pro') }}</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    @inertia
</body>
</html>
