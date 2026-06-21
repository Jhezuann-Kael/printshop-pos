<div align="center">

# 🖨️ PrintShop POS

### Sistema de Punto de Venta para Negocios de Impresión

*Gestiona ventas, clientes, cierres, nómina y reportes — todo en un solo lugar*

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/Licencia-MIT-10b981?style=flat-square)](#licencia)
[![Moneda](https://img.shields.io/badge/Moneda-USD%20%2F%20Bs-f59e0b?style=flat-square)](#métodos-de-pago)
[![Telegram](https://img.shields.io/badge/Notificaciones-Telegram-26A5E4?style=flat-square&logo=telegram)](#telegram)

</div>

---

## ¿Qué es PrintShop POS?

**PrintShop POS** es un sistema web completo de punto de venta (POS) diseñado para negocios de impresión digital en Venezuela. Permite registrar ventas con precios en dólares y conversión automática a bolívares usando la tasa BCV oficial, gestionar clientes, controlar la nómina de trabajadores, cerrar el día con un resumen detallado y generar facturas completamente personalizables.

Está construido sobre PHP puro + MySQL, sin frameworks ni dependencias externas en el servidor, lo que lo hace fácil de desplegar en cualquier hosting compartido o VPS.

---

## Características

### Ventas y Pagos
- **Registro rápido de ventas** con múltiples productos/servicios por transacción
- **4 métodos de pago**: Efectivo Bs, Efectivo USD, Pago Móvil y Mixto (combinación libre)
- **Pago Móvil**: campo de referencia y carga de comprobante con imagen
- **Pago Mixto**: desglose libre entre métodos, con validación de suma = total
- **Descuentos** por monto en cada venta
- **Búsqueda de clientes** con autocompletado en tiempo real (nombre, cédula, teléfono)
- **Tasa BCV automática** — conversión USD→Bs en cada venta (cache 30 min, con fallback)

### Catálogo de Servicios
- 6 categorías incluidas: **Sublimación, DTF, Vinil Textil, Vinil de Corte, Vinil de Impresión, Impresiones**
- Cada categoría con color e ícono propios
- Botones de acceso rápido por categoría en el formulario de venta

### Facturación
- Factura generada en pantalla al instante
- Impresión directa (`Ctrl+P`) o **descarga en PDF** (jsPDF + html2canvas)
- Personalización total: colores del encabezado, pie, filas y totales
- Logo del negocio (PNG/JPG con fondo transparente)
- Textos configurables: título, subtítulo, pie de página y nota adicional
- Muestra comprobante de pago móvil si existe

### Dashboard y Reportes
- **Dashboard** con métricas del día y del mes: ventas e ingresos
- Gráfico de barras de los últimos 7 días
- Gráfico de dona por categoría de servicio
- Desglose de ingresos por método de pago
- **Reportes** (solo admin): semanal, mensual, anual y por trabajador
- Gráficos con **Chart.js** sin configuración adicional

### Cierres Diarios
- Cierre del día con resumen por método de pago y por categoría
- Historial de cierres con gráfico de tendencia de los últimos 30 días
- Un solo cierre por día (protección contra duplicados)
- Notas opcionales en cada cierre

### Gestión de Clientes
- CRUD completo: personas y empresas (cédula / RIF)
- Búsqueda por nombre, cédula o teléfono
- Paginación de 30 registros por página
- Notas internas por cliente

### Trabajadores y Nómina
- CRUD de trabajadores con datos personales completos
- Campos de **Pago Móvil**: banco, teléfono y cédula
- Registro de pagos en USD con conversión automática a Bs
- Carga de comprobante de pago (imagen)
- Historial de pagos por trabajador
- Gestión de roles: `admin` y `vendedor`

### Notificaciones Telegram
- Notificación automática al registrar una venta
- Notificación al realizar el cierre del día
- Configurable por tipo de evento (ventas / cierres)
- Configuración desde el panel de administración

### Seguridad
- Contraseñas con `bcrypt` (cost 12)
- Sesiones con `HTTPOnly`, `SameSite=Strict` y regeneración de ID en cada login
- Todas las consultas SQL con sentencias preparadas (PDO)
- Control de acceso por roles en cada ruta y endpoint
- Subidas de archivos con validación de tipo MIME

---

## Tecnologías

| Capa | Detalle |
|------|---------|
| Backend | PHP 8.2 — PDO/MySQL, sin frameworks |
| Base de datos | MySQL 5.7+ / MariaDB 10.3+ |
| Frontend | HTML5 + CSS3 + JavaScript vanilla |
| Gráficos | [Chart.js 4.4](https://www.chartjs.org/) |
| PDF | [jsPDF](https://github.com/parallax/jsPDF) + [html2canvas](https://html2canvas.hertzen.com/) |
| Iconos | [Font Awesome 6.5](https://fontawesome.com/) |
| Fuentes | [Inter](https://fonts.google.com/specimen/Inter) — Google Fonts |
| Servidor | Apache 2.4 + mod_rewrite (o Nginx) |
| API externa | [ve.dolarapi.com](https://ve.dolarapi.com) — Tasa BCV oficial |

---

## Requisitos

- PHP **8.1 o superior** (se usan `match`, `str_starts_with`, sin-variable catch)
- MySQL **5.7+** o MariaDB **10.3+**
- Extensiones PHP: `pdo_mysql`, `json`, `fileinfo`, `mbstring`
- Apache con `AllowOverride All` (o Nginx equivalente)
- Acceso a internet del servidor (para consultar la tasa BCV)

---

## Instalación

### 1 — Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/printshop-pos.git /var/www/html/negocio
cd /var/www/html/negocio
```

### 2 — Crear la base de datos

```sql
CREATE DATABASE negocio_ventas
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'negocio_user'@'localhost' IDENTIFIED BY 'TuContraseñaSegura';
GRANT ALL PRIVILEGES ON negocio_ventas.* TO 'negocio_user'@'localhost';
FLUSH PRIVILEGES;
```

Importar el esquema completo (tablas + datos iniciales):

```bash
mysql -u negocio_user -p negocio_ventas < database.sql
```

> El script crea todas las tablas, inserta el usuario **admin** con contraseña `Admin2024!` y las 6 categorías de servicios por defecto. **Cambia la contraseña tras el primer acceso.**

### 3 — Configurar variables de entorno

```bash
cp .env.example .env
nano .env
```

```env
DB_HOST=localhost
DB_NAME=negocio_ventas
DB_USER=negocio_user
DB_PASS=TuContraseñaSegura
APP_TIMEZONE=America/Caracas
```

### 4 — Permisos de carpetas de carga

```bash
chown -R www-data:www-data uploads/
chmod -R 775 uploads/
```

### 5 — Configurar Apache

```apache
<VirtualHost *:80>
    ServerName printshop.midominio.com
    DocumentRoot /var/www/html/negocio

    <Directory /var/www/html/negocio>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog  ${APACHE_LOG_DIR}/printshop_error.log
    CustomLog ${APACHE_LOG_DIR}/printshop_access.log combined
</VirtualHost>
```

```bash
a2ensite printshop.conf
systemctl reload apache2
```

### 6 — Primer acceso

Abre el navegador en tu dominio o IP e inicia sesión:

| Campo | Valor |
|-------|-------|
| Usuario | `admin` |
| Contraseña | `Admin2024!` |

Luego ve a **Configuración** (menú lateral) para:
- Ingresar el nombre, RIF y dirección del negocio
- Subir tu logo
- Personalizar los colores de la factura
- Configurar el bot de Telegram (opcional)

---

## Primeros pasos después de instalar

```
1. Configuración  →  Llenar datos del negocio + logo
2. Trabajadores   →  Crear los usuarios vendedores
3. Nueva Venta    →  ¡Empezar a vender!
4. Cierre del Día →  Al final de cada jornada
```

---

## Estructura del proyecto

```
printshop-pos/
│
├── 📄 index.php              # Login
├── 📄 dashboard.php          # Panel principal con métricas y gráficos
├── 📄 ventas.php             # Registro de nueva venta
├── 📄 historial.php          # Historial y búsqueda de ventas
├── 📄 clientes.php           # Gestión de clientes (CRUD)
├── 📄 cierre.php             # Cierre del día
├── 📄 cierres.php            # Historial de cierres
├── 📄 factura.php            # Vista de factura (impresión / PDF)
├── 📄 reportes.php           # Reportes avanzados (solo admin)
├── 📄 trabajadores.php       # Gestión de trabajadores y nómina
├── 📄 configuracion.php      # Ajustes del sistema (solo admin)
│
├── 📁 api/                   # Endpoints JSON
│   ├── login.php             # POST — Autenticación
│   ├── logout.php            # POST — Cierre de sesión
│   ├── dashboard.php         # GET  — Datos del dashboard
│   ├── ventas.php            # GET / POST / DELETE — Ventas
│   ├── clientes.php          # GET / POST / PUT / DELETE — Clientes
│   ├── cierre.php            # GET / POST — Cierres diarios
│   ├── reportes.php          # GET — Reportes por período
│   ├── trabajadores.php      # GET / POST / PUT — Trabajadores y pagos
│   ├── configuracion.php     # GET / POST — Configuración + logo
│   ├── categorias.php        # GET — Listado de categorías
│   ├── facturas.php          # GET — Datos de factura
│   └── bcv.php               # GET — Tasa BCV en tiempo real
│
├── 📁 config/
│   └── database.php          # Conexión PDO (lee desde .env)
│
├── 📁 includes/
│   ├── auth.php              # Sesiones, login, roles
│   ├── functions.php         # Funciones globales (BCV, config, formato)
│   ├── layout.php            # Encabezado HTML + sidebar
│   ├── layout_end.php        # Cierre del layout
│   └── telegram.php          # Notificaciones por Telegram
│
├── 📁 assets/
│   ├── css/app.css           # Hoja de estilos global (dark theme)
│   └── js/app.js             # JavaScript compartido
│
├── 📁 uploads/
│   ├── logos/                # Logo del negocio
│   └── comprobantes/         # Comprobantes de pago móvil y nómina
│
├── 📄 database.sql           # Esquema SQL completo con datos iniciales
├── 📄 .env.example           # Plantilla de configuración
└── 📄 .gitignore
```

---

## Módulos del sistema

### Dashboard
Panel central con información del día y del mes. Incluye:
- Número de ventas e ingresos del día y del mes actual
- Gráfico de barras con las ventas de los últimos 7 días
- Gráfico de dona con distribución por categoría de servicio
- Desglose de ingresos por método de pago
- Las 5 últimas ventas registradas
- Alerta si hay ventas del día sin cierre registrado

### Nueva Venta
Formulario en dos columnas para agilizar el cobro:
- **Izquierda**: datos del cliente (con autocompletado), método de pago, items y totales
- **Derecha**: preview en vivo de los artículos agregados + acceso rápido por categoría
- Al completar la venta: modal de confirmación con opción de imprimir factura al instante

### Historial de Ventas
- Búsqueda por número de venta o nombre de cliente
- Filtros por fecha y estado
- Paginación de 15 registros por página
- Modal de detalle con todos los datos de la venta
- Acceso directo a la factura de cada venta
- Anulación de ventas (solo admin)

### Clientes
- Registro de personas y empresas
- Cédula/RIF con validación de duplicados
- Los clientes guardados aparecen en el autocompletado de ventas

### Cierre del Día
- Vista de todas las ventas del día seleccionado
- Resumen de totales por método de pago con barras de progreso
- Resumen por categoría de servicio
- Registro de notas del cierre
- Historial de los últimos 30 cierres con gráfico de tendencia

### Factura
Documento generado dinámicamente con los datos de la venta:
- Encabezado con logo y datos del negocio
- Tabla de artículos con categoría, cantidad y precios
- Totales con descuento aplicado
- Equivalencia en bolívares con tasa BCV del momento
- Impresión directa o descarga en PDF

### Reportes *(solo admin)*
Cuatro vistas analíticas:
| Vista | Contenido |
|-------|-----------|
| Resumen | Hoy, esta semana, este mes, este año |
| Semanal | Gráfico de barras por los últimos 7 días |
| Mensual | Gráfico de línea por días del mes seleccionado |
| Anual | Gráfico combinado barras + línea por mes |
| Trabajadores | Ventas y nómina por empleado |

### Trabajadores *(solo admin)*
- Alta de usuarios con datos personales y de Pago Móvil
- Dos roles: `admin` (acceso total) y `vendedor` (acceso limitado)
- Registro de pagos de nómina en USD con conversión automática a Bs
- Carga de comprobante de pago
- Historial de pagos por trabajador

### Configuración *(solo admin)*
- **Datos del negocio**: nombre, RIF, dirección, teléfono, email
- **Logo**: carga de imagen, preview en vivo
- **Colores de factura**: personalización completa con color picker
- **Textos**: título, subtítulo, pie de página y nota
- **Telegram**: token, chat ID y selección de eventos a notificar
- Preview de la factura actualizado en tiempo real mientras editas

---

## Métodos de pago

| Código | Descripción | Campos adicionales |
|--------|-------------|-------------------|
| `fisico_bs` | Efectivo en bolívares | — |
| `fisico_usd` | Efectivo en dólares | — |
| `pago_movil` | Pago Móvil (transferencia) | Referencia + imagen comprobante |
| `mixto` | Combinación libre de métodos | Desglose por método con validación de suma |

---

## Telegram

Para recibir notificaciones automáticas en Telegram:

**1. Crear el bot**

Abre [@BotFather](https://t.me/BotFather) en Telegram y ejecuta:
```
/newbot
```
Copia el **token** que te entrega.

**2. Obtener el Chat ID**

Envía un mensaje a tu bot y abre esta URL en el navegador (reemplaza `TOKEN`):
```
https://api.telegram.org/botTOKEN/getUpdates
```
Busca el campo `"chat": {"id": XXXXXXX}` — ese es tu Chat ID.

**3. Configurar en el sistema**

Ve a **Configuración → Telegram**, pega el token y el chat ID, activa las notificaciones que desees y guarda.

**Mensajes que envía el bot:**

- 🧾 Nueva venta: número, cliente, total, equivalente en Bs, método e ítems
- 📊 Cierre del día: fecha, cantidad de ventas, total y desglose por método

---

## Seguridad

| Aspecto | Implementación |
|---------|---------------|
| Contraseñas | `password_hash()` con bcrypt (cost 12) |
| Sesiones | `HTTPOnly`, `SameSite=Strict`, regeneración de ID en login |
| SQL | PDO con sentencias preparadas — sin concatenación de parámetros |
| XSS | `htmlspecialchars()` en todos los outputs PHP |
| Subidas | Validación de tipo MIME + nombre de archivo con timestamp |
| Uploads | Directorio protegido con `index.php` que retorna 403 |
| Credenciales | Nunca en el repositorio — siempre en `.env` |
| Control de acceso | `requireLogin()` y `requireAdmin()` en cada ruta y endpoint |

---

## Roles y permisos

| Módulo | Admin | Vendedor |
|--------|:-----:|:--------:|
| Dashboard | ✅ | ✅ |
| Nueva Venta | ✅ | ✅ |
| Historial de Ventas | ✅ | ✅ |
| Clientes | ✅ | ✅ |
| Cierre del Día | ✅ | ✅ |
| Historial de Cierres | ✅ | ✅ |
| Reportes | ✅ | ❌ |
| Trabajadores | ✅ | ❌ |
| Configuración | ✅ | ❌ |
| Anular ventas | ✅ | ❌ |

---

## Base de datos

El esquema incluye 9 tablas:

| Tabla | Descripción |
|-------|-------------|
| `usuarios` | Trabajadores y cuentas del sistema |
| `categorias` | Categorías de servicios (Sublimación, DTF, etc.) |
| `clientes` | Directorio de clientes personas y empresas |
| `ventas` | Cabecera de cada transacción |
| `detalle_ventas` | Ítems individuales de cada venta |
| `ventas_pago_mixto` | Desglose de pagos mixtos por método |
| `cierres_diarios` | Registro de cierres con totales por método |
| `pagos_trabajadores` | Historial de pagos de nómina |
| `configuracion` | Ajustes del sistema (clave-valor) |

---

## API — Referencia rápida

Todos los endpoints requieren sesión activa. Los marcados con 🔒 requieren rol `admin`.

```
POST   /api/login.php                        Iniciar sesión
POST   /api/logout.php                       Cerrar sesión

GET    /api/dashboard.php                    Datos del dashboard
GET    /api/bcv.php                          Tasa BCV actual

GET    /api/ventas.php?page=&buscar=&fecha=  Listar ventas
GET    /api/ventas.php?id=X                  Detalle de una venta
POST   /api/ventas.php                       Registrar venta
DELETE /api/ventas.php?id=X            🔒   Anular venta

GET    /api/clientes.php?buscar=             Autocomplete (max 15)
GET    /api/clientes.php?page=&q=&tipo=      Listar clientes
GET    /api/clientes.php?id=X               Obtener cliente
POST   /api/clientes.php                     Crear cliente
PUT    /api/clientes.php                     Editar cliente
DELETE /api/clientes.php?id=X               Eliminar cliente

GET    /api/cierre.php?fecha=YYYY-MM-DD      Datos del cierre de un día
GET    /api/cierre.php?historial=1           Últimos 30 cierres
POST   /api/cierre.php                       Registrar cierre del día

GET    /api/reportes.php?tipo=resumen        🔒 Resumen general
GET    /api/reportes.php?tipo=semanal        🔒 Últimos 7 días
GET    /api/reportes.php?tipo=mensual        🔒 Por día del mes
GET    /api/reportes.php?tipo=anual          🔒 Por mes del año
GET    /api/reportes.php?tipo=trabajadores   🔒 Estadísticas por empleado

GET    /api/trabajadores.php                 🔒 Listar trabajadores
GET    /api/trabajadores.php?id=X            🔒 Datos de un trabajador
GET    /api/trabajadores.php?pagos=X         🔒 Historial de pagos
POST   /api/trabajadores.php                 🔒 Crear trabajador
POST   /api/trabajadores.php?pago=1          🔒 Registrar pago de nómina
PUT    /api/trabajadores.php                 🔒 Editar trabajador

GET    /api/configuracion.php                🔒 Leer configuración
POST   /api/configuracion.php                🔒 Guardar configuración
POST   /api/configuracion.php?logo=1         🔒 Subir logo

GET    /api/categorias.php                   Listar categorías activas
GET    /api/facturas.php?id=X               Datos de factura
```

---

## Instalación en producción (checklist)

```
[ ] Cambiar contraseña del admin por defecto
[ ] Configurar HTTPS (Let's Encrypt / Certbot)
[ ] Poner session.cookie_secure = On en php.ini
[ ] Ajustar permisos: uploads/ 775, archivos PHP 644
[ ] Deshabilitar display_errors en php.ini
[ ] Configurar backup automático de la base de datos
[ ] Agregar datos del negocio en Configuración
[ ] Subir logo del negocio
[ ] (Opcional) Configurar bot de Telegram
```

---

## Licencia

Este proyecto está bajo la licencia **MIT**. Puedes usarlo, modificarlo y distribuirlo libremente, incluso con fines comerciales.

---

<div align="center">

Hecho con 💜 para negocios de impresión venezolanos

</div>
