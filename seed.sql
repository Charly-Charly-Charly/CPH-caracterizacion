-- seed.sql - Esquema completo + catálogos por defecto para una base de datos nueva.
--
-- Fuente única de verdad del esquema inicial. includes/db.php lo ejecuta
-- automáticamente la primera vez que no existe database.sqlite. El panel de
-- admin (futuro) también parte de este archivo para generar la base de cada
-- PC, sustituyendo los catálogos por los que defina cada comisión si aplica.

CREATE TABLE IF NOT EXISTS Nucleo (
    id TEXT PRIMARY KEY, Nombre TEXT NOT NULL, Segundo_Nombre TEXT, Primer_Apellido TEXT NOT NULL,
    Segundo_Apellido TEXT, Apellido_Casada TEXT, DPI TEXT NOT NULL UNIQUE, Fecha_Nacimiento TEXT NOT NULL,
    Sexo TEXT, Telefono TEXT, Persona_Beneficiaria_MC TEXT NOT NULL, Parentesco_MC TEXT, Afinidad_MC TEXT,
    Pertenencia_Cultural TEXT, Comunidad_Linguistica TEXT, creado_por TEXT,
    Desconoce_Fecha INTEGER DEFAULT 0, Edad INTEGER,
    sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Familiares (
    id TEXT PRIMARY KEY, nucleo_id TEXT NOT NULL, Nombre TEXT NOT NULL,
    Parentesco TEXT, Afinidad TEXT, Estado_Civil TEXT, Fecha_Nacimiento TEXT, Sexo TEXT,
    Embarazada INTEGER, Atencion_Prenatal INTEGER, Tipo_Atencion_Prenatal TEXT,
    Tiene_Discapacidad INTEGER, Tipo_Discapacidad TEXT, Sabe_Leer INTEGER, Sabe_Escribir INTEGER,
    Escolaridad TEXT, Estudia_Actualmente INTEGER, Razon_Abandono TEXT, Actualmente_Labora INTEGER,
    Ingresos_Mensuales REAL, Viajan_Trabajo INTEGER, Tiempo_Viaje INTEGER, creado_por TEXT,
    Tiene_DPI INTEGER DEFAULT 0, Tiene_Certificado_Nacimiento INTEGER DEFAULT 0,
    Desconoce_Fecha INTEGER DEFAULT 0, Edad INTEGER,
    sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Enfermedades (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, categoria TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Familiar_Enfermedades (id TEXT PRIMARY KEY, familiar_id TEXT NOT NULL, enfermedad_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (enfermedad_id) REFERENCES Enfermedades(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Trabajos (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Familiar_Trabajos (id TEXT PRIMARY KEY, familiar_id TEXT NOT NULL, trabajo_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (trabajo_id) REFERENCES Trabajos(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Fuentes_Ingreso (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Familiar_Fuentes (id TEXT PRIMARY KEY, familiar_id TEXT NOT NULL, fuente_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (fuente_id) REFERENCES Fuentes_Ingreso(id) ON DELETE CASCADE);

CREATE TABLE IF NOT EXISTS Vivienda (
    id TEXT PRIMARY KEY, nucleo_id TEXT, Cantidad_Ambientes INTEGER, Techo TEXT, Paredes TEXT, Suelo TEXT, Tiene_Sanitario INTEGER,
    creado_por TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Energia_Cocina (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Energia_Cocina (id TEXT PRIMARY KEY, vivienda_id TEXT, energia_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (energia_id) REFERENCES Energia_Cocina(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Alumbrado (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Alumbrado (id TEXT PRIMARY KEY, vivienda_id TEXT, alumbrado_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (alumbrado_id) REFERENCES Alumbrado(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Agua_Hogar (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Agua_Hogar (id TEXT PRIMARY KEY, vivienda_id TEXT, agua_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (agua_id) REFERENCES Agua_Hogar(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Agua_Consumo (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Agua_Consumo (id TEXT PRIMARY KEY, vivienda_id TEXT, agua_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (agua_id) REFERENCES Agua_Consumo(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Ubicacion_Animales (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Animales (id TEXT PRIMARY KEY, vivienda_id TEXT, ubicacion_id TEXT, conviven_hogar INTEGER DEFAULT 0, espacio_aislado INTEGER DEFAULT 0, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (ubicacion_id) REFERENCES Ubicacion_Animales(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Tipo_Sanitario (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Sanitario (id TEXT PRIMARY KEY, vivienda_id TEXT, sanitario_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (sanitario_id) REFERENCES Tipo_Sanitario(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Aguas_Grises (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Aguas_Grises (id TEXT PRIMARY KEY, vivienda_id TEXT, aguas_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (aguas_id) REFERENCES Aguas_Grises(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Manejo_Basura (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Vivienda_Basura (id TEXT PRIMARY KEY, vivienda_id TEXT, basura_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (basura_id) REFERENCES Manejo_Basura(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Animales_Domesticos (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Nucleo_Animales_Domesticos (id TEXT PRIMARY KEY, nucleo_id TEXT, animal_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (animal_id) REFERENCES Animales_Domesticos(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Ganado (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Nucleo_Ganado (id TEXT PRIMARY KEY, nucleo_id TEXT, ganado_id TEXT, es_personal INTEGER DEFAULT 0, es_comercial INTEGER DEFAULT 0, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (ganado_id) REFERENCES Ganado(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Cultivos (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Nucleo_Cultivos (id TEXT PRIMARY KEY, nucleo_id TEXT, cultivo_id TEXT, es_personal INTEGER DEFAULT 0, es_comercial INTEGER DEFAULT 0, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (cultivo_id) REFERENCES Cultivos(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Arboles (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Cultivo_Arboles (id TEXT PRIMARY KEY, nucleo_id TEXT NOT NULL, tiene_cultivo INTEGER DEFAULT 0, creado_por TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Cultivo_Arboles_Detalle (id TEXT PRIMARY KEY, cultivo_id TEXT NOT NULL, arbol_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (cultivo_id) REFERENCES Cultivo_Arboles(id) ON DELETE CASCADE, FOREIGN KEY (arbol_id) REFERENCES Arboles(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Actividades_Bosque (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, descripcion TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS Nucleo_Actividades_Bosque (id TEXT PRIMARY KEY, nucleo_id TEXT NOT NULL, actividad_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (actividad_id) REFERENCES Actividades_Bosque(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS Fallecidos (
    id TEXT PRIMARY KEY, nucleo_id TEXT, Nombre TEXT, Sexo TEXT, Parentesco TEXT, Fecha_Nacimiento TEXT, Fecha_Fallecimiento TEXT, Causa_Fallecimiento TEXT, Lugar_Inhumacion TEXT,
    Desconoce_Fecha INTEGER DEFAULT 0, Edad INTEGER,
    creado_por TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
);

-- Catálogos (valores por defecto; una comisión puede sustituirlos antes de generar sus PCs)

INSERT INTO Enfermedades (id, nombre, categoria) VALUES
    (lower(hex(randomblob(16))), 'Gripe', 'COMUN'),
    (lower(hex(randomblob(16))), 'Gastritis', 'COMUN'),
    (lower(hex(randomblob(16))), 'Dengue', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Diabetes', 'CRONICA'),
    (lower(hex(randomblob(16))), 'Hipertensión', 'CRONICA'),
    (lower(hex(randomblob(16))), 'Cancer', 'CRONICA'),
    (lower(hex(randomblob(16))), 'Asma', 'CRONICA'),
    (lower(hex(randomblob(16))), 'Artritis', 'CRONICA'),
    (lower(hex(randomblob(16))), 'Desnutrición', 'TALLA_PESO'),
    (lower(hex(randomblob(16))), 'Talla Baja', 'TALLA_PESO'),
    (lower(hex(randomblob(16))), 'Diarrea Aguda', 'COMUN'),
    (lower(hex(randomblob(16))), 'Colera', 'COMUN'),
    (lower(hex(randomblob(16))), 'Chikunguya', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Envermedades Oculares', 'COMUN'),
    (lower(hex(randomblob(16))), 'Paratosisis Intestinal', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Zika', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Paludismo', 'CRONICA'),
    (lower(hex(randomblob(16))), 'Tifoidea', 'COMUN'),
    (lower(hex(randomblob(16))), 'Infecciones respiratorias', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Hepatitis', 'COMUN'),
    (lower(hex(randomblob(16))), 'Enfermedades de la piel', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Enfermedades de transmisión sexual', 'TRANSMISION'),
    (lower(hex(randomblob(16))), 'Infección Urinaria', 'COMUN'),
    (lower(hex(randomblob(16))), 'Enfermedades Renales', 'CRONICA'),
    (lower(hex(randomblob(16))), 'Sarampion', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Viruela', 'VIRAL'),
    (lower(hex(randomblob(16))), 'Infección Vaginal', 'COMUN');

INSERT INTO Trabajos (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Jornalero'),
    (lower(hex(randomblob(16))), 'Agente de Seguridad'),
    (lower(hex(randomblob(16))), 'Albañileria'),
    (lower(hex(randomblob(16))), 'Ganado'),
    (lower(hex(randomblob(16))), 'Servicio Domestico'),
    (lower(hex(randomblob(16))), 'Costurera'),
    (lower(hex(randomblob(16))), 'Tejido'),
    (lower(hex(randomblob(16))), 'Electrisista'),
    (lower(hex(randomblob(16))), 'Carpintero'),
    (lower(hex(randomblob(16))), 'Cuidador de Animales'),
    (lower(hex(randomblob(16))), 'Bordadora'),
    (lower(hex(randomblob(16))), 'Comercio'),
    (lower(hex(randomblob(16))), 'Agricultura'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Fuentes_Ingreso (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Remesas'),
    (lower(hex(randomblob(16))), 'Jubilaciones'),
    (lower(hex(randomblob(16))), 'Donaciones'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Energia_Cocina (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Leña'),
    (lower(hex(randomblob(16))), 'Electricidad'),
    (lower(hex(randomblob(16))), 'Gas'),
    (lower(hex(randomblob(16))), 'Carbon'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Alumbrado (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Energia Electrica'),
    (lower(hex(randomblob(16))), 'Gas Corriente'),
    (lower(hex(randomblob(16))), 'Panel Solar'),
    (lower(hex(randomblob(16))), 'Generador a base de combustible'),
    (lower(hex(randomblob(16))), 'Candelas / Velas Artesanales'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Agua_Hogar (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Nacimiento de Agua'),
    (lower(hex(randomblob(16))), 'Laguna'),
    (lower(hex(randomblob(16))), 'Compra de Agua a otra Comunidad'),
    (lower(hex(randomblob(16))), 'Pozo Bombeado'),
    (lower(hex(randomblob(16))), 'Pozo Artesanal'),
    (lower(hex(randomblob(16))), 'Aguada'),
    (lower(hex(randomblob(16))), 'Captación de Agua de Lluvia'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Agua_Consumo (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Agua Embotellada'),
    (lower(hex(randomblob(16))), 'Agua Filtrada'),
    (lower(hex(randomblob(16))), 'Agua Hervida'),
    (lower(hex(randomblob(16))), 'Agua Clorada'),
    (lower(hex(randomblob(16))), 'Agua directa de un cuerpo de Agua'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Ubicacion_Animales (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Perros'),
    (lower(hex(randomblob(16))), 'Gatos'),
    (lower(hex(randomblob(16))), 'Gallinas'),
    (lower(hex(randomblob(16))), 'Patos'),
    (lower(hex(randomblob(16))), 'Cerdos'),
    (lower(hex(randomblob(16))), 'Caballos'),
    (lower(hex(randomblob(16))), 'Vacas'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Tipo_Sanitario (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Pozo Ciego'),
    (lower(hex(randomblob(16))), 'Letrina Abonera'),
    (lower(hex(randomblob(16))), 'Sanitario de Losa con fosa septica'),
    (lower(hex(randomblob(16))), 'Sin instalación / Aire Libre'),
    (lower(hex(randomblob(16))), 'Inodoro'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Aguas_Grises (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Drenaje'),
    (lower(hex(randomblob(16))), 'Sumideros'),
    (lower(hex(randomblob(16))), 'Desemboca a cuerpo de Agua'),
    (lower(hex(randomblob(16))), 'Sin Instalación / Aire Libre'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Manejo_Basura (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Quema de Basura'),
    (lower(hex(randomblob(16))), 'Enterrarla'),
    (lower(hex(randomblob(16))), 'Vertedero'),
    (lower(hex(randomblob(16))), 'Tira a un barranco'),
    (lower(hex(randomblob(16))), 'Servicio Municipal o Privado'),
    (lower(hex(randomblob(16))), 'La tira a un Cuerpo de Agua (Laguna)'),
    (lower(hex(randomblob(16))), 'Reciclaje'),
    (lower(hex(randomblob(16))), 'Composta'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Animales_Domesticos (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Perros'),
    (lower(hex(randomblob(16))), 'Gatos'),
    (lower(hex(randomblob(16))), 'Loros'),
    (lower(hex(randomblob(16))), 'Conejos'),
    (lower(hex(randomblob(16))), 'Peces'),
    (lower(hex(randomblob(16))), 'Aves de corral menores'),
    (lower(hex(randomblob(16))), 'Otro'),
    (lower(hex(randomblob(16))), 'Perro'),
    (lower(hex(randomblob(16))), 'Gato');

INSERT INTO Ganado (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Gallinas'),
    (lower(hex(randomblob(16))), 'Gallos'),
    (lower(hex(randomblob(16))), 'Patos'),
    (lower(hex(randomblob(16))), 'Chompipe / Chuntos'),
    (lower(hex(randomblob(16))), 'Vacas'),
    (lower(hex(randomblob(16))), 'Caballos'),
    (lower(hex(randomblob(16))), 'Toros'),
    (lower(hex(randomblob(16))), 'Cabras'),
    (lower(hex(randomblob(16))), 'Otros');

INSERT INTO Cultivos (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Maíz'),
    (lower(hex(randomblob(16))), 'Frijol'),
    (lower(hex(randomblob(16))), 'Limon'),
    (lower(hex(randomblob(16))), 'Paterna'),
    (lower(hex(randomblob(16))), 'Cardamomo'),
    (lower(hex(randomblob(16))), 'Chile'),
    (lower(hex(randomblob(16))), 'Caxlut'),
    (lower(hex(randomblob(16))), 'Café'),
    (lower(hex(randomblob(16))), 'Cacao'),
    (lower(hex(randomblob(16))), 'Achiote'),
    (lower(hex(randomblob(16))), 'Papaya'),
    (lower(hex(randomblob(16))), 'Jocote'),
    (lower(hex(randomblob(16))), 'Tomate'),
    (lower(hex(randomblob(16))), 'Camote'),
    (lower(hex(randomblob(16))), 'Mania'),
    (lower(hex(randomblob(16))), 'Nance'),
    (lower(hex(randomblob(16))), 'Aguacate'),
    (lower(hex(randomblob(16))), 'Ayote'),
    (lower(hex(randomblob(16))), 'Arroz'),
    (lower(hex(randomblob(16))), 'Ajonjoli'),
    (lower(hex(randomblob(16))), 'Pepitoria / Chigua'),
    (lower(hex(randomblob(16))), 'Yuca'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Arboles (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Pino'),
    (lower(hex(randomblob(16))), 'Teca'),
    (lower(hex(randomblob(16))), 'Caoba'),
    (lower(hex(randomblob(16))), 'Cedro'),
    (lower(hex(randomblob(16))), 'Frutales'),
    (lower(hex(randomblob(16))), 'Otro');

INSERT INTO Actividades_Bosque (id, nombre) VALUES
    (lower(hex(randomblob(16))), 'Cuidado contra incendios'),
    (lower(hex(randomblob(16))), 'Vigilancia de la Zona'),
    (lower(hex(randomblob(16))), 'Limpieza y mantenimiento'),
    (lower(hex(randomblob(16))), 'Uso de medicina natural'),
    (lower(hex(randomblob(16))), 'Ninguna Actividad'),
    (lower(hex(randomblob(16))), 'Otro'),
    (lower(hex(randomblob(16))), 'Venta de Madera'),
    (lower(hex(randomblob(16))), 'Roza');

