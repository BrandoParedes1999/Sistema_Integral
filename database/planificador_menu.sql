-- ============================================================
-- Módulo: Planificador de Menú (pm_*)
-- Base de datos: unisalud
-- ============================================================
-- Ejecutar en la base de datos `unisalud` existente.
-- Si las tablas ya existen, este script las omite (IF NOT EXISTS).
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- pm_dias_semana
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_dias_semana` (
  `id_dia`     int(11)     NOT NULL AUTO_INCREMENT,
  `nombre_dia` varchar(30) NOT NULL,
  `orden`      int(11)     NOT NULL,
  PRIMARY KEY (`id_dia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pm_dias_semana` (`id_dia`, `nombre_dia`, `orden`) VALUES
(1, 'Lunes',      1),
(2, 'Martes',     2),
(3, 'Miércoles',  3),
(4, 'Jueves',     4),
(5, 'Viernes',    5),
(6, 'Sábado',     6),
(7, 'Domingo',    7);

-- ------------------------------------------------------------
-- pm_grupos_alimento
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_grupos_alimento` (
  `id_grupo_alimento` int(11)      NOT NULL AUTO_INCREMENT,
  `nombre_grupo`      varchar(100) NOT NULL,
  `color`             varchar(20)  DEFAULT NULL,
  `icono`             varchar(20)  DEFAULT NULL,
  PRIMARY KEY (`id_grupo_alimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pm_grupos_alimento` (`id_grupo_alimento`, `nombre_grupo`, `color`, `icono`) VALUES
(1, 'Verdura',                    '#9AD14B', '🥦'),
(2, 'Fruta',                      '#00B050', '🍎'),
(3, 'Cereal',                     '#FFE66D', '🌾'),
(4, 'Leguminosas',                '#BFA600', '🫘'),
(5, 'Alimentos de origen animal', '#FF5C5C', '🍗'),
(6, 'Leche',                      '#F5F5F5', '🥛'),
(7, 'Aceites y grasas',           '#D9A7E0', '🥑'),
(8, 'Azúcar',                     '#BFBFBF', '🍯'),
(9, 'Alimento libre',             '#008DD2', '☕');

-- ------------------------------------------------------------
-- pm_ingredientes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_ingredientes` (
  `id_ingrediente`    int(11)      NOT NULL AUTO_INCREMENT,
  `nombre_ingrediente` varchar(120) NOT NULL,
  `id_grupo_alimento` int(11)      DEFAULT NULL,
  PRIMARY KEY (`id_ingrediente`),
  KEY `id_grupo_alimento` (`id_grupo_alimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pm_ingredientes` (`id_ingrediente`, `nombre_ingrediente`, `id_grupo_alimento`) VALUES
(1,  'Aceite', 7),
(2,  'Aceite de oliva', 7),
(3,  'Aguacate', 7),
(4,  'Ajo', 9),
(5,  'Arroz cocido', 3),
(6,  'Atún en agua', 5),
(7,  'Avena', 3),
(8,  'Bolillo integral', 3),
(9,  'Canela', 9),
(10, 'Carne de res magra', 5),
(11, 'Cebolla', 1),
(12, 'Champiñones', 1),
(13, 'Chile en polvo', 9),
(14, 'Cilantro', 1),
(15, 'Claras de huevo', 5),
(16, 'Crema de cacahuate natural', 7),
(17, 'Ensalada verde', 1),
(18, 'Especias', 9),
(19, 'Espinaca', 1),
(20, 'Fresas', 2),
(21, 'Frijoles cocidos', 4),
(22, 'Frijoles negros cocidos', 4),
(23, 'Frijoles refritos bajos en grasa', 4),
(24, 'Frijoles refritos sin grasa', 4),
(25, 'Galletas integrales', 3),
(26, 'Galletas saladas integrales', 3),
(27, 'Granola', 3),
(28, 'Huevo', 5),
(29, 'Huevo cocido', 5),
(30, 'Hummus', 4),
(31, 'Jitomate', 1),
(32, 'Jugo de limón', 9),
(33, 'Leche descremada', 6),
(34, 'Lechuga', 1),
(35, 'Limón', 9),
(36, 'Mango', 2),
(37, 'Manzana', 2),
(38, 'Miel', 8),
(39, 'Nopales cocidos', 1),
(40, 'Nuez', 7),
(41, 'Palomitas naturales', 3),
(42, 'Pan integral', 3),
(43, 'Pan integral tostado', 3),
(44, 'Pan pita integral', 3),
(45, 'Papa cocida', 3),
(46, 'Pasta cocida', 3),
(47, 'Pechuga de pavo', 5),
(48, 'Pechuga de pollo a la plancha', 5),
(49, 'Pechuga de pollo asada', 5),
(50, 'Pechuga de pollo cocida', 5),
(51, 'Pechuga de pollo en cubos', 5),
(52, 'Pepino', 1),
(53, 'Pescado blanco', 5),
(54, 'Pico de gallo', 1),
(55, 'Pimienta', 9),
(56, 'Plátano', 2),
(57, 'Pollo asado', 5),
(58, 'Pollo cocido', 5),
(59, 'Pollo deshebrado', 5),
(60, 'Pollo desmenuzado', 5),
(61, 'Queso Oaxaca o panela', 5),
(62, 'Queso fresco', 5),
(63, 'Queso panela', 5),
(64, 'Queso panela o manchego ligero', 5),
(65, 'Sal', 9),
(66, 'Salsa', 9),
(67, 'Salsa mexicana', 9),
(68, 'Salsa roja', 9),
(69, 'Salsa roja o verde', 9),
(70, 'Sopa de verduras casera', 1),
(71, 'Tortilla de harina integral', 3),
(72, 'Tortilla de maíz', 3),
(73, 'Tortilla integral', 3),
(74, 'Tortillas de maíz horneadas', 3),
(75, 'Tostada horneada', 3),
(76, 'Uvas', 2),
(77, 'Verduras al vapor', 1),
(78, 'Verduras mixtas', 1),
(79, 'Verduras salteadas', 1),
(80, 'Yogur griego natural', 6),
(81, 'Yogur griego natural bajo en grasa', 6),
(82, 'Yogur natural', 6),
(83, 'Yogur natural bajo en grasa', 6),
(84, 'Zanahoria', 1);

-- ------------------------------------------------------------
-- pm_tiempos_comida
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_tiempos_comida` (
  `id_tiempo`    int(11)     NOT NULL AUTO_INCREMENT,
  `nombre_tiempo` varchar(80) NOT NULL,
  `orden`        int(11)     NOT NULL,
  PRIMARY KEY (`id_tiempo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pm_tiempos_comida` (`id_tiempo`, `nombre_tiempo`, `orden`) VALUES
(1, 'Desayuno',              1),
(2, 'Colación matutina',     2),
(3, 'Comida',                3),
(4, 'Colación vespertina',   4),
(5, 'Cena',                  5);

-- ------------------------------------------------------------
-- pm_menu_alumno
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_menu_alumno` (
  `id_menu_alumno`   int(11)     NOT NULL AUTO_INCREMENT,
  `matricula_alum`   varchar(50) NOT NULL,
  `objetivo_calorico` int(11)    DEFAULT 2000,
  `fecha_creacion`   timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_menu_alumno`),
  UNIQUE KEY `menu_unico_alumno` (`matricula_alum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- pm_menu_detalle
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_menu_detalle` (
  `id_detalle`     int(11)   NOT NULL AUTO_INCREMENT,
  `id_menu_alumno` int(11)   NOT NULL,
  `id_dia`         int(11)   NOT NULL,
  `id_tiempo`      int(11)   NOT NULL,
  `id_platillo`    int(11)   NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  UNIQUE KEY `seleccion_unica` (`id_menu_alumno`, `id_dia`, `id_tiempo`),
  KEY `id_dia`         (`id_dia`),
  KEY `id_tiempo`      (`id_tiempo`),
  KEY `id_platillo`    (`id_platillo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- pm_platillos
-- Images are relative to the browser URL of planificador_menu.php
-- Actual files go in: alumnos/planificador/platillos/{desayunos,colaciones,comidas,cenas}/
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_platillos` (
  `id_platillo`    int(11)      NOT NULL AUTO_INCREMENT,
  `nombre_platillo` varchar(150) NOT NULL,
  `descripcion`    text         DEFAULT NULL,
  `preparacion`    text         DEFAULT NULL,
  `calorias`       int(11)      NOT NULL,
  `id_tiempo`      int(11)      NOT NULL,
  `imagen`         varchar(255) DEFAULT NULL,
  `activo`         tinyint(4)   DEFAULT 1,
  PRIMARY KEY (`id_platillo`),
  KEY `id_tiempo` (`id_tiempo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pm_platillos` (`id_platillo`, `nombre_platillo`, `descripcion`, `preparacion`, `calorias`, `id_tiempo`, `imagen`, `activo`) VALUES
(1,  'Yogur con fruta y avena', 'Yogur griego natural con fresas, avena y canela.', 'Coloca el yogur en un recipiente. Agrega las fresas y la avena. Espolvorea canela y mezcla.', 190, 1, '../planificador/platillos/desayunos/yogurt_fruta_av.jpg', 1),
(2,  'Tostada con aguacate y huevo', 'Tostada integral con aguacate y huevo cocido.', 'Tuesta el pan. Machaca el aguacate y úntalo sobre la tostada. Rebana el huevo y colócalo encima. Agrega sal y pimienta.', 200, 1, '../planificador/platillos/desayunos/tostada_hvo.jpg', 1),
(3,  'Omelette de claras con verduras', 'Omelette ligero con claras de huevo, espinaca, champiñones y queso panela.', 'Saltea las verduras con el aceite. Agrega las claras batidas. Cuando empiece a cocerse, añade el queso. Dobla el omelette y sirve.', 180, 1, '../planificador/platillos/desayunos/omelette_cla_ver.png', 1),
(4,  'Quesadilla ligera', 'Quesadilla de maíz con queso panela, nopales y salsa.', 'Coloca el queso en la tortilla y calienta en un comal. Dobla la tortilla y cocina hasta que el queso se derrita. Sirve con nopales y salsa.', 200, 1, '../planificador/platillos/desayunos/ques.jpeg', 1),
(5,  'Avena con plátano y nuez', 'Avena cocida con leche descremada, plátano y nuez.', 'Hierve la leche y agrega la avena. Cocina durante 5 minutos, moviendo constantemente. Sirve y añade el plátano en rodajas y la nuez.', 300, 1, '../planificador/platillos/desayunos/avena_platano.jpeg', 1),
(6,  'Mollete ligero', 'Mollete integral con frijoles bajos en grasa, queso panela, pico de gallo y aguacate.', 'Tuesta el pan. Unta los frijoles y agrega el queso. Gratina ligeramente. Sirve con pico de gallo y aguacate.', 310, 1, '../planificador/platillos/desayunos/mollete_ligero.jpeg', 1),
(7,  'Yogur con fruta y granola', 'Yogur griego natural con mango, granola y canela.', 'Coloca el yogur en un recipiente. Añade el mango y la granola. Espolvorea canela.', 300, 1, '../planificador/platillos/desayunos/yogurth_granola.jpeg', 1),
(8,  'Huevos revueltos con tortilla y aguacate', 'Huevos revueltos con jitomate, cebolla, tortillas y aguacate.', 'Sofríe el jitomate y la cebolla con el aceite. Agrega los huevos y revuelve hasta cocer. Sirve con las tortillas y el aguacate.', 400, 1, '../planificador/platillos/desayunos/huevo.jpeg', 1),
(9,  'Sándwich de pavo y queso', 'Sándwich integral con pechuga de pavo, queso, lechuga, jitomate y manzana.', 'Arma el sándwich con todos los ingredientes. Acompaña con la manzana.', 400, 1, '../planificador/platillos/desayunos/pacvo.jpeg', 1),
(10, 'Hotcakes de avena y plátano', 'Hotcakes preparados con avena, huevo, plátano y miel.', 'Licúa la avena, el huevo y el plátano. Cocina pequeños hotcakes en un sartén ligeramente engrasado. Sirve con la miel.', 390, 1, '../planificador/platillos/desayunos/hotcakes.jpeg', 1),
(11, 'Chilaquiles ligeros', 'Chilaquiles con totopos horneados, salsa, queso fresco, huevo y cebolla.', 'Calienta la salsa y agrega los totopos horneados. Mezcla rápidamente para que no se remojen demasiado. Añade el queso, la cebolla y el huevo encima.', 400, 1, '../planificador/platillos/desayunos/chilaquiles.jpeg', 1),
(12, 'Manzana con canela', 'Manzana mediana con canela.', 'Lava y corta la manzana en gajos. Espolvorea canela y consume.', 95, 2, '../planificador/platillos/colaciones/manzana_canela.jpeg', 1),
(13, 'Pepino con limón', 'Pepino con jugo de limón y chile opcional.', 'Corta el pepino en rodajas. Agrega limón y chile al gusto.', 50, 2, '../planificador/platillos/colaciones/pepino.png', 1),
(14, 'Yogur natural', 'Yogur natural bajo en grasa servido frío.', 'Sirve frío y consume.', 100, 2, '../planificador/platillos/colaciones/yogurt.jpg', 1),
(15, 'Uvas y queso panela', 'Uvas acompañadas con queso panela en cubos.', 'Lava las uvas. Acompaña con el queso en cubos.', 150, 2, '../planificador/platillos/colaciones/uvas.png', 1),
(16, 'Palomitas naturales', 'Palomitas naturales preparadas al aire.', 'Prepara las palomitas sin mantequilla. Agrega una pizca de sal.', 140, 2, '../planificador/platillos/colaciones/palomitas.png', 1),
(17, 'Tostada de aguacate', 'Tostada horneada con aguacate, jitomate y limón.', 'Machaca el aguacate. Úntalo sobre la tostada. Agrega jitomate, limón y sal.', 150, 2, '../planificador/platillos/colaciones/tostadas_aguacate.jpg', 1),
(18, 'Yogur con nueces', 'Yogur griego natural con nuez picada.', 'Mezcla el yogur con la nuez. Consume frío.', 200, 2, '../planificador/platillos/colaciones/yogurt_nueces.png', 1),
(19, 'Plátano con crema de cacahuate', 'Plátano acompañado con crema de cacahuate natural.', 'Corta el plátano en rodajas. Acompaña con la crema de cacahuate.', 200, 2, '../planificador/platillos/colaciones/platano_cacahuate.png', 1),
(20, 'Rollitos de pavo y queso', 'Rollitos de pechuga de pavo con queso panela y zanahoria.', 'Enrolla el queso con las rebanadas de pavo. Acompaña con la zanahoria.', 190, 2, '../planificador/platillos/colaciones/rollitos_pavo.jpeg', 1),
(21, 'Licuado de fresa', 'Licuado con leche descremada, fresas y avena.', 'Licúa todos los ingredientes. Sirve inmediatamente.', 200, 2, '../planificador/platillos/colaciones/licuado_fresa.jpg', 1),
(22, 'Hummus con verduras', 'Hummus acompañado con bastones de zanahoria y pepino.', 'Lava y corta las verduras. Utiliza el hummus como dip.', 200, 2, '../planificador/platillos/colaciones/Hummus_verduras.png', 1),
(23, 'Manzana con canela', 'Manzana mediana con canela.', 'Lava y corta la manzana en gajos. Espolvorea canela y consume.', 95, 4, '../planificador/platillos/colaciones/manzana_canela.jpeg', 1),
(24, 'Pepino con limón', 'Pepino con jugo de limón y chile opcional.', 'Corta el pepino en rodajas. Agrega limón y chile al gusto.', 50, 4, '../planificador/platillos/colaciones/pepino.png', 1),
(25, 'Yogur natural', 'Yogur natural bajo en grasa servido frío.', 'Sirve frío y consume.', 100, 4, '../planificador/platillos/colaciones/yogurt.jpg', 1),
(26, 'Uvas y queso panela', 'Uvas acompañadas con queso panela en cubos.', 'Lava las uvas. Acompaña con el queso en cubos.', 150, 4, '../planificador/platillos/colaciones/uvas.png', 1),
(27, 'Palomitas naturales', 'Palomitas naturales preparadas al aire.', 'Prepara las palomitas sin mantequilla. Agrega una pizca de sal.', 140, 4, '../planificador/platillos/colaciones/palomitas.png', 1),
(28, 'Tostada de aguacate', 'Tostada horneada con aguacate, jitomate y limón.', 'Machaca el aguacate. Úntalo sobre la tostada. Agrega jitomate, limón y sal.', 150, 4, '../planificador/platillos/colaciones/tostadas_aguacate.jpg', 1),
(29, 'Yogur con nueces', 'Yogur griego natural con nuez picada.', 'Mezcla el yogur con la nuez. Consume frío.', 200, 4, '../planificador/platillos/colaciones/yogurt_nueces.png', 1),
(30, 'Plátano con crema de cacahuate', 'Plátano acompañado con crema de cacahuate natural.', 'Corta el plátano en rodajas. Acompaña con la crema de cacahuate.', 200, 4, '../planificador/platillos/colaciones/platano_cacahuate.png', 1),
(31, 'Rollitos de pavo y queso', 'Rollitos de pechuga de pavo con queso panela y zanahoria.', 'Enrolla el queso con las rebanadas de pavo. Acompaña con la zanahoria.', 190, 4, '../planificador/platillos/colaciones/rollitos_pavo.jpeg', 1),
(32, 'Licuado de fresa', 'Licuado con leche descremada, fresas y avena.', 'Licúa todos los ingredientes. Sirve inmediatamente.', 200, 4, '../planificador/platillos/colaciones/licuado_fresa.jpg', 1),
(33, 'Hummus con verduras', 'Hummus acompañado con bastones de zanahoria y pepino.', 'Lava y corta las verduras. Utiliza el hummus como dip.', 200, 4, '../planificador/platillos/colaciones/Hummus_verduras.png', 1),
(34, 'Ensalada de pollo y manzana', 'Ensalada ligera con pollo desmenuzado, lechuga y manzana.', 'Mezcla la lechuga y la manzana en un plato. Añade el pollo desmenuzado. Agrega el aceite de oliva, el jugo de limón, sal y pimienta. Revuelve y sirve.', 300, 3, '../planificador/platillos/comidas/Ensalada_pollo_manzana.png', 1),
(35, 'Tostadas de atún', 'Tostadas horneadas con atún, pico de gallo, aguacate y lechuga.', 'Mezcla el atún con el pico de gallo. Coloca la lechuga sobre las tostadas. Agrega el atún y el aguacate en rebanadas. Exprime limón antes de comer.', 300, 3, '../planificador/platillos/comidas/tostadas_atun.png', 1),
(36, 'Bowl de arroz, pollo y verduras', 'Bowl balanceado con arroz cocido, pollo asado y verduras al vapor.', 'Coloca el arroz en un tazón. Añade el pollo en cubos. Agrega las verduras. Aliña con aceite, limón, sal y pimienta.', 400, 3, '../planificador/platillos/comidas/Bowl_arroz_pollo_verduras.png', 1),
(37, 'Enchiladas ligeras de pollo', 'Enchiladas de tortilla de maíz rellenas de pollo con salsa roja y queso fresco.', 'Calienta las tortillas. Rellénalas con el pollo y dóblalas. Baña con la salsa caliente. Agrega queso y cebolla.', 400, 3, '../planificador/platillos/comidas/Enchiladas_ligeras_pollo.png', 1),
(38, 'Filete de pescado con verduras', 'Pescado blanco a la plancha con papa cocida y verduras salteadas.', 'Sazona el pescado con ajo, limón y especias. Cocínalo a la plancha. Saltea las verduras con el aceite. Sirve junto con la papa cocida.', 400, 3, '../planificador/platillos/comidas/Filete_pescado_verduras.png', 1),
(39, 'Burrito ligero de pollo', 'Burrito con pollo, frijoles, verduras y aderezo de yogur natural.', 'Calienta la tortilla. Coloca el pollo, frijoles y verduras. Añade el yogur como aderezo. Enrolla y sirve.', 400, 3, '../planificador/platillos/comidas/Burrito_ligero_pollo.jpg', 1),
(40, 'Tacos de carne asada', 'Tacos de carne magra con tortilla de maíz, salsa, cebolla, cilantro y aguacate.', 'Asa la carne y córtala en tiras. Calienta las tortillas. Rellénalas con la carne. Añade cebolla, cilantro, salsa y aguacate.', 500, 3, '../planificador/platillos/comidas/Tacos_carne_asada.jpg', 1),
(41, 'Ensalada completa de atún', 'Ensalada completa con atún, huevo cocido, verduras mixtas, aceite de oliva y galletas integrales.', 'Mezcla las verduras y el atún. Agrega los huevos en cuartos. Aliña con el aceite de oliva. Acompaña con las galletas.', 500, 3, '../planificador/platillos/comidas/Ensalada_atun.png', 1),
(42, 'Pasta con pollo y verduras', 'Pasta cocida mezclada con pollo en cubos y verduras salteadas.', 'Cocina la pasta según las instrucciones. Saltea el pollo hasta que esté cocido. Añade las verduras y cocina 2 minutos más. Mezcla con la pasta y sirve.', 500, 3, '../planificador/platillos/comidas/Pasta_pollo_verduras.jpg', 1),
(43, 'Bowl mexicano', 'Bowl con pollo asado, arroz, frijoles negros, aguacate, pico de gallo y lechuga.', 'Coloca una base de lechuga. Agrega arroz, frijoles y pollo. Añade aguacate y pico de gallo. Mezcla antes de comer.', 500, 3, '../planificador/platillos/comidas/Bowl_mexicano.png', 1),
(44, 'Quesadillas de pollo y verduras', 'Quesadillas con pollo, queso y nopales, acompañadas con salsa.', 'Rellena las tortillas con pollo y queso. Calienta en comal hasta que el queso se derrita. Sirve con nopales y salsa.', 400, 5, '../planificador/platillos/cenas/Quesadillas_pollo_verduras.jpg', 1),
(45, 'Ensalada mediterránea', 'Ensalada con pollo a la plancha, verduras frescas, queso fresco y pan pita integral.', 'Mezcla las verduras. Añade el pollo en tiras y el queso. Aliña con el aceite. Sirve con el pan pita.', 400, 5, '../planificador/platillos/cenas/Ensalada_mediterránea.jpg', 1),
(46, 'Molletes ligeros', 'Molletes con bolillo integral, frijoles bajos en grasa, queso panela y ensalada verde.', 'Abre el bolillo y unta los frijoles. Agrega el queso y hornea hasta que se derrita. Añade el pico de gallo. Acompaña con la ensalada.', 400, 5, '../planificador/platillos/cenas/Molletes_ligeros.jpeg', 1),
(47, 'Wrap de pavo y verduras', 'Wrap integral con pechuga de pavo, queso panela, verduras y hummus.', 'Unta el hummus sobre la tortilla. Coloca el pavo, queso y verduras. Enrolla firmemente. Corta por la mitad y sirve.', 400, 5, '../planificador/platillos/cenas/Wrap_pavo_verduras.png', 1),
(48, 'Ensalada de pollo con pan tostado', 'Cena ligera con pollo a la plancha, verduras frescas y pan integral tostado.', 'Cocina el pollo y córtalo en tiras. Mezcla las verduras. Agrega el pollo y el aceite de oliva. Sirve con el pan tostado.', 300, 5, '../planificador/platillos/cenas/Ensalada_pollo_pan.png', 1),
(49, 'Omelette de espinacas y queso', 'Omelette con espinacas, queso panela y tortillas de maíz.', 'Saltea las espinacas. Agrega los huevos batidos. Añade el queso y dobla el omelette. Sirve con las tortillas.', 300, 5, '../planificador/platillos/cenas/Omelette_espinacas_queso.png', 1),
(50, 'Sopa de verduras con queso panela', 'Sopa casera de verduras acompañada con queso panela y galletas integrales.', 'Calienta la sopa. Agrega el queso en cubos. Acompaña con las galletas.', 300, 5, '../planificador/platillos/cenas/Sopa_verduras_queso.png', 1);

-- ------------------------------------------------------------
-- pm_platillo_ingredientes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_platillo_ingredientes` (
  `id_platillo_ingrediente` int(11)    NOT NULL AUTO_INCREMENT,
  `id_platillo`             int(11)    NOT NULL,
  `id_ingrediente`          int(11)    NOT NULL,
  `cantidad`                varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id_platillo_ingrediente`),
  KEY `id_platillo`    (`id_platillo`),
  KEY `id_ingrediente` (`id_ingrediente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pm_platillo_ingredientes` (`id_platillo_ingrediente`, `id_platillo`, `id_ingrediente`, `cantidad`) VALUES
(1,1,81,'150 g'),(2,1,20,'1/2 taza'),(3,1,7,'2 cucharadas'),(4,1,9,'al gusto'),
(5,2,42,'1 rebanada'),(6,2,3,'1/4 pieza'),(7,2,29,'1 pieza pequeña'),(8,2,65,'al gusto'),(9,2,55,'al gusto'),
(10,3,15,'3 piezas'),(11,3,19,'1/2 taza'),(12,3,12,'1/4 taza'),(13,3,63,'30 g'),(14,3,2,'1 cucharadita'),
(15,4,72,'1 pieza'),(16,4,63,'40 g'),(17,4,66,'al gusto'),(18,4,39,'1/2 taza'),
(19,5,7,'1/3 taza'),(20,5,33,'1 taza'),(21,5,56,'1/2 pieza'),(22,5,40,'1 cucharada'),
(23,6,8,'1/2 pieza'),(24,6,24,'1/3 taza'),(25,6,63,'30 g'),(26,6,54,'al gusto'),(27,6,3,'1/4 pieza'),
(28,7,80,'170 g'),(29,7,36,'1/2 taza'),(30,7,27,'1/4 taza'),(31,7,9,'al gusto'),
(32,8,28,'2 piezas'),(33,8,72,'2 piezas'),(34,8,3,'1/4 pieza'),(35,8,31,'1/2 taza'),(36,8,11,'al gusto'),(37,8,1,'1 cucharadita'),
(38,9,42,'2 rebanadas'),(39,9,47,'60 g'),(40,9,64,'1 rebanada'),(41,9,34,'al gusto'),(42,9,31,'al gusto'),(43,9,37,'1 pieza pequeña'),
(44,10,7,'1/2 taza'),(45,10,28,'1 pieza'),(46,10,56,'1 pieza pequeña'),(47,10,38,'1 cucharadita'),(48,10,1,'1 cucharadita'),
(49,11,74,'6 piezas en trozos'),(50,11,69,'1/2 taza'),(51,11,62,'40 g'),(52,11,28,'1 pieza'),(53,11,11,'al gusto'),
(54,12,37,'1 pieza mediana'),(55,12,9,'al gusto'),
(56,13,52,'1 pieza mediana'),(57,13,32,'1 pieza'),(58,13,13,'opcional'),
(59,14,83,'150 g'),
(60,15,76,'1 taza'),(61,15,63,'25 g'),
(62,16,41,'3 tazas'),(63,16,65,'al gusto'),
(64,17,75,'1 pieza'),(65,17,3,'1/4 pieza'),(66,17,31,'al gusto'),(67,17,35,'al gusto'),(68,17,65,'al gusto'),
(69,18,80,'150 g'),(70,18,40,'15 g'),
(71,19,56,'1 pieza mediana'),(72,19,16,'1 cucharada'),
(73,20,47,'3 rebanadas'),(74,20,63,'40 g'),(75,20,84,'1/2 taza'),
(76,21,33,'1 taza'),(77,21,20,'1 taza'),(78,21,7,'2 cucharadas'),
(79,22,30,'3 cucharadas'),(80,22,84,'1/2 taza'),(81,22,52,'1/2 taza'),
(82,23,37,'1 pieza mediana'),(83,23,9,'al gusto'),
(84,24,52,'1 pieza mediana'),(85,24,32,'1 pieza'),(86,24,13,'opcional'),
(87,25,83,'150 g'),
(88,26,76,'1 taza'),(89,26,63,'25 g'),
(90,27,41,'3 tazas'),(91,27,65,'al gusto'),
(92,28,75,'1 pieza'),(93,28,3,'1/4 pieza'),(94,28,31,'al gusto'),(95,28,35,'al gusto'),(96,28,65,'al gusto'),
(97,29,80,'150 g'),(98,29,40,'15 g'),
(99,30,56,'1 pieza mediana'),(100,30,16,'1 cucharada'),
(101,31,47,'3 rebanadas'),(102,31,63,'40 g'),(103,31,84,'1/2 taza'),
(104,32,33,'1 taza'),(105,32,20,'1 taza'),(106,32,7,'2 cucharadas'),
(107,33,30,'3 cucharadas'),(108,33,84,'1/2 taza'),(109,33,52,'1/2 taza'),
(110,34,50,'80 g'),(111,34,34,'1 taza'),(112,34,37,'1/2 pieza'),(113,34,2,'1 cucharadita'),(114,34,32,'1/2 pieza'),(115,34,65,'al gusto'),(116,34,55,'al gusto'),
(117,35,75,'2 piezas'),(118,35,6,'1 lata'),(119,35,54,'2 cucharadas'),(120,35,3,'1/4 pieza'),(121,35,34,'al gusto'),(122,35,35,'al gusto'),
(123,36,49,'100 g'),(124,36,5,'1/2 taza'),(125,36,77,'1 taza'),(126,36,2,'1 cucharadita'),(127,36,35,'al gusto'),(128,36,65,'al gusto'),(129,36,55,'al gusto'),
(130,37,72,'3 piezas'),(131,37,59,'80 g'),(132,37,68,'1/4 taza'),(133,37,62,'15 g'),(134,37,11,'al gusto'),
(135,38,53,'120 g'),(136,38,45,'1 pieza pequeña'),(137,38,79,'1 taza'),(138,38,2,'1 cucharada'),(139,38,35,'al gusto'),(140,38,4,'al gusto'),(141,38,18,'al gusto'),
(142,39,71,'1 pieza mediana'),(143,39,58,'80 g'),(144,39,21,'1/4 taza'),(145,39,34,'al gusto'),(146,39,31,'al gusto'),(147,39,82,'1 cucharada'),
(148,40,10,'100 g'),(149,40,72,'3 piezas'),(150,40,11,'al gusto'),(151,40,14,'al gusto'),(152,40,67,'al gusto'),(153,40,3,'1/4 pieza'),
(154,41,6,'1 lata'),(155,41,29,'2 piezas'),(156,41,78,'2 tazas'),(157,41,2,'1 cucharada'),(158,41,26,'4 piezas'),
(159,42,46,'1 taza'),(160,42,51,'100 g'),(161,42,79,'1 taza'),(162,42,2,'1 cucharadita'),(163,42,4,'al gusto'),(164,42,65,'al gusto'),(165,42,55,'al gusto'),
(166,43,57,'100 g'),(167,43,5,'1/2 taza'),(168,43,22,'1/2 taza'),(169,43,3,'1/4 pieza'),(170,43,54,'al gusto'),(171,43,34,'al gusto'),
(172,44,72,'2 piezas grandes'),(173,44,60,'80 g'),(174,44,61,'30 g'),(175,44,39,'1 taza'),(176,44,66,'al gusto'),
(177,45,48,'100 g'),(178,45,34,'2 tazas'),(179,45,52,'al gusto'),(180,45,31,'al gusto'),(181,45,62,'30 g'),(182,45,2,'1 cucharadita'),(183,45,44,'1 pieza pequeña'),
(184,46,8,'1 pieza pequeña'),(185,46,23,'1/3 taza'),(186,46,63,'40 g'),(187,46,54,'al gusto'),(188,46,17,'1 taza'),
(189,47,73,'1 pieza grande'),(190,47,47,'80 g'),(191,47,63,'1 rebanada'),(192,47,34,'al gusto'),(193,47,31,'al gusto'),(194,47,52,'al gusto'),(195,47,30,'1 cucharada'),
(196,48,48,'80 g'),(197,48,34,'2 tazas'),(198,48,52,'al gusto'),(199,48,31,'al gusto'),(200,48,2,'1 cucharadita'),(201,48,43,'1 rebanada'),(202,48,32,'al gusto'),(203,48,65,'al gusto'),(204,48,55,'al gusto'),
(205,49,28,'2 piezas'),(206,49,19,'1 taza'),(207,49,63,'20 g'),(208,49,1,'1 cucharadita'),(209,49,72,'2 piezas pequeñas'),
(210,50,70,'2 tazas'),(211,50,63,'60 g'),(212,50,25,'2 piezas');

-- ------------------------------------------------------------
-- pm_restricciones_alumno
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pm_restricciones_alumno` (
  `id_restriccion`  int(11)     NOT NULL AUTO_INCREMENT,
  `matricula_alum`  varchar(50) NOT NULL,
  `id_ingrediente`  int(11)     NOT NULL,
  `tipo_restriccion` enum('no_gusta','alergia','intolerancia') NOT NULL,
  PRIMARY KEY (`id_restriccion`),
  KEY `id_ingrediente` (`id_ingrediente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
