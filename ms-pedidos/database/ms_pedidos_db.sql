CREATE DATABASE ms_pedidos_db;
USE ms_pedidos_db;

CREATE TABLE pedidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    mesa_id BIGINT UNSIGNED NOT NULL,

    fecha DATE NOT NULL,
    hora TIME NOT NULL,

    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,

    estado ENUM(
        'pendiente',
        'en_preparacion',
        'entregado',
        'pagado',
        'cancelado'
    ) DEFAULT 'pendiente',

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE detalles_pedidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    pedido_id BIGINT UNSIGNED NOT NULL,

    producto_id BIGINT UNSIGNED NOT NULL,

    nombre_producto VARCHAR(150) NOT NULL,

    cantidad INT NOT NULL,

    precio_unitario DECIMAL(10,2) NOT NULL,

    subtotal DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_detalles_pedidos_pedidos
        FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id)
);

INSERT INTO pedidos (
    mesa_id,
    fecha,
    hora,
    subtotal,
    total,
    estado,
    created_at,
    updated_at
)
VALUES
(
    1,
    '2026-06-10',
    '20:00:00',
    36000,
    36000,
    'pendiente',
    NOW(),
    NOW()
);

INSERT INTO detalles_pedidos (
    pedido_id,
    producto_id,
    nombre_producto,
    cantidad,
    precio_unitario,
    subtotal,
    created_at,
    updated_at
)
VALUES
(
    1,
    1,
    'Hamburguesa Especial',
    1,
    28000,
    28000,
    NOW(),
    NOW()
),
(
    1,
    2,
    'Limonada Natural',
    1,
    8000,
    8000,
    NOW(),
    NOW()
);
