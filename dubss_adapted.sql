/* ---------------------------------------------------- */
/*  DUBSS - Script SQL Adaptado para Laravel           */
/*  Integración con tabla users de Laravel Breeze      */
/*  Created: 06-dic-2025                                */
/*  DBMS: PostgreSQL                                    */
/* ---------------------------------------------------- */
/* Drop Tables */

DROP TABLE IF EXISTS beca CASCADE
;

DROP TABLE IF EXISTS beca_requisito CASCADE
;

DROP TABLE IF EXISTS convocatoria CASCADE
;

DROP TABLE IF EXISTS dependencia_economica CASCADE
;

DROP TABLE IF EXISTS documento CASCADE
;

DROP TABLE IF EXISTS estado_tramite CASCADE
;

DROP TABLE IF EXISTS estudiante CASCADE
;

DROP TABLE IF EXISTS formulario_socio_economico CASCADE
;

DROP TABLE IF EXISTS grupo_familiar CASCADE
;

DROP TABLE IF EXISTS ingreso_economico CASCADE
;

DROP TABLE IF EXISTS miembro_familiar CASCADE
;

DROP TABLE IF EXISTS notificacion CASCADE
;

DROP TABLE IF EXISTS personal_administrativo CASCADE
;

DROP TABLE IF EXISTS postulacion CASCADE
;

DROP TABLE IF EXISTS requisito CASCADE
;

DROP TABLE IF EXISTS residencia CASCADE
;

DROP TABLE IF EXISTS rol CASCADE
;

DROP TABLE IF EXISTS tenencia_vivienda CASCADE
;

DROP TABLE IF EXISTS tipo_ocupacion_dependiente CASCADE
;

DROP TABLE IF EXISTS tipo_tenencia_vivienda CASCADE
;

DROP TABLE IF EXISTS tramite CASCADE
;

DROP TABLE IF EXISTS tramite_historial CASCADE
;

DROP TABLE IF EXISTS usuario CASCADE
;
-- =====================================================
-- PASO 1: EXTENDER LA TABLA USERS DE LARAVEL
-- =====================================================

-- Agregar campos adicionales a la tabla users existente
ALTER TABLE users
ADD COLUMN IF NOT EXISTS nombres VARCHAR(100),
ADD COLUMN IF NOT EXISTS apellidos VARCHAR(100),
ADD COLUMN IF NOT EXISTS ci VARCHAR(20) UNIQUE,
ADD COLUMN IF NOT EXISTS telefono VARCHAR(15),
ADD COLUMN IF NOT EXISTS ciudad VARCHAR(50),
ADD COLUMN IF NOT EXISTS fecha_nacimiento DATE;

-- Crear índice para CI (cédula de identidad)
CREATE INDEX IF NOT EXISTS idx_users_ci ON users(ci);

-- =====================================================
-- PASO 2: TABLAS BASE (SIN DEPENDENCIAS)
-- =====================================================

-- Estado Trámite
CREATE TABLE IF NOT EXISTS estado_tramite (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(255)
);

-- Convocatoria
CREATE TABLE IF NOT EXISTS convocatoria (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Requisito
CREATE TABLE IF NOT EXISTS requisito (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- PASO 3: BECAS
-- =====================================================

-- Beca
CREATE TABLE IF NOT EXISTS beca (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    version VARCHAR(10) NOT NULL,
    periodo VARCHAR(20) NOT NULL,
    id_convocatoria BIGINT NOT NULL,
    cupos_disponibles INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_convocatoria) REFERENCES convocatoria(id) ON DELETE CASCADE
);

-- Beca Requisito (Tabla pivote)
CREATE TABLE IF NOT EXISTS beca_requisito (
    id SERIAL PRIMARY KEY,
    id_beca BIGINT NOT NULL,
    id_requisito INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_beca) REFERENCES beca(id) ON DELETE CASCADE,
    FOREIGN KEY (id_requisito) REFERENCES requisito(id) ON DELETE CASCADE
);

-- =====================================================
-- PASO 4: USUARIOS ESPECIALIZADOS (HERENCIA)
-- =====================================================

-- Estudiante (extiende users)
CREATE TABLE IF NOT EXISTS estudiante (
    id_usuario BIGINT PRIMARY KEY,
    nro_registro VARCHAR(50),
    carrera VARCHAR(100),
    semestre INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES users(id) ON DELETE CASCADE
);

-- Personal Administrativo (extiende users)
CREATE TABLE IF NOT EXISTS personal_administrativo (
    id_usuario BIGINT PRIMARY KEY,
    cargo VARCHAR(50),
    departamento VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- PASO 5: FORMULARIO SOCIOECONÓMICO
-- =====================================================

-- Formulario Socio Económico
CREATE TABLE IF NOT EXISTS formulario_socio_economico (
    id BIGSERIAL PRIMARY KEY,
    id_estudiante BIGINT,
    validado_por BOOLEAN,
    fecha_llenado DATE,
    completado BOOLEAN,
    telefono_referencia VARCHAR(50),
    comentario_personal VARCHAR(255),
    observaciones VARCHAR(50),
    discapacidad BOOLEAN,
    comentario_discapacidad VARCHAR(255),
    otro_beneficio BOOLEAN,
    comentario_otro_beneficio VARCHAR(255),
    lugar_procedencia VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_usuario) ON DELETE CASCADE
);

