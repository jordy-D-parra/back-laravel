<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Inventario') - Gobernacion Yaracuy</title>

    @vite(['resources/js/bootstrap.js'])

    @yield('styles')
</head>
<body>
    @yield('content')

    @yield('scripts')
</body>
</html>
