<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Reporte' }}</title>
    @include('reportes.pdf._styles', ['orientacion' => $orientacion ?? 'letter portrait'])
</head>
<body>
    @include('reportes.pdf._header')

    @yield('contenido')

    @include('reportes.pdf._footer')
</body>
</html>