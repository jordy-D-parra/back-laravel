<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Verificar permiso
        if (!auth()->user()->hasPermission('ver-dashboard')) {
            abort(403, 'No tienes permiso para ver el dashboard');
        }

        $usuario = Auth::user();

        // ==========================================
        // SALUDO SEGÚN LA HORA DEL DÍA
        // ==========================================
        $hora = now()->hour;
        if ($hora < 12) {
            $saludo = 'Buenos días';
        } elseif ($hora < 19) {
            $saludo = 'Buenas tardes';
        } else {
            $saludo = 'Buenas noches';
        }

        // ==========================================
        // FECHA ACTUAL EN ESPAÑOL
        // ==========================================
        Carbon::setLocale('es');
        $fechaActual = now()->translatedFormat('l, d \d\e F \d\e Y');

        // ==========================================
        // FRASE MOTIVACIONAL ALEATORIA DEL DÍA
        // ==========================================
        $fraseDelDia = $this->obtenerFraseDelDia();

        return view('dashboard', compact(
            'usuario',
            'saludo',
            'fechaActual',
            'fraseDelDia'
        ));
    }

    /**
     * Devuelve una frase motivacional relacionada con el sistema.
     * La frase cambia según el día del año (misma frase todo el día).
     */
    private function obtenerFraseDelDia(): array
    {
        $frases = [
            [
                'texto' => 'Un inventario bien gestionado es la base de una institución eficiente.',
                'autor' => 'Sistema de Gestión de Inventario',
            ],
            [
                'texto' => 'Cada equipo registrado es un paso más hacia la excelencia administrativa.',
                'autor' => 'Departamento de Informática',
            ],
            [
                'texto' => 'La organización es el reflejo de un trabajo bien hecho.',
                'autor' => 'Gobernación del Estado Yaracuy',
            ],
            [
                'texto' => 'Gestionar los recursos con responsabilidad es servir al pueblo con excelencia.',
                'autor' => 'Sistema de Gestión',
            ],
            [
                'texto' => 'Un préstamo devuelto a tiempo mantiene el flujo de trabajo sin interrupciones.',
                'autor' => 'Módulo de Préstamos',
            ],
            [
                'texto' => 'La tecnología avanza cuando se administra con criterio y orden.',
                'autor' => 'Dirección de Informática',
            ],
            [
                'texto' => 'Cada solicitud atendida es una muestra de compromiso institucional.',
                'autor' => 'Módulo de Solicitudes',
            ],
            [
                'texto' => 'Un soporte técnico oportuno previene fallas y mantiene la operatividad.',
                'autor' => 'Módulo de Soporte Técnico',
            ],
            [
                'texto' => 'El orden en el inventario es el reflejo del orden en la gestión.',
                'autor' => 'Sistema de Inventario',
            ],
            [
                'texto' => 'Registrar bien es cuidar el patrimonio de todos los yaracuyanos.',
                'autor' => 'Gobernación del Estado Yaracuy',
            ],
            [
                'texto' => 'La información bien organizada se convierte en conocimiento útil.',
                'autor' => 'Sistema de Gestión',
            ],
            [
                'texto' => 'Un sistema limpio y actualizado es un sistema confiable.',
                'autor' => 'Departamento de Informática',
            ],
            [
                'texto' => 'Cuidar los equipos es cuidar la inversión del estado.',
                'autor' => 'Sistema de Inventario',
            ],
            [
                'texto' => 'La responsabilidad compartida garantiza el éxito de cualquier sistema.',
                'autor' => 'Módulo de Préstamos',
            ],
            [
                'texto' => 'Cada registro correcto evita futuros dolores de cabeza.',
                'autor' => 'Sistema de Gestión',
            ],
            [
                'texto' => 'Trabajar en equipo con información clara multiplica los resultados.',
                'autor' => 'Gobernación del Estado Yaracuy',
            ],
            [
                'texto' => 'La tecnología al servicio del pueblo requiere orden y compromiso.',
                'autor' => 'Dirección de Informática',
            ],
            [
                'texto' => 'Gestionar con transparencia fortalece la confianza institucional.',
                'autor' => 'Sistema de Gestión',
            ],
            [
                'texto' => 'Mantener el inventario actualizado es mantener el control.',
                'autor' => 'Módulo de Inventario',
            ],
            [
                'texto' => 'Un buen registro hoy evita problemas mañana.',
                'autor' => 'Sistema de Gestión de Inventario',
            ],
            [
                'texto' => 'Atender cada reporte con prontitud es servir con calidad.',
                'autor' => 'Módulo de Soporte Técnico',
            ],
            [
                'texto' => 'El éxito del sistema depende del compromiso de cada usuario.',
                'autor' => 'Gobernación del Estado Yaracuy',
            ],
            [
                'texto' => 'La previsión y el orden son las mejores herramientas de gestión.',
                'autor' => 'Sistema de Préstamos',
            ],
            [
                'texto' => 'Cada equipo bien administrado rinde el doble de su valor.',
                'autor' => 'Sistema de Inventario',
            ],
            [
                'texto' => 'Ser ordenado es la mejor manera de optimizar los recursos.',
                'autor' => 'Departamento de Informática',
            ],
            [
                'texto' => 'La mejora continua empieza por registrar correctamente cada acción.',
                'autor' => 'Sistema de Gestión',
            ],
            [
                'texto' => 'Los datos bien gestionados son la base de las buenas decisiones.',
                'autor' => 'Módulo de Reportes',
            ],
            [
                'texto' => 'Tener control es tener la capacidad de mejorar.',
                'autor' => 'Sistema de Gestión',
            ],
            [
                'texto' => 'La excelencia administrativa empieza por pequeños detalles.',
                'autor' => 'Gobernación del Estado Yaracuy',
            ],
            [
                'texto' => 'Un sistema ordenado refleja una institución organizada.',
                'autor' => 'Dirección de Informática',
            ],
        ];

        // Determinar índice según el día del año (misma frase todo el día)
        $indice = now()->dayOfYear % count($frases);

        return $frases[$indice];
    }
}