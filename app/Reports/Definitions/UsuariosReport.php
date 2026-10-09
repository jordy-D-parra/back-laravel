<?php

namespace App\Reports\Definitions;

use App\Models\Usuario;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class UsuariosReport extends BaseReport
{
    public function key(): string            { return "usuarios"; }
    public function title(): string          { return "Usuarios y Roles"; }
    public function description(): string    { return "Listado completo de usuarios del sistema con su rol, estado y ultimo acceso."; }
    public function category(): string       { return "usuarios"; }
    public function icon(): string           { return "users"; }
    public function pdfOrientation(): string { return "portrait"; }

    public function filters(): array
    {
        return [
            "rol_id" => [
                "type"    => "select",
                "label"   => "Rol",
                "source"  => null,
                "options" => $this->rolesOpciones(),
            ],
            "status" => [
                "type"    => "select",
                "label"   => "Estado",
                "options" => [
                    ""         => "Todos",
                    "activo"   => "Activo",
                    "inactivo" => "Inactivo",
                ],
            ],
        ];
    }

    protected function rolesOpciones(): array
    {
        $roles = \App\Models\Rol::orderBy("nombre")->get(["id", "nombre"]);
        $opciones = ["" => "Todos los roles"];
        foreach ($roles as $r) {
            $opciones[$r->id] = ucfirst($r->nombre);
        }
        return $opciones;
    }

    public function query(array $params): Builder
    {
        return Usuario::query()
            ->with([
                "rol:id,nombre",
                "trabajador:id,cedula,nombre,apellido,departamento,cargo,email",
            ])
            ->when($params["rol_id"] ?? null, fn($q, $v) => $q->where("rol_id", $v))
            ->when($params["status"] ?? null, fn($q, $v) => $q->where("status", $v))
            ->orderBy("usuario");
    }

    public function columns(): array
    {
        return [
            ["key" => "usuario",                            "label" => "Usuario", "width" => "14%"],
            ["key" => "trabajador.cedula",                  "label" => "Cedula", "width" => "10%"],
            ["key" => "trabajador.nombre",                  "label" => "Nombre", "width" => "14%"],
            ["key" => "trabajador.apellido",                "label" => "Apellido", "width" => "14%"],
            ["key" => "rol.nombre",                         "label" => "Rol", "width" => "12%"],
            ["key" => "trabajador.departamento",            "label" => "Departamento", "width" => "14%"],
            ["key" => "status",                             "label" => "Estado", "width" => "10%", "format" => "badge"],
            ["key" => "ultimo_login",                       "label" => "Ultimo Acceso", "width" => "12%", "format" => "date"],
        ];
    }

    public function summary(array $params): array
    {
        $q = $this->query($params);

        return [
            "Total usuarios"      => (clone $q)->count(),
            "Activos"             => (clone $q)->where("status", "activo")->count(),
            "Inactivos"           => (clone $q)->where("status", "inactivo")->count(),
            "Pendientes cambio"   => (clone $q)->where("must_change_password", true)->count(),
        ];
    }

    public function signatures(): array
    {
        return [
            ["role" => "Elabora", "nombre" => "Departamento de Informatica"],
            ["role" => "Revisa",  "nombre" => "Supervisor"],
        ];
    }

        public function requiredPermission(): ?string
    {
        return "ver-usuarios";
    }
}
