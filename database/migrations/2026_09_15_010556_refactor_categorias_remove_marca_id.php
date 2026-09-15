<?php
// database/migrations/2026_09_15_010556_refactor_categorias_remove_marca_id.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // 1. CONSOLIDAR CATEGORÍAS DUPLICADAS (por nombre)
        //    Solo si la columna marca_id existe todavía
        // ============================================================
        if (Schema::hasColumn('categorias', 'marca_id')) {
            $this->consolidarCategoriasDuplicadas();
        }

        // ============================================================
        // 2. ELIMINAR la constraint UNIQUE COMPUESTA si existe
        // ============================================================
        if ($this->constraintExists('categorias', 'categorias_nombre_marca_unique')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->dropUnique('categorias_nombre_marca_unique');
            });
        }

        // ============================================================
        // 3. ELIMINAR FK de marca_id si existe
        // ============================================================
        if (Schema::hasColumn('categorias', 'marca_id')) {
            // En PostgreSQL, primero se debe soltar la FK
            $foreignKeys = $this->getForeignKeys('categorias');
            foreach ($foreignKeys as $fk) {
                if (in_array('marca_id', $fk['columns'])) {
                    Schema::table('categorias', function (Blueprint $table) use ($fk) {
                        $table->dropForeign($fk['name']);
                    });
                }
            }
        }

        // ============================================================
        // 4. ELIMINAR la columna marca_id si existe
        // ============================================================
        if (Schema::hasColumn('categorias', 'marca_id')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->dropColumn('marca_id');
            });
        }

        // ============================================================
        // 5. RESTAURAR UNIQUE en nombre si no existe todavía
        // ============================================================
        if (!$this->constraintExists('categorias', 'categorias_nombre_unique')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->unique('nombre', 'categorias_nombre_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            if ($this->constraintExists('categorias', 'categorias_nombre_unique')) {
                $table->dropUnique('categorias_nombre_unique');
            }

            if (!Schema::hasColumn('categorias', 'marca_id')) {
                $table->foreignId('marca_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('marcas')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Consolida categorías con el mismo nombre (normalizado),
     * repuntando los modelos a la categoría "superviviente".
     */
    private function consolidarCategoriasDuplicadas(): void
    {
        $categorias = DB::table('categorias')
            ->orderBy('id')
            ->get(['id', 'nombre']);

        if ($categorias->isEmpty()) {
            return;
        }

        $grupos = [];
        foreach ($categorias as $cat) {
            $clave = mb_strtolower(trim($cat->nombre));
            if (!isset($grupos[$clave])) {
                $grupos[$clave] = [];
            }
            $grupos[$clave][] = $cat;
        }

        foreach ($grupos as $clave => $items) {
            if (count($items) <= 1) {
                continue;
            }

            $superviviente = $items[0];
            $duplicadas = array_slice($items, 1);

            foreach ($duplicadas as $dup) {
                DB::table('modelos')
                    ->where('categoria_id', $dup->id)
                    ->update(['categoria_id' => $superviviente->id]);

                DB::table('categorias')->where('id', $dup->id)->delete();
            }
        }
    }

    /**
     * Verifica si una constraint existe en una tabla (PostgreSQL).
     */
    private function constraintExists(string $table, string $constraint): bool
    {
        $result = DB::selectOne("
            SELECT 1
            FROM pg_constraint
            WHERE conname = ?
            AND conrelid = ?::regclass
        ", [$constraint, $table]);

        return $result !== null;
    }

    /**
     * Obtiene las foreign keys de una tabla (PostgreSQL).
     */
    private function getForeignKeys(string $table): array
    {
        $results = DB::select("
            SELECT
                tc.constraint_name AS name,
                kcu.column_name AS column
            FROM information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
            WHERE tc.table_name = ?
                AND tc.constraint_type = 'FOREIGN KEY'
        ", [$table]);

        $grouped = [];
        foreach ($results as $row) {
            if (!isset($grouped[$row->name])) {
                $grouped[$row->name] = ['name' => $row->name, 'columns' => []];
            }
            $grouped[$row->name]['columns'][] = $row->column;
        }

        return array_values($grouped);
    }
};