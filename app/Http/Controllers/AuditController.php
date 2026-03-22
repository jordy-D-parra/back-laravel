<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'No tienes permisos.');
        }

        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('audit.index', compact('logs'));
    }

    public function sessions()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'No tienes permisos.');
        }

        $sessions = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.*', 'users.name', 'users.email', 'users.role')
            ->whereNotNull('sessions.user_id')
            ->orderBy('sessions.last_activity', 'desc')
            ->get();

        return view('audit.sessions', compact('sessions'));
    }

    public function userActivity($userId)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'No tienes permisos.');
        }

        $user = User::findOrFail($userId);
        $logs = ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('audit.user-activity', compact('user', 'logs'));
    }

    public function clearSessions($userId = null)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'No tienes permisos.');
        }

        if ($userId) {
            DB::table('sessions')->where('user_id', $userId)->delete();
            $message = "Sesiones del usuario eliminadas correctamente.";
        } else {
            $currentSessionId = session()->getId();
            DB::table('sessions')->where('id', '!=', $currentSessionId)->delete();
            $message = "Todas las sesiones excepto la actual han sido eliminadas.";
        }

        return back()->with('success', $message);
    }
}
