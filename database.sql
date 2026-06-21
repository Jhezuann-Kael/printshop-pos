-- PrintShop — Esquema de base de datos
-- Compatibble con MySQL 5.7+ / MariaDB 10.3+
-- Uso: mysql -u <usuario> -p <base_de_datos> < database.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ── Usuarios / Trabajadores ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nombre`               VARCHAR(100)    NOT NULL,
  `apellido`             VARCHAR(100)             DEFAULT NULL,
  `usuario`              VARCHAR(60)     NOT NULL UNIQUE,
  `email`                VARCHAR(150)             DEFAULT NULL,
  `password_hash`        VARCHAR(255)    NOT NULL,
  `rol`                  ENUM('admin','vendedor') NOT NULL DEFAULT 'vendedor',
  `activo`               TINYINT(1)      NOT NULL DEFAULT 1,
  `cedula`               VARCHAR(20)              DEFAULT NULL,
  `telefono`             VARCHAR(20)              DEFAULT NULL,
  `lugar_residencia`     VARCHAR(200)             DEFAULT NULL,
  `fecha_nacimiento`     DATE                     DEFAULT NULL,
  `banco_pago_movil`     VARCHAR(100)             DEFAULT NULL,
  `telefono_pago_movil`  VARCHAR(20)              DEFAULT NULL,
  `cedula_pago_movil`    VARCHAR(20)              DEFAULT NULL,
  `creado_en`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin por defecto: usuario=admin, contraseña=Admin2024!
INSERT IGNORE INTO `usuarios` (`nombre`, `usuario`, `password_hash`, `rol`) VALUES
('Administrador', 'admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin');

-- ── Categorías de productos ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categorias` (
  `id`     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100)  NOT NULL,
  `color`  VARCHAR(20)   NOT NULL DEFAULT '#7c3aed',
  `icono`  VARCHAR(50)   NOT NULL DEFAULT 'fa-tag',
  `activo` TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `categorias` (`nombre`, `color`, `icono`) VALUES
('Sublimación',       '#7c3aed', 'fa-shirt'),
('DTF',               '#06b6d4', 'fa-print'),
('Vinil Textil',      '#f59e0b', 'fa-scissors'),
('Vinil de Corte',    '#10b981', 'fa-cut'),
('Vinil de Impresión','#ef4444', 'fa-image'),
('Impresiones',       '#8b5cf6', 'fa-file-image');

-- ── Clientes ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `clientes` (
  `id`                    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `tipo`                  ENUM('persona','empresa') NOT NULL DEFAULT 'persona',
  `nombre`                VARCHAR(150)  NOT NULL,
  `cedula_rif`            VARCHAR(20)             DEFAULT NULL UNIQUE,
  `telefono`              VARCHAR(20)             DEFAULT NULL,
  `notas`                 TEXT                    DEFAULT NULL,
  `registrado_por`        INT UNSIGNED            DEFAULT NULL,
  `creado_en`             DATETIME      NOT NULL  DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`registrado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Ventas ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ventas` (
  `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `numero_venta`      VARCHAR(30)     NOT NULL UNIQUE,
  `usuario_id`        INT UNSIGNED    NOT NULL,
  `cliente`           VARCHAR(150)             DEFAULT NULL,
  `cliente_cedula`    VARCHAR(20)              DEFAULT NULL,
  `cliente_telefono`  VARCHAR(20)              DEFAULT NULL,
  `total`             DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `descuento`         DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `total_final`       DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `metodo_pago`       ENUM('fisico_bs','fisico_usd','pago_movil','mixto') NOT NULL,
  `estado`            ENUM('completada','anulada') NOT NULL DEFAULT 'completada',
  `notas`             TEXT                     DEFAULT NULL,
  `tasa_bcv`          DECIMAL(10,4)            DEFAULT NULL,
  `total_bs`          DECIMAL(14,2)            DEFAULT NULL,
  `referencia_pm`     VARCHAR(100)             DEFAULT NULL,
  `comprobante_pm`    VARCHAR(255)             DEFAULT NULL,
  `creado_en`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ventas_fecha`   (`creado_en`),
  INDEX `idx_ventas_estado`  (`estado`),
  INDEX `idx_ventas_cliente` (`cliente`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Detalle de ventas ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `detalle_ventas` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `venta_id`       INT UNSIGNED   NOT NULL,
  `categoria_id`   INT UNSIGNED   NOT NULL,
  `descripcion`    VARCHAR(255)   NOT NULL,
  `cantidad`       INT UNSIGNED   NOT NULL DEFAULT 1,
  `precio_unitario` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `subtotal`       DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`venta_id`)     REFERENCES `ventas`(`id`)     ON DELETE CASCADE,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Pagos mixtos ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ventas_pago_mixto` (
  `id`       INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `venta_id` INT UNSIGNED  NOT NULL,
  `metodo`   ENUM('fisico_bs','fisico_usd','pago_movil') NOT NULL,
  `monto`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`venta_id`) REFERENCES `ventas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Cierres diarios ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cierres_diarios` (
  `id`                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `usuario_id`          INT UNSIGNED   NOT NULL,
  `fecha`               DATE           NOT NULL UNIQUE,
  `total_ventas`        INT UNSIGNED   NOT NULL DEFAULT 0,
  `monto_total`         DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `monto_efectivo`      DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `monto_transferencia` DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `monto_tarjeta`       DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `monto_mixto`         DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `notas`               TEXT                    DEFAULT NULL,
  `cerrado_en`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Pagos a trabajadores ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pagos_trabajadores` (
  `id`                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `usuario_id`         INT UNSIGNED   NOT NULL,
  `monto`              DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `moneda`             VARCHAR(10)    NOT NULL DEFAULT 'usd',
  `descripcion`        VARCHAR(255)            DEFAULT NULL,
  `fecha`              DATE           NOT NULL,
  `tasa_bcv`           DECIMAL(10,4)           DEFAULT NULL,
  `monto_bs`           DECIMAL(14,2)           DEFAULT NULL,
  `imagen_comprobante` VARCHAR(255)            DEFAULT NULL,
  `registrado_por`     INT UNSIGNED   NOT NULL,
  `creado_en`          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`)     REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`registrado_por`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Configuración del negocio ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `configuracion` (
  `clave`       VARCHAR(80)   NOT NULL,
  `valor`       TEXT                   DEFAULT NULL,
  `actualizado` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `configuracion` (`clave`, `valor`) VALUES
('negocio_nombre',             'Mi Negocio'),
('negocio_rif',                ''),
('negocio_direccion',          ''),
('negocio_telefono',           ''),
('negocio_email',              ''),
('factura_titulo',             'FACTURA'),
('factura_subtitulo',          ''),
('factura_pie',                'Gracias por su compra'),
('factura_nota',               ''),
('factura_logo',               ''),
('factura_mostrar_logo',       '1'),
('factura_color_primario',     '#7c3aed'),
('factura_color_header_texto', '#ffffff'),
('factura_color_pie_bg',       '#7c3aed'),
('factura_color_pie_texto',    '#ffffff'),
('factura_color_fila_bg',      '#ffffff'),
('factura_color_fila_alt',     '#f9f9f9'),
('factura_color_fila_texto',   '#333333'),
('factura_color_fila_borde',   '#e0e0e0'),
('factura_color_total_bg',     '#7c3aed'),
('factura_color_total_texto',  '#ffffff'),
('telegram_bot_token',         ''),
('telegram_chat_id',           ''),
('telegram_notif_ventas',      '1'),
('telegram_notif_cierres',     '1');
