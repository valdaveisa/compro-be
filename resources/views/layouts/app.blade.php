<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ManPro') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #111418; /* Match dashboard bg */
        }
    </style>
</head>
<body>
    <div id="app">
        @if(session('success'))
            <div style="background-color: #38a169; color: white; padding: 15px; text-align: center; font-weight: bold; position: sticky; top: 0; z-index: 9999;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background-color: #e53e3e; color: white; padding: 15px; text-align: center; font-weight: bold; position: sticky; top: 0; z-index: 9999;">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