-- Grupo Familiar
CREATE TABLE IF NOT EXISTS grupo_familiar (
    id BIGSERIAL PRIMARY KEY,
    id_formulario BIGINT,
    cantidad_hijos INTEGER,
    cantidad_familiares INTEGER NOT NULL,
    tiene_hijos BOOLEAN NOT NULL,
    puntaje NUMERIC(4,2),
    puntaje_total NUMERIC(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_formulario) REFERENCES formulario_socio_economico(id) ON DELETE CASCADE
);

-- Miembro Familiar
CREATE TABLE IF NOT EXISTS miembro_familiar (
    id BIGSERIAL PRIMARY KEY,
    id_grupo_familiar BIGINT,
    nombre_completo VARCHAR(100),
    parentesco VARCHAR(100),
    edad INTEGER,
    ocupacion VARCHAR(100),
    observacion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_grupo_familiar) REFERENCES grupo_familiar(id) ON DELETE CASCADE
);

-- Dependencia Económica
CREATE TABLE IF NOT EXISTS dependencia_economica (
    id BIGSERIAL PRIMARY KEY,
    id_formulario BIGINT,
    tipo_dependencia VARCHAR(50),
    nota_ocupacion_dependiente VARCHAR(50),
    id_ocupacion_dependiente BIGINT,
    puntaje NUMERIC(3,1),
    puntaje_total NUMERIC(3,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_formulario) REFERENCES formulario_socio_economico(id) ON DELETE CASCADE
);

-- Ingreso Económico
CREATE TABLE IF NOT EXISTS ingreso_economico (
    id BIGSERIAL PRIMARY KEY,
    id_dependencia_eco BIGINT,
    rango_monto VARCHAR(50),
    puntaje NUMERIC(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dependencia_eco) REFERENCES dependencia_economica(id) ON DELETE CASCADE
);

-- Tipo Ocupación Dependiente (Tabla paramétrica)
CREATE TABLE IF NOT EXISTS tipo_ocupacion_dependiente (
    id BIGSERIAL PRIMARY KEY,
    id_dependencia_eco BIGINT,
    nombre VARCHAR(50),
    archivo_adjuntar VARCHAR(50),
    puntaje NUMERIC(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dependencia_eco) REFERENCES dependencia_economica(id) ON DELETE CASCADE
);

-- Residencia
CREATE TABLE IF NOT EXISTS residencia (
    id BIGSERIAL PRIMARY KEY,
    id_formulario BIGINT,
    provincia VARCHAR(50),
    zona VARCHAR(50),
    calle VARCHAR(50),
    cant_banhos INTEGER,
    cant_salas INTEGER,
    cant_dormitorios INTEGER,
    cantt_comedor INTEGER,
    barrio VARCHAR(50),
    cant_patios INTEGER,
    puntaje_total NUMERIC(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_formulario) REFERENCES formulario_socio_economico(id) ON DELETE CASCADE
);

-- Tenencia Vivienda
CREATE TABLE IF NOT EXISTS tenencia_vivienda (
    id BIGSERIAL PRIMARY KEY,
    id_formulario BIGINT,
    tipo_tenencia VARCHAR(50),
    detalle_tenencia VARCHAR(50),
    puntaje NUMERIC(3,1),
    puntaje_total NUMERIC(3,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_formulario) REFERENCES formulario_socio_economico(id) ON DELETE CASCADE
);

-- Tipo Tenencia Vivienda (Tabla paramétrica)
CREATE TABLE IF NOT EXISTS tipo_tenencia_vivienda (
    id BIGSERIAL PRIMARY KEY,
    id_tenencia_vivienda BIGINT,
    nombre VARCHAR(50),
    documento_adjuntar VARCHAR(50),
    puntaje NUMERIC(3,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tenencia_vivienda) REFERENCES tenencia_vivienda(id) ON DELETE CASCADE
);

-- =====================================================
-- PASO 6: POSTULACIONES Y TRÁMITES
-- =====================================================

-- Postulación
CREATE TABLE IF NOT EXISTS postulacion (
    id BIGSERIAL PRIMARY KEY,
    id_estudiante BIGINT,
    id_convocatoria BIGINT,
    id_formulario BIGINT,
    id_beca BIGINT,
    fecha_postulacion DATE,
    estado_postulado VARCHAR(50),
    motivo_rechazo VARCHAR(100),
    posicion_ranking INTEGER,
    puntaje_final NUMERIC(5,2),
    creado_por BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_convocatoria) REFERENCES convocatoria(id) ON DELETE CASCADE,
    FOREIGN KEY (id_formulario) REFERENCES formulario_socio_economico(id) ON DELETE CASCADE,
    FOREIGN KEY (id_beca) REFERENCES beca(id) ON DELETE SET NULL,
    FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL
);

