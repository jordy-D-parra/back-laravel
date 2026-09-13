<?php
// app/Policies/EstadoPolicy.php

namespace App\Policies;

use App\Models\Estado;
use App\Models\Usuario;

class EstadoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermission('ver-estados');
    }

    public function view(Usuario $usuario, Estado $estado): bool
    {
        return $usuario->hasPermission('ver-estados');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermission('crear-estado');
    }

    public function update(Usuario $usuario, Estado $estado): bool
    {
        return $usuario->hasPermission('editar-estado');
    }

    public function delete(Usuario $usuario, Estado $estado): bool
    {
        return $usuario->hasPermission('eliminar-estado');
    }
}
