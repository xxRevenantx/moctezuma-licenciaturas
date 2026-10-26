<?php

namespace App\Services\Profesores;

use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Administra expedientes docentes, NO las cuentas de acceso.
 * En particular, status de profesores y status de users son independientes.
 */
class ProfesorManagementService
{
    public function autorizar(): User
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User && $actor->can('admin.administracion'), 403);

        return $actor;
    }

    public function cuentasElegibles()
    {
        $this->autorizar();

        // Incluir cuentas pendientes/inactivas creadas por Usuarios PRO.
        return User::role('Profesor')->whereDoesntHave('profesor')
            ->orderBy('username')->get(['id', 'username', 'CURP', 'email', 'status']);
    }

    public function crear(array $data, ?UploadedFile $foto = null): Profesor
    {
        $actor = $this->autorizar();
        $ruta = null;

        try {
            $profesor = DB::transaction(function () use ($data, $foto, &$ruta) {
                $usuario = User::query()->lockForUpdate()->findOrFail((int) $data['user_id']);
                if (! $usuario->hasRole('Profesor') || Profesor::where('user_id', $usuario->id)->exists()) {
                    throw ValidationException::withMessages([
                        'user_id' => 'La cuenta ya tiene expediente docente o no conserva el rol Profesor.',
                    ]);
                }

                if ($foto) {
                    $ruta = $foto->store('profesores', 'public');
                }

                return Profesor::create([
                    'user_id' => $usuario->id,
                    'foto' => $ruta ? basename($ruta) : null,
                    'nombre' => mb_strtoupper(trim($data['nombre'])),
                    'apellido_paterno' => mb_strtoupper(trim($data['apellido_paterno'])),
                    'apellido_materno' => mb_strtoupper(trim($data['apellido_materno'] ?? '')),
                    'telefono' => trim($data['telefono'] ?? ''),
                    'perfil' => mb_strtoupper(trim($data['perfil'] ?? '')),
                    'color' => $data['color'] ?: null,
                    'status' => 'true',
                ]);
            });
        } catch (\Throwable $exception) {
            if ($ruta) {
                Storage::disk('public')->delete($ruta);
            }
            throw $exception;
        }

        Log::info('profesores.crear', ['actor_id' => $actor->id, 'profesor_id' => $profesor->id, 'user_id' => $profesor->user_id]);
        return $profesor;
    }

    public function actualizar(int $id, array $data, ?UploadedFile $foto = null): Profesor
    {
        $actor = $this->autorizar();
        $nuevaRuta = null;
        $viejaFoto = null;

        try {
            $profesor = DB::transaction(function () use ($id, $data, $foto, &$nuevaRuta, &$viejaFoto) {
                $profesor = Profesor::query()->lockForUpdate()->findOrFail($id);
                $viejaFoto = $profesor->foto;
                if ($foto) {
                    $nuevaRuta = $foto->store('profesores', 'public');
                }
                $profesor->update([
                    'nombre' => mb_strtoupper(trim($data['nombre'])),
                    'apellido_paterno' => mb_strtoupper(trim($data['apellido_paterno'])),
                    'apellido_materno' => mb_strtoupper(trim($data['apellido_materno'] ?? '')),
                    'telefono' => trim($data['telefono'] ?? ''),
                    'perfil' => mb_strtoupper(trim($data['perfil'] ?? '')),
                    'color' => $data['color'] ?: null,
                    'foto' => $nuevaRuta ? basename($nuevaRuta) : $profesor->foto,
                ]);

                return $profesor;
            });
        } catch (\Throwable $exception) {
            if ($nuevaRuta) {
                Storage::disk('public')->delete($nuevaRuta);
            }
            throw $exception;
        }

        if ($nuevaRuta && $viejaFoto && $viejaFoto !== basename($nuevaRuta)) {
            try {
                Storage::disk('public')->delete('profesores/' . basename($viejaFoto));
            } catch (\Throwable $exception) {
                // No revertir una edición válida si falla únicamente la limpieza del archivo viejo.
                Log::warning('profesores.foto.limpieza', ['profesor_id' => $id, 'error' => $exception->getMessage()]);
            }
        }

        Log::info('profesores.actualizar', ['actor_id' => $actor->id, 'profesor_id' => $id]);
        return $profesor;
    }

    /** Cambia exclusivamente la disponibilidad; no modifica users.status ni asignaciones. */
    public function cambiarDisponibilidad(array $ids, bool $disponible): int
    {
        $actor = $this->autorizar();
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (! $ids || count($ids) > 100 || min($ids) <= 0) {
            throw ValidationException::withMessages(['selected' => 'Selecciona entre 1 y 100 profesores válidos.']);
        }

        $cantidad = DB::transaction(function () use ($ids, $disponible) {
            $profesores = Profesor::query()->whereIn('id', $ids)->lockForUpdate()->get();
            if ($profesores->count() !== count($ids)) {
                throw ValidationException::withMessages(['selected' => 'La selección cambió. Actualiza los resultados.']);
            }
            foreach ($profesores as $profesor) {
                $profesor->update(['status' => $disponible ? 'true' : 'false']);
            }
            return $profesores->count();
        });

        Log::info('profesores.disponibilidad.masiva', [
            'actor_id' => $actor->id, 'cantidad' => $cantidad, 'disponibles' => $disponible,
        ]);
        return $cantidad;
    }
}
