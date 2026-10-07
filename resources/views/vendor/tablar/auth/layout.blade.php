<!doctype html>
<html lang="{{ Config::get('app.locale') }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('apple-touch-icon.png') }}">

    <title>@yield('title')</title>

    <!-- CSS/JS files -->
    @if(config('tablar','vite'))
        @vite('resources/js/app.js')
    @endif
  
    {{-- Custom Stylesheets (post Tablar) --}}
    @yield('tablar_css')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fonts/tabler-icons.css') }}">

</head>
<body class=" border-top-wide border-primary d-flex flex-column">
    {{-- @include('partials.navbar') --}}
<div class="page page-center">
    @include('partials.navbar')
    @yield('content')
</div>

@yield('tablar_js')

</html>
