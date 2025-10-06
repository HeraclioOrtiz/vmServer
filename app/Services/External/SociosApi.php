<?php

namespace App\Services\External;

use App\Contracts\SociosApiInterface;
use App\Exceptions\ApiConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SociosApi implements SociosApiInterface
{
    protected string $base;
    protected string $login;
    protected string $token;
    protected string $imgBase;
    protected int $timeout;
    protected bool $verify;

    public function __construct()
    {
        $cfg = config('services.socios');

        $this->base    = rtrim($cfg['base'] ?? '', '/');   // https://clubvillamitre.com/api_back_socios
        $this->login   = (string)($cfg['login'] ?? '');
        $this->token   = (string)($cfg['token'] ?? '');
        $this->imgBase = rtrim($cfg['img_base'] ?? '', '/');
        $this->timeout = (int)($cfg['timeout'] ?? 15);
        $this->verify  = (bool)($cfg['verify'] ?? true);   // en DEV podés setear false
    }

    public function getSocioPorDni(string $dni): ?array
    {
        if ($this->base === '' || $this->login === '' || $this->token === '') {
            Log::error('SociosApi: config incompleta', [
                'base' => $this->base,
                'login' => $this->login,
                'token_len' => strlen($this->token),
            ]);
            throw new ApiConnectionException('SociosApi configuration is incomplete');
        }

        $url = "{$this->base}/get_socio";

        // 1) Primer intento: x-www-form-urlencoded
        $resp = Http::withOptions([
                'timeout' => $this->timeout,
                'verify'  => $this->verify,   // si hay problemas SSL, poné false en .env -> SERVICES.SOCIOS.VERIFY
            ])
            ->asForm()
            ->withHeaders([
                'Authorization' => $this->token,
                'Login'         => $this->login,
            ])
            ->post($url, ['dni' => $dni]);

        $json = $this->decodeAndLog('form', $url, $resp);
        $result = $this->extractResult($json);
        if ($result) return $result;

        // 2) Segundo intento: multipart/form-data (algunas APIs lo exigen)
        $resp2 = Http::withOptions([
                'timeout' => $this->timeout,
                'verify'  => $this->verify,
            ])
            ->asMultipart()
            ->withHeaders([
                'Authorization' => $this->token,
                'Login'         => $this->login,
            ])
            ->post($url, [
                ['name' => 'dni', 'contents' => $dni],
            ]);

        $json2 = $this->decodeAndLog('multipart', $url, $resp2);
        $result2 = $this->extractResult($json2);
        return $result2;
    }

    protected function extractResult(?array $json): ?array
    {
        if (!$json) return null;

        // NUEVA LÓGICA: Solo estado "0" es válido según documentación
        // { estado:"0", result:{...}, msg:"Proceso OK" }
        // Otros estados contienen errores en el campo 'msg'
        if (isset($json['estado']) && $json['estado'] === "0") {
            if (!empty($json['result']) && is_array($json['result'])) {
                // Verificar que tenga datos reales del socio (no solo campos nulos)
                $result = $json['result'];
                
                // Un socio válido debe tener al menos ID y nombre/apellido
                if (!empty($result['Id']) && (!empty($result['nombre']) || !empty($result['apellido']))) {
                    return $result;
                }
                
                // Si solo tiene campos nulos/vacíos, es una respuesta vacía
                Log::info('SociosApi: respuesta exitosa pero sin datos de socio válidos', [
                    'result_keys' => array_keys($result),
                    'id' => $result['Id'] ?? null,
                    'nombre' => $result['nombre'] ?? null,
                    'apellido' => $result['apellido'] ?? null
                ]);
            }
        }
        
        // Log de errores de API
        if (isset($json['estado']) && $json['estado'] !== "0") {
            Log::warning('SociosApi error response', [
                'estado' => $json['estado'],
                'msg' => $json['msg'] ?? 'Sin mensaje de error',
                'full_response' => $json
            ]);
        }
        
        return null;
    }

    protected function decodeAndLog(string $mode, string $url, $resp): ?array
    {
        if (!$resp->ok()) {
            Log::error("SociosApi {$mode} HTTP error", [
                'status' => $resp->status(),
                'url'    => $url,
                'body'   => $resp->body(),
            ]);
            return null;
        }

        $json = null;
        try {
            $json = $resp->json();
        } catch (\Throwable $e) {
            Log::error("SociosApi {$mode} JSON parse error", [
                'url'  => $url,
                'raw'  => $resp->body(),
                'err'  => $e->getMessage(),
            ]);
        }

        Log::info("SociosApi {$mode} response", [
            'url'   => $url,
            'json'  => $json,
        ]);

        return $json;
    }

    /**
     * Construye la URL directa de la foto del socio
     * No es un endpoint, es una URL directa a la imagen
     */
    public function buildFotoUrl(string $socioId): ?string
    {
        if ($this->imgBase === '' || $socioId === '') {
            return null;
        }

        // URL directa: https://clubvillamitre.com/images/socios/{socio_id}.jpg
        return "{$this->imgBase}/{$socioId}.jpg";
    }
}
