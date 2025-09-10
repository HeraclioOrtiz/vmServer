-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS vmserver;

-- Usar la base de datos
USE vmserver;

-- Configuraciones para mejor performance
SET GLOBAL innodb_buffer_pool_size = 134217728; -- 128MB
SET GLOBAL max_connections = 200;