-- Trámite
CREATE TABLE IF NOT EXISTS tramite (
    id BIGSERIAL PRIMARY KEY,
    id_postulacion BIGINT,
    codigo VARCHAR(50) NOT NULL,
    fecha_creacion DATE NOT NULL,
    clasificado VARCHAR(50) NOT NULL,
    fecha_clasificacion DATE,
    estado_actual BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_postulacion) REFERENCES postulacion(id) ON DELETE CASCADE,
    FOREIGN KEY (estado_actual) REFERENCES estado_tramite(id) ON DELETE SET NULL
);

-- Trámite Historial
CREATE TABLE IF NOT EXISTS tramite_historial (
    id SERIAL PRIMARY KEY,
    id_tramite BIGINT NOT NULL,
    observaciones VARCHAR(255) NOT NULL,
    revisador_por BIGINT NOT NULL,
    fecha_revision DATE NOT NULL,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tramite) REFERENCES tramite(id) ON DELETE CASCADE,
    FOREIGN KEY (revisador_por) REFERENCES users(id) ON DELETE CASCADE
);

-- Documento
CREATE TABLE IF NOT EXISTS documento (
    id BIGSERIAL PRIMARY KEY,
    id_tramite BIGINT,
    tipo_documento VARCHAR(50),
    nombre_archivo VARCHAR(100),
    ruta_digital VARCHAR(255),
    estado_fisico VARCHAR(100),
    digitalizado_por BIGINT,
    fecha_presentacion DATE,
    fecha_digitalizacion DATE,
    observaciones VARCHAR(255),
    motivo_rechazo VARCHAR(255),
    validado_por BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tramite) REFERENCES tramite(id) ON DELETE CASCADE,
    FOREIGN KEY (digitalizado_por) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (validado_por) REFERENCES users(id) ON DELETE SET NULL
);

-- Notificación
CREATE TABLE IF NOT EXISTS notificacion (
    id BIGSERIAL PRIMARY KEY,
    id_estudiante BIGINT,
    id_tramite BIGINT,
    tipo VARCHAR(50),
    titulo VARCHAR(100),
    mensaje VARCHAR(255),
    leido BOOLEAN DEFAULT FALSE,
    fecha_creacion DATE,
    fecha_lectura DATE,
    canal VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_tramite) REFERENCES tramite(id) ON DELETE CASCADE
);

-- =====================================================
-- PASO 7: ÍNDICES ADICIONALES PARA PERFORMANCE
-- =====================================================

CREATE INDEX IF NOT EXISTS idx_estudiante_nro_registro ON estudiante(nro_registro);
CREATE INDEX IF NOT EXISTS idx_beca_convocatoria ON beca(id_convocatoria);
CREATE INDEX IF NOT EXISTS idx_postulacion_estudiante ON postulacion(id_estudiante);
CREATE INDEX IF NOT EXISTS idx_postulacion_convocatoria ON postulacion(id_convocatoria);
CREATE INDEX IF NOT EXISTS idx_tramite_postulacion ON tramite(id_postulacion);
CREATE INDEX IF NOT EXISTS idx_tramite_estado ON tramite(estado_actual);
CREATE INDEX IF NOT EXISTS idx_documento_tramite ON documento(id_tramite);
CREATE INDEX IF NOT EXISTS idx_notificacion_estudiante ON notificacion(id_estudiante);
CREATE INDEX IF NOT EXISTS idx_formulario_estudiante ON formulario_socio_economico(id_estudiante);

-- =====================================================
-- PASO 8: DATOS INICIALES (SEEDERS)
-- =====================================================

-- Estados de Trámite
INSERT INTO estado_tramite (id, nombre, descripcion) VALUES
(1, 'PENDIENTE', 'Trámite pendiente de validación'),
(2, 'EN_VALIDACION', 'Documentación física en proceso de validación'),
(3, 'VALIDADO', 'Documentación física validada correctamente'),
(4, 'RECHAZADO', 'Documentación física rechazada'),
(5, 'EN_CLASIFICACION', 'En proceso de clasificación socioeconómica'),
(6, 'CLASIFICADO', 'Clasificación completada'),
(7, 'EN_DIGITALIZACION', 'Documentos en proceso de digitalización'),
(8, 'DIGITALIZADO', 'Documentos digitalizados'),
(9, 'APROBADO', 'Postulación aprobada'),
(10, 'DENEGADO', 'Postulación denegada')
ON CONFLICT (id) DO NOTHING;

-- Resetear secuencia
SELECT setval('estado_tramite_id_seq', (SELECT MAX(id) FROM estado_tramite));

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================

-- Mostrar resumen
SELECT
    'Migración completada exitosamente' as mensaje,
    COUNT(*) as total_tablas
FROM information_schema.tables
WHERE table_schema = 'public'
AND table_type = 'BASE TABLE';
