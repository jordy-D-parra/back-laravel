<table class="pdf-header">
    <tr>
        <td class="td-logo">
            <img src="{{ public_path('images/gobierno.jpeg') }}" alt="Gobierno">
            <span class="logo-label">Gobierno<br>Bolivariano</span>
        </td>
        <td class="td-center">
            <div class="titulo-pais">República Bolivariana de Venezuela</div>
            <div class="titulo-gobierno">Gobernación del Estado Yaracuy</div>
            <div class="titulo-depto">Dirección de Informática</div>
            <div class="titulo-reporte">{{ $titulo }}</div>
        </td>
        <td class="td-logo">
            <img src="{{ public_path('images/escudo-yaracuy.jpeg') }}" alt="Escudo">
            <span class="logo-label">Gobernación<br>del Estado Yaracuy</span>
        </td>
    </tr>
</table>

<table class="pdf-meta">
    <tr>
        <td><strong>Generado:</strong> {{ $fecha_generacion ?? now()->format('d/m/Y H:i') }}</td>
        <td class="td-right"><strong>Por:</strong> {{ $usuario_genero ?? (auth()->user()->nombre_completo ?? 'Sistema') }}</td>
    </tr>
</table>

@if(!empty($filtrosAplicados))
<div class="pdf-filtros">
    <strong>Filtros aplicados:</strong> {{ $filtrosAplicados }}
</div>
@endif