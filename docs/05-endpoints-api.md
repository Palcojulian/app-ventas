# 05 · Endpoints de la API

Base URL local: `http://localhost:8000/api` (controlador configurado en `bootstrap/app.php` con `apiPrefix: 'api'`).

Todos los endpoints de `v1` requieren encabezado:

```http
Authorization: Bearer <token>
Accept: application/json
```

Los errores siguen la forma:

- `401` → `{ success:false, message:"No autenticado…", error:"Unauthorized" }`
- `404` → `{ error:"… no encontrado.", message:"El … con el ID especificado no existe." }`
- `422` → `{ success:false, message:"Campos requeridos pendientes", errores:"…" }`
- `500` → `{ error:"Error al …", message:"<excepción>" }`

## 1. Auth (públicos y protegidos)

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| `POST` | `/api/auth/register` | ❌ | Registra usuario y devuelve token. |
| `POST` | `/api/auth/login` | ❌ | Inicia sesión y devuelve token. |
| `POST` | `/api/auth/logout` | ✅ | Revoca el token actual. |
| `GET`  | `/api/auth/me` | ✅ | Devuelve el usuario autenticado. |

### `POST /api/auth/register`

Body:

```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

Respuesta `201`:

```json
{
  "success": true,
  "message": "Usuario registrado exitosamente.",
  "token": "1|abc...",
  "user": { "id": 1, "name": "Juan Pérez", "email": "juan@example.com" }
}
```

### `POST /api/auth/login`

Body:

```json
{ "email": "juan@example.com", "password": "secret123" }
```

- `200` → `{ success:true, token, user }`.
- `401` → `{ success:false, message:"Credenciales incorrectas." }`.

## 2. Categorías — `api/v1/categorias`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET`    | `/api/v1/categorias`        | Lista todas las categorías (filtro `?estado=activo`). |
| `POST`   | `/api/v1/categorias`        | Crea una categoría. |
| `GET`    | `/api/v1/categorias/{id}`   | Obtiene una categoría. |
| `PUT`    | `/api/v1/categorias/{id}`   | Actualiza una categoría. |
| `DELETE` | `/api/v1/categorias/{id}`   | Elimina una categoría. |

Body de ejemplo (`POST` / `PUT`):

```json
{
  "nombre": "Bebidas",
  "descripcion": "Bebidas gaseosas y jugos",
  "estado": "activo",
  "prefijo": "BEB"
}
```

## 3. Proveedores — `api/v1/proveedores`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET`    | `/api/v1/proveedores`        | Lista proveedores (filtro `?estado=activo`). |
| `POST`   | `/api/v1/proveedores`        | Crea un proveedor. |
| `GET`    | `/api/v1/proveedores/{id}`   | Obtiene un proveedor. |
| `PUT`    | `/api/v1/proveedores/{id}`   | Actualiza un proveedor. |
| `DELETE` | `/api/v1/proveedores/{id}`   | Elimina un proveedor. |

Body:

```json
{
  "nombre": "Distribuidora XYZ",
  "contacto": "María López",
  "telefono": "+57 300 123 4567",
  "email": "contacto@xyz.com",
  "direccion": "Calle 1 #2-3",
  "estado": "activo"
}
```

## 4. Productos — `api/v1/productos`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET`    | `/api/v1/productos`        | Lista productos con nombre de categoría. |
| `POST`   | `/api/v1/productos`        | Crea un producto (autogenera `codigo` si está vacío). |
| `GET`    | `/api/v1/productos/{id}`   | Obtiene un producto. |
| `PUT`    | `/api/v1/productos/{id}`   | Actualiza. |
| `DELETE` | `/api/v1/productos/{id}`   | Elimina. |
| `POST`   | `/api/v1/productos/importar` | Importa productos desde Excel (`multipart/form-data`, campo `archivo`). |

Body `POST`/`PUT`:

```json
{
  "nombre": "Gaseosa 500ml",
  "descripcion": "Botella personal",
  "id_categoria": 1,
  "precio_venta": 3500,
  "costo": 2000,
  "stock_actual": 100,
  "stock_minimo": 10,
  "unidad_medida": "unidad",
  "estado": "activo"
}
```

> Si `codigo` no se envía, `CreateProductoAction` lo genera como `{prefijo}-{####}` según el contador de productos de la categoría.

## 5. Compras — `api/v1/compras`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET`    | `/api/v1/compras`        | Lista compras. |
| `POST`   | `/api/v1/compras`        | Crea una compra (proveedor + detalles). |
| `GET`    | `/api/v1/compras/{id}`   | Obtiene una compra. |
| `PUT`    | `/api/v1/compras/{id}`   | Actualiza. |
| `DELETE` | `/api/v1/compras/{id}`   | Elimina. |

Body:

```json
{
  "id_proveedor": 1,
  "estado": "pendiente",
  "detalles": [
    { "id_producto": 1, "cantidad": 50, "precio_unitario": 1800 }
  ]
}
```

## 6. Ventas — `api/v1/ventas`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET`    | `/api/v1/ventas`        | Lista ventas con `cliente` y `usuario_sistema`. |
| `POST`   | `/api/v1/ventas`        | Crea una venta. Si `estado=completado`, descuenta stock. |
| `GET`    | `/api/v1/ventas/{id}`   | Devuelve `{ id_venta, items_venta }`. |
| `PUT`    | `/api/v1/ventas/{id}`   | Actualiza. |
| `DELETE` | `/api/v1/ventas/{id}`   | Elimina. |

Body `POST`:

```json
{
  "id_cliente": 2,
  "impuestos": 0,
  "descuento": 0,
  "metodo_pago": "efectivo",
  "estado": "completado",
  "detalles": [
    { "id_producto": 1, "cantidad": 2, "precio_unitario": 3500 },
    { "id_producto": 2, "cantidad": 1, "precio_unitario": 5000 }
  ]
}
```

- El `numero_factura` se genera automáticamente (`FACT-00000001`, `FACT-00000002`, …).
- `subtotal`/`impuestos`/`descuento`/`total` se calculan en el backend.

## 🧪 Probar la API rápidamente

Con `composer dev` ya tendrás `php artisan serve` corriendo. Ejemplos con `curl`:

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Listar productos
curl http://localhost:8000/api/v1/productos \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Alternativas con UI: usar `php artisan route:list` o herramientas como **Insomnia** / **Postman** importando este archivo como especificación OpenAPI.
