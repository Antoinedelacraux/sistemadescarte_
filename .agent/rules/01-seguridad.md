---
trigger: always_on
description: "Seguridad de datos, autenticación, autorización RBAC, protección de credenciales y principio de mínimo privilegio."
---

# Seguridad no negociable de la información

## 1. Protección estricta de credenciales y secretos
- Nunca registrar secretos, contraseñas, tokens de API, hashes ni llaves privadas en archivos rastreados por Git, documentación, prompts o registros de chat.
- Las variables de entorno de producción (`.env`) jamás deben commitearse ni compartirse en canales inseguros.
- El archivo `.gitignore` debe contener obligatoriamente `.env`, `.env.backup`, `*.sql`, `*.dump`, `*.sqlite` y respaldos.

## 2. Autenticación y control de acceso (RBAC)
- Autenticación obligatoria con hashes seguros (Argon2id o Bcrypt con costo adecuado).
- Autorización basada estrictamente en roles (`Admin`, `Analista`, `Individual`, `Visualizador`) y scopes de fundo (`FundoScope`).
- Toda validación de autorización debe ejecutarse en el servidor; ocultar botones o enlaces en la interfaz de usuario no reemplaza los controles del backend.
- Aplicar el principio de mínimo privilegio: los usuarios solo pueden consultar y operar los fundos y recursos que tienen explícitamente asignados.

## 3. Validación y protección contra inyecciones
- Validación rigurosa de todas las entradas del lado servidor (tipos, rangos numéricos, listas permitidas y formato).
- Todas las consultas a base de datos deben utilizar consultas parametrizadas u ORM seguro (Eloquent/PDO preparado); prohibida la concatenación directa de entradas de usuario en sentencias SQL.
- Sanitización de salidas para prevenir XSS y protección CSRF activa en todos los formularios y mutaciones de estado.

## 4. Gestión de sesiones y respuestas
- Sesiones seguras con cookies `HttpOnly`, `SameSite=Lax/Strict` y transmisión exclusiva sobre HTTPS (`Secure`).
- En entornos de producción, `APP_DEBUG=false` obligatorio para evitar exponer stack traces, rutas del servidor, variables de entorno o consultas SQL en respuestas de error.

## 5. Confidencialidad y anonimización de datos agrícolas
- Nunca subir datasets reales del fundo, nombres de productores, datos comerciales, precios ni información personal a servicios externos de IA o nubes públicas sin autorización y anonimización previa.
- Los logs del sistema nunca deben registrar números de documento (RUC/DNI), datos de tarjetas, contraseñas ni detalles financieros confidenciales.

## 6. Operaciones de alto impacto
- Toda operación destructiva, migración de producción o eliminación física de datos está bloqueada hasta recibir confirmación humana explícita.
- No desplegar a producción automáticamente sin pasar los filtros de verificación de seguridad.
