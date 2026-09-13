<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ActivityLog;

class AuditController extends Controller
{
    /**
     * Mostrar registro de actividad
     */
    public function index()
    {
        // Obtener logs de actividad con relación de usuario
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('audit.index', compact('logs'));
    }
    
    /**
     * Mostrar sesiones activas
     */
    public function sessions()
    {
        $sessions = DB::table('sessions')
            ->leftJoin('usuario', 'sessions.user_id', '=', 'usuario.id')
            ->select(
                'sessions.*',
                'usuario.nombre',
                'usuario.apellido',
                'usuario.cedula'
            )
            ->whereNotNull('sessions.user_id')
            ->orderBy('sessions.last_activity', 'desc')
            ->get();
        
        return view('audit.sessions', compact('sessions'));
    }
    
    /**
     * Cerrar sesión de un usuario específico
     */
    public function clearSession($userId)
    {
        // No permitir cerrar la propia sesión
        if ($userId == auth()->id()) {
            return redirect()->back()->with('error', 'No puedes cerrar tu propia sesión');
        }
        
        DB::table('sessions')
            ->where('user_id', $userId)
            ->delete();
        
        // Registrar la acción
        $this->logActivity('DELETE', 'sessions', $userId, null, null, 'Sesión cerrada manualmente');
        
        return redirect()->back()->with('success', 'Sesión cerrada correctamente');
    }
    
    /**
     * Cerrar todas las sesiones excepto la actual
     */
    public function clearAllSessions(Request $request)
    {
        $currentSessionId = $request->session()->getId();
        $currentUserId = auth()->id();
        
        $deletedSessions = DB::table('sessions')
            ->where('id', '!=', $currentSessionId)
            ->delete();
        
        // Registrar la acción
        $this->logActivity('DELETE', 'sessions', null, null, null, 'Todas las sesiones cerradas excepto la actual');
        
        return redirect()->back()->with('success', 'Todas las sesiones fueron cerradas');
    }
    
    /**
     * Función auxiliar para registrar actividad
     */
    private function logActivity($operation, $tableName, $recordId = null, $oldData = null, $newData = null, $description = null)
    {
        $user = auth()->user();
        
        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user ? $user->nombre . ' ' . $user->apellido : 'Sistema',
            'user_role' => $user?->rol?->nombre ?? 'N/A',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'operation' => $operation,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'description' => $description,
            'request_method' => request()->method(),
            'request_url' => request()->fullUrl(),
        ]);
    }
}