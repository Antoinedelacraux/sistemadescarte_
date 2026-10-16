<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de autenticación para la API de Power BI.
 *
 * Valida el token Bearer contra la variable de entorno POWERBI_API_TOKEN.
 * El token nunca se almacena en base de datos; es un secreto estático
 * gestionado por variables de entorno fuera del repositorio.
 *
 * Uso: añadir 'api.token' como alias en bootstrap/app.php y aplicar
 * a las rutas del grupo /api/v1/...
 */
class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('app.powerbi_api_token');

        // Si no hay token configurado, la API está deshabilitada por seguridad.
        if (empty($expectedToken)) {
            return response()->json([
                'error'   => 'API no disponible.',
                'message' => 'El token de acceso no está configurado en el servidor.',
            ], 503);
        }

        $providedToken = $request->bearerToken();

        if (empty($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'error'   => 'No autorizado.',
                'message' => 'Token Bearer inválido o ausente. Incluya el encabezado: Authorization: Bearer <token>',
            ], 401);
        }

        return $next($request);
    }
}
