<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SociosApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Body: { dni: string, password: string }
     */
    public function login(Request $request, SociosApi $api)
    {
        $data = $request->validate([
            'dni'      => 'required|string',
            'password' => 'required|string',
        ]);

	

        $dni  = trim($data['dni']);
        $pass = $data['password'];

        // 1) ¿Usuario local?
        $user = User::where('dni', $dni)->first();

        if ($user) {
            // Verificamos password local
            if (!Hash::check($pass, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Credenciales inválidas.'],
                ]);
            }

            // (Opcional) Refrescar datos si la API trae update_ts más nuevo
            $this->maybeRefreshFromApi($user, $api);

            return response()->json([
                'token' => $user->createToken('auth')->plainTextToken,
                'user'  => $user->fresh(),
                'fetched_from_api' => false,
                'refreshed' => (bool) $user->wasChanged(), // true si actualizó algo
            ]);
        }

        // 2) No existe local: buscamos en API
        $socio = $api->getSocioPorDni($dni);
        if (!$socio) {
            throw ValidationException::withMessages([
                'dni' => ['No se encontró el socio por DNI.'],
            ]);
        }

        // 3) Crear usuario local a partir de la API
        $attrs = $this->mapSocioToUserAttributes($socio, $dni, $pass);
        $avatarPath = $this->downloadAndStoreAvatar($api, (string)($socio['Id'] ?? $socio['socio_n'] ?? ''));
        if ($avatarPath) {
            $attrs['avatar_path'] = $avatarPath;
        }

        $user = User::create($attrs);

        return response()->json([
            'token' => $user->createToken('auth')->plainTextToken,
            'user'  => $user->fresh(),
            'fetched_from_api' => true,
        ], 201);
    }

    /**
     * GET /api/auth/me  (auth:sanctum)
     */
    public function me(Request $request)
    {
        return $request->user();
    }

    /**
     * POST /api/auth/logout  (auth:sanctum)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Si la API trae un update_ts más nuevo que api_update_ts local,
     * actualiza campos e imagen.
     */
    protected function maybeRefreshFromApi(User $user, SociosApi $api): void
    {
        $socio = $api->getSocioPorDni($user->dni);
        if (!$socio) return;

        $apiTsStr = $socio['update_ts'] ?? null; // ejemplo "2025-09-01 03:01:54"
        $apiTs = $apiTsStr ? Carbon::parse($apiTsStr) : null;

        // Si no hay update_ts en API, no refrescamos para evitar sobreescrituras innecesarias
        if (!$apiTs) return;

        $localTs = $user->api_update_ts ? Carbon::parse($user->api_update_ts) : null;
        if ($localTs && $apiTs->lte($localTs)) {
            return; // local ya está actualizado
        }

        // Mapear y actualizar
        $attrs = $this->mapSocioToUserAttributes($socio, $user->dni, null, /*isNew=*/false);

        // Re-descargar foto por si cambió
        $socioId = (string)($socio['Id'] ?? $socio['socio_n'] ?? '');
        if ($socioId !== '') {
            if ($binary = $api->fetchFotoSocio($socioId)) {
                $file = "socios/{$socioId}.jpg";
                Storage::disk('public')->put($file, $binary);
                $attrs['avatar_path'] = "storage/{$file}";
            }
        }

        $user->fill($attrs);
        $user->save();
    }

    /**
     * Mapea el payload del socio (API) a los atributos del User local.
     * Si $isNew es true, también setea password.
     */
    protected function mapSocioToUserAttributes(array $socio, string $dni, ?string $plainPassword, bool $isNew = true): array
    {
        $nombre   = trim((string)($socio['nombre'] ?? ''));
        $apellido = trim((string)($socio['apellido'] ?? ''));
        $email    = $socio['mail'] ?? null;

        $attrs = [
            'dni'          => $dni,
            'name'         => ($apellido || $nombre) ? "{$apellido}, {$nombre}" : $dni,
            'email'        => $email ?: null,
            'nombre'       => $nombre ?: null,
            'apellido'     => $apellido ?: null,
            'nacionalidad' => $socio['nacionalidad'] ?? null,
            'nacimiento'   => !empty($socio['nacimiento']) ? Carbon::parse($socio['nacimiento']) : null,
            'domicilio'    => $socio['domicilio'] ?? null,
            'localidad'    => $socio['localidad'] ?? null,
            'telefono'     => $socio['telefono'] ?? null,
            'celular'      => $socio['celular'] ?? null,
            'categoria'    => $socio['categoria'] ?? null,
            'socio_id'     => (string)($socio['Id'] ?? $socio['socio_n'] ?? null),
            'barcode'      => $socio['barcode'] ?? null,
            'estado_socio' => $socio['estado'] ?? null,
            'api_update_ts'=> ($socio['update_ts'] ?? null) ? Carbon::parse($socio['update_ts']) : now(),
        ];

        if ($isNew && $plainPassword !== null) {
            $attrs['password'] = Hash::make($plainPassword);
        }

        return $attrs;
    }

    /**
     * Descarga la imagen del socio y la guarda en /storage/app/public/socios/{id}.jpg
     * Devuelve la ruta pública "storage/socios/{id}.jpg" o null si no pudo.
     */
    protected function downloadAndStoreAvatar(SociosApi $api, string $socioId): ?string
    {
        if ($socioId === '') return null;

        if ($binary = $api->fetchFotoSocio($socioId)) {
            $file = "socios/{$socioId}.jpg";
            Storage::disk('public')->put($file, $binary);
            return "storage/{$file}";
        }
        return null;
    }
}
