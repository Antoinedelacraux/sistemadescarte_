# Modelo de Datos (Entidad-Relación) y Políticas de Acceso

## 1. Diagrama ER Lógico

```mermaid
erDiagram
    USERS ||--o{ FUNDO_USER : "es asignado a"
    ROLES ||--o{ USERS : "tiene"
    FUNDOS ||--o{ FUNDO_USER : "tiene asignado"
    FUNDOS ||--o{ LOTES : "tiene"
    LOTES ||--o{ CUARTELES : "contiene"
    
    FUNDOS ||--o{ VENTAS_DESCARTE : "registra"
    LOTES ||--o{ VENTAS_DESCARTE : "origen"
    USERS ||--o{ VENTAS_DESCARTE : "creado_por"
    USERS ||--o{ VENTAS_DESCARTE : "actualizado_por"
    
    VENTAS_DESCARTE ||--o{ AUDIT_LOGS : "genera"

    ROLES {
        bigint id PK
        string nombre "Administrador, Individual, General, Analista"
    }
    
    USERS {
        bigint id PK
        bigint role_id FK
        string name
        string email
        string password
    }

    FUNDOS {
        bigint id PK
        string nombre
    }

    FUNDO_USER {
        bigint user_id FK
        bigint fundo_id FK
    }

    LOTES {
        bigint id PK
        bigint fundo_id FK
        string nombre
    }

    CUARTELES {
        bigint id PK
        bigint lote_id FK
        string nombre
    }

    VENTAS_DESCARTE {
        string id PK "UUID v4 (para offline idempotency)"
        bigint fundo_id FK
        bigint lote_id FK
        string cuartel_manual "Texto (cuando es Cosecha Nacional)"
        string motivo "Campo, Packing, Cosecha Nacional"
        string tipo_descarte "Racimos, Racimos con plaga, Granos"
        date fecha_produccion
        decimal precio "10,2"
        decimal kilogramos "10,2"
        decimal valor_venta "10,2"
        int jabas "nullable"
        decimal peso_jaba "10,2 nullable"
        string brevete "nullable"
        string ruc "nullable"
        string placa "nullable"
        string conductor "nullable"
        string viaje "nullable"
        text observacion "nullable"
        bigint created_by FK
        bigint updated_by FK "Usuario que hizo la última modificación"
        datetime created_at
        datetime updated_at
    }

    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string table_name
        string record_id "UUID de la Venta"
        string action "CREATED, UPDATED, DELETED"
        json old_values
        json new_values
        datetime created_at
    }
```

## 2. Normalización y Decisiones Técnicas
- **UUID en Ventas:** Se usará UUID v4 como Primary Key en `ventas_descarte` para permitir la generación en la PWA (Offline) y evitar colisiones de IDs al sincronizar.
- **Cuarteles:** Aunque existe la tabla `CUARTELES` para mantener un catálogo estructurado (perteneciente a `LOTES`), en la tabla de `ventas_descarte` se guardará el texto en `cuartel_manual` para dar flexibilidad al ingreso manual si el motivo es "Cosecha Nacional".
- **Decimales:** Todos los montos y pesos se definen como `DECIMAL(10,2)`. El cálculo se hará en frontend y se verificará en backend.
- **Auditoría:** La tabla `audit_logs` guardará el historial de cambios inmutables. Además, `ventas_descarte` conservará `created_by` y `updated_by`.

## 3. Políticas de Acceso y Aislamiento por Fundo (Multi-tenant)
- **Global Scopes (Laravel):** Se implementará un `FundoScope` global en el modelo `VentaDescarte` y `Lote`.
  - Si el usuario logueado es **Individual** o **General**, el query de base de datos añadirá automáticamente `WHERE fundo_id IN (...)` garantizando que NUNCA puedan consultar o modificar datos ajenos, independientemente de la URL a la que accedan.
  - El rol **Analista** se exceptuará del Scope, permitiéndole ver los registros de todos los fundos.
  - Las validaciones de Form Requests (Backend) impedirán insertar registros con un `fundo_id` o `lote_id` no asignado al usuario.
