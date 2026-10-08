# Seguridad y manejo de datos

Datos de la empresa: asumir confidencialidad. Usar datos simulados en desarrollo.

## Política de Sesión y Datos Offline (PWA)
- **Persistencia de sesión:** El token JWT o cookie de sesión debe tener una expiración prolongada pero razonable, o usar un mecanismo de refresh token automático para que la operación sin internet se pueda realizar a lo largo de toda la jornada laboral.
- **Protección IndexedDB:** Los datos de la cola local (pendientes de sincronizar) y los catálogos residen en el navegador. Por limitación del navegador no están encriptados por defecto. Si el modelo de amenazas lo exige, se deberá encriptar usando la contraseña/PIN del usuario.
- **Vaciado Seguro y Cierre de Sesión:** Al desloguearse explícitamente, la PWA debe borrar catálogos y tokens. **Obligatoriamente**, la PWA verificará si existen ventas de descarte en cola (pendientes/error) y **bloqueará o advertirá severamente** antes de limpiar, ya que implicaría pérdida de datos de negocio.
- **Catálogos y Permisos Desactualizados:** Dado que un trabajador puede operar offline mientras le revocan permisos en el sistema central, el Backend (Laravel) será la única fuente de verdad validando todo al sincronizar. Un rechazo no debe purgar los datos locales sino dejarlos como 'error' para revisión.
