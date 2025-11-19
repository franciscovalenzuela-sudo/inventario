-- Tabla de proveedores
CREATE TABLE inv_proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    ruc VARCHAR(20),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Agregar columna proveedor_id a la tabla de inventario
ALTER TABLE inv_inventario ADD COLUMN proveedor_id INT NULL AFTER tipo_objeto_id;
ALTER TABLE inv_inventario ADD FOREIGN KEY (proveedor_id) REFERENCES inv_proveedores(id);

-- Insertar algunos proveedores de ejemplo
INSERT INTO inv_proveedores (nombre, contacto, telefono, email, direccion, ruc) VALUES
('Office Solutions S.A.', 'Juan Pérez', '+1234567890', 'ventas@officesolutions.com', 'Av. Comercial 123, Zona Industrial', '12345678901'),
('Tecnología Avanzada Ltda.', 'María García', '+1234567891', 'info@tecnologia-avanzada.com', 'Calle Tecnológica 456, Parque Industrial', '12345678902'),
('Mobiliario Corporativo', 'Carlos Rodríguez', '+1234567892', 'cotizaciones@mobiliariocorp.com', 'Av. Empresarial 789, Centro', '12345678903'),
('Equipos y Suministros S.A.', 'Ana Martínez', '+1234567893', 'ventas@equipos-suministros.com', 'Calle Industrial 321, Zona Franca', '12345678904');