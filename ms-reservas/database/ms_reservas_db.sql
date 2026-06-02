CREATE DATABASE ms_reservas_db;
USE ms_reservas_db;

CREATE TABLE mesas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL UNIQUE,
    capacidad INT NOT NULL,
    estado ENUM(
        'disponible',
        'reservada',
        'ocupada',
        'fuera_servicio'
    ) DEFAULT 'disponible',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE reservas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(150) NOT NULL,
    telefono_cliente VARCHAR(30) NOT NULL,
    cantidad_personas INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    observaciones TEXT NULL,
    estado ENUM(
        'pendiente',
        'confirmada',
        'cancelada',
        'finalizada'
    ) DEFAULT 'pendiente',

    mesa_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_reservas_mesas
        FOREIGN KEY (mesa_id)
        REFERENCES mesas(id)
);

INSERT INTO mesas (
    numero,
    capacidad,
    estado,
    created_at,
    updated_at
)
VALUES
('MESA-1', 2, 'disponible', NOW(), NOW()),
('MESA-2', 4, 'disponible', NOW(), NOW()),
('MESA-3', 6, 'disponible', NOW(), NOW()),
('MESA-4', 8, 'disponible', NOW(), NOW());

INSERT INTO reservas (
    nombre_cliente,
    telefono_cliente,
    cantidad_personas,
    fecha,
    hora,
    observaciones,
    estado,
    mesa_id,
    created_at,
    updated_at
)
VALUES
(
    'Carlos Ramirez',
    '3001234567',
    4,
    '2026-06-10',
    '19:00:00',
    'Reserva familiar',
    'confirmada',
    2,
    NOW(),
    NOW()
);
