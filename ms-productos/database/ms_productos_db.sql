CREATE DATABASE ms_productos_db;
USE ms_productos_db;


CREATE TABLE categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(10,2) NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,

    categoria_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_productos_categorias
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
);

INSERT INTO categorias (
    nombre,
    descripcion,
    created_at,
    updated_at
)
VALUES
('Entradas', 'Productos de entrada', NOW(), NOW()),
('Bebidas', 'Bebidas frías y calientes', NOW(), NOW()),
('Platos fuertes', 'Platos principales', NOW(), NOW()),
('Postres', 'Productos dulces', NOW(), NOW());

INSERT INTO productos (
    nombre,
    descripcion,
    precio,
    disponible,
    categoria_id,
    created_at,
    updated_at
)
VALUES
(
    'Hamburguesa Especial',
    'Hamburguesa con queso y tocineta',
    28000,
    TRUE,
    3,
    NOW(),
    NOW()
),
(
    'Limonada Natural',
    'Bebida natural de limón',
    8000,
    TRUE,
    2,
    NOW(),
    NOW()
),
(
    'Cheesecake',
    'Postre de queso',
    12000,
    TRUE,
    4,
    NOW(),
    NOW()
);
