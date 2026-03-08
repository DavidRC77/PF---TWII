<?php
/**
 * JwtSession
 *
 * Reemplaza las sesiones de archivo de PHP con un JWT firmado (HMAC-SHA256)
 * almacenado en una cookie HttpOnly. Funciona en entornos serverless (Vercel)
 * donde el filesystem no es persistente entre invocaciones.
 *
 * - No almacena nada en el servidor ni en la base de datos.
 * - El token es verificable y a prueba de manipulaciones.
 * - Requiere la variable de entorno SESSION_SECRET (string largo y aleatorio).
 *   En Vercel: añadirla en Project Settings > Environment Variables.
 *   En local:  añadir SESSION_SECRET=tu_clave_aqui al archivo .env
 */
class JwtSession
{
    private const COOKIE   = 'twii_sess';
    private const LIFETIME = 7200; // 2 horas en segundos

    // -------------------------------------------------------------------------
    // API pública
    // -------------------------------------------------------------------------

    /**
     * Crea un JWT con los datos del usuario y lo guarda en cookie HttpOnly.
     * Llamar justo antes del header("Location:...") en el login.
     */
    public static function create(array $payload): void
    {
        $header  = self::b64e(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + self::LIFETIME;
        $body    = self::b64e(json_encode($payload));
        $sig     = self::b64e(hash_hmac('sha256', "$header.$body", self::secret(), true));

        setcookie(self::COOKIE, "$header.$body.$sig", [
            'expires'  => time() + self::LIFETIME,
            'path'     => '/',
            'httponly' => true,
            'secure'   => self::isHttps(),
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Lee y verifica el JWT de la cookie.
     * Devuelve el payload como array o null si el token no existe / es inválido.
     */
    public static function load(): ?array
    {
        $token = $_COOKIE[self::COOKIE] ?? null;
        if (!$token) return null;

        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $sig] = $parts;

        // Verificar firma (comparación en tiempo constante para evitar timing attacks)
        $expected = self::b64e(hash_hmac('sha256', "$header.$body", self::secret(), true));
        if (!hash_equals($expected, $sig)) return null;

        $data = json_decode(self::b64d($body), true);
        if (!$data || !isset($data['exp']) || $data['exp'] < time()) return null;

        return $data;
    }

    /**
     * Elimina la cookie del JWT. Llamar en logout.
     */
    public static function destroy(): void
    {
        setcookie(self::COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => self::isHttps(),
            'samesite' => 'Lax',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private static function secret(): string
    {
        static $secret = null;
        if ($secret !== null) return $secret;

        // 1. Variable de entorno ya cargada (Vercel o cargada por conexion.php)
        $secret = $_ENV['SESSION_SECRET'] ?? getenv('SESSION_SECRET') ?? '';

        // 2. Fallback: leer .env directamente (útil si jwt_session se carga
        //    antes de conexion.php)
        if (!$secret) {
            $envFile = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envFile)) {
                foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
                    [$k, $v] = explode('=', $line, 2);
                    if (trim($k) === 'SESSION_SECRET') { $secret = trim($v); break; }
                }
            }
        }

        if (!$secret) {
            // Sin clave configurada: inseguro en producción, solo para desarrollo local.
            $secret = 'dev_only_fallback_key_set_SESSION_SECRET_in_env';
        }

        return $secret;
    }

    private static function b64e(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string
    {
        $pad = (4 - strlen($data) % 4) % 4;
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', $pad));
    }

    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
}
