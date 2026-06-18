<?php
require_once '../config/config.php';

$database = new Database();
$conexion = $database->connect();

function h($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

$matricula = isset($_GET['matricula']) ? trim($_GET['matricula']) : 'ALUMNO_TEST';

$sqlMenu = "
    INSERT INTO pm_menu_alumno (matricula_alum, objetivo_calorico)
    VALUES (?, 2000)
    ON DUPLICATE KEY UPDATE matricula_alum = matricula_alum
";

$stmtMenu = $conexion->prepare($sqlMenu);
$stmtMenu->bind_param("s", $matricula);
$stmtMenu->execute();

$sqlGetMenu = "SELECT * FROM pm_menu_alumno WHERE matricula_alum = ?";
$stmtGetMenu = $conexion->prepare($sqlGetMenu);
$stmtGetMenu->bind_param("s", $matricula);
$stmtGetMenu->execute();
$menuAlumno = $stmtGetMenu->get_result()->fetch_assoc();

$idMenuAlumno = $menuAlumno['id_menu_alumno'];
$objetivoCalorico = $menuAlumno['objetivo_calorico'];

$dias = [];
$resDias = $conexion->query("SELECT * FROM pm_dias_semana ORDER BY orden");
while ($fila = $resDias->fetch_assoc()) {
    $dias[] = $fila;
}

$tiempos = [];
$resTiempos = $conexion->query("SELECT * FROM pm_tiempos_comida ORDER BY orden");
while ($fila = $resTiempos->fetch_assoc()) {
    $tiempos[] = $fila;
}

$idDiaInicial = $dias[0]['id_dia'] ?? 1;
$idTiempoInicial = $tiempos[0]['id_tiempo'] ?? 1;
$nombreDiaInicial = $dias[0]['nombre_dia'] ?? 'Lunes';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planificador de menú</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="planificador_menu.css?v=limpieza_obsoletos_20260617">
</head>

<body class="bento-ui"
    data-id-menu="<?php echo h($idMenuAlumno); ?>"
    data-matricula="<?php echo h($matricula); ?>"
    data-dia-activo="<?php echo h($idDiaInicial); ?>"
    data-tiempo-activo="<?php echo h($idTiempoInicial); ?>"
    data-objetivo="<?php echo h($objetivoCalorico); ?>"
>

<main class="app">
<header class="app-topbar">
    <a class="brand-app" href="#panelPlanificador">
        <span class="brand-logo"></span>
        <strong>Bienestar U</strong>
    </a>
    <nav class="tabs-principales">
        <button class="btn-tab activo" data-panel="panelPlanificador">
            <img src="Ui/icon-planificador.png" class="tab-icon" alt="">
            <span>Planificador</span>
        </button>
        <button class="btn-tab" data-panel="panelMiMenu">
            <img src="Ui/icon-menu.png" class="tab-icon" alt="">
            <span>Mi menú</span>
        </button>
        <button class="btn-tab" data-panel="panelHidratacion">
            <img src="Ui/icon-hidratacion.png" class="tab-icon" alt="">
            <span>Hidratación</span>
        </button>
        <button class="btn-tab" data-panel="panelRelajacion">
            <img src="Ui/icon-relajacion.png" class="tab-icon" alt="">
            <span>Relajación</span>
        </button>
        <button class="btn-tab" data-panel="panelEjercicio">
            <img src="Ui/icon-ejercicio.png" class="tab-icon" alt="">
            <span>Ejercicio</span>
        </button>
        <button class="btn-tab" data-panel="panelCita">
            <img src="Ui/icon-agenda.png" class="tab-icon" alt="">
            <span>Agenda tu cita</span>
        </button>
    </nav>
    <div class="user-chip">
        <span class="avatar-user"><?php echo strtoupper(substr(h($matricula), 0, 1)); ?></span>
        <span class="user-copy"><?php echo h($matricula); ?></span>
    </div>
</header>

    <section id="panelPlanificador" class="panel-principal activo">
<section class="hero-app">
    <div class="hero-copy">
        <div class="shape-red"></div>
        <div class="shape-yellow"></div>
        <div class="shape-mint"></div>
        <div class="dot-grid"></div>
        <h1>Hola, <?php echo h($matricula); ?>.<br>Tu bienestar,<br>tu mejor plan.</h1>
        <span class="title-mark"></span>
        <p>Planifica tus comidas, cuida tu energía y alcanza tus metas, un día a la vez.</p>
    </div>
    <div class="hero-art hero-image-stage" aria-hidden="true">
        <img src="Ui/hero-planificador.png" class="hero-section-image hero-img-planificador" alt="">
    </div>
    <article class="meta-card meta-editable">
        <div class="meta-title"><h3>Meta diaria</h3><span>◎</span></div>
        <small>Energía</small>
        <strong><b id="heroKcalActual">0</b> / <span id="heroMetaObjetivo"><?php echo h($objetivoCalorico); ?></span> kcal</strong>
        <div class="bar"><i id="heroMetaProgress"></i></div>
        <p><b id="heroPorcentajeKcal">0%</b> de tu meta</p>

        <div class="meta-editor">
            <label for="inputMetaDiaria">Modificar meta</label>
            <div>
                <input id="inputMetaDiaria" type="number" min="800" max="6000" step="50" value="<?php echo h($objetivoCalorico); ?>">
                <button id="btnGuardarMetaDiaria" type="button">Guardar</button>
            </div>
        </div>

        <div class="note" id="mensajeMetaDiaria">
            <span id="iconoMensajeMeta">★</span>
            <div>
                <b id="tituloMensajeMeta">Empieza tu día</b>
                <small id="textoMensajeMeta">Agrega platillos para ver tu avance.</small>
            </div>
        </div>
    </article>
</section>
<section class="quick-stats">
    <article class="stat-card stat-kcal"><span>♨</span><div><small>Kcal elegidas</small><strong id="statKcalElegidas">0 kcal</strong><em id="statMetaKcal">de <?php echo h($objetivoCalorico); ?> kcal</em></div></article>
    <article class="stat-card agua"><span>♢</span><div><small>Agua sugerida</small><strong id="statAgua">Sin calcular</strong><em>Registra tu consumo</em></div></article>
    <article class="stat-card relax"><span>✿</span><div><small>Relajación</small><strong>5-10 min</strong><em>Enfócate en ti</em></div></article>
    <article class="stat-card move"><span>⌁</span><div><small>Ejercicio</small><strong>5-10 min</strong><em>¡Sigue moviéndote!</em></div></article>
</section>


        <section class="card-panel panel-planificador">

            <div class="planner-grid">
                <div class="planner-column side">
                    <div class="visual-box">
                        <div class="visual-box-header">
                            <span class="visual-icon">📅</span>
                            <div>
                                <strong>Agenda semanal</strong>
                            </div>
                        </div>

                        <div class="selector-dias">
                            <?php foreach ($dias as $index => $dia): ?>
                                <button
                                    class="btn-dia <?php echo $index === 0 ? 'activo' : ''; ?>"
                                    data-id-dia="<?php echo h($dia['id_dia']); ?>"
                                    data-nombre-dia="<?php echo h($dia['nombre_dia']); ?>"
                                    title="<?php echo h($dia['nombre_dia']); ?>"
                                >
                                    <?php echo h(substr($dia['nombre_dia'], 0, 1)); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="visual-box">
                        <div class="visual-box-header">
                            <span class="visual-icon">🍱</span>
                            <div>
                                <strong>Resumen del día</strong>
                                <small>Lo elegido hoy</small>
                            </div>
                        </div>

                        <div id="menuDia" class="menu-dia">
                            <p>Cargando menú...</p>
                        </div>
                    </div>
                </div>

                <div class="planner-column main">
                    <div class="visual-box catalogo-box">
                        <div class="catalogo-top">
                            <div>
                                <span class="mini-label">Catálogo</span>
                                <h3>Selección de platillos</h3>
                                <p>Explora y agrega opciones.</p>
                            </div>

                            <div class="search-panel-compacto">
                                <div class="search-compact-head">
                                    <label class="search-label" for="buscarPlatillo">Buscar</label>
                                    <strong id="contadorPlatillos">0 resultados</strong>
                                </div>

                                <div class="search-box-compacto">
                                    <span class="search-inline-icon">🔎</span>
                                    <input type="search" id="buscarPlatillo" placeholder="Nombre o ingrediente">
                                    <button type="button" id="limpiarBusqueda" class="btn-clear-search">Limpiar</button>
                                </div>

                                <div class="search-compact-foot">

                                    <div class="search-chips">
                                        <button type="button" class="chip-busqueda" data-search="pollo">Pollo</button>
                                        <button type="button" class="chip-busqueda" data-search="ensalada">Ensalada</button>
                                        <button type="button" class="chip-busqueda" data-search="avena">Avena</button>
                                        <button type="button" class="chip-busqueda" data-search="yogur">Yogur</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="selector-tiempos">
                            <?php foreach ($tiempos as $index => $tiempo): ?>
                                <button class="btn-tiempo <?php echo $index === 0 ? 'activo' : ''; ?>" data-id-tiempo="<?php echo h($tiempo['id_tiempo']); ?>"><?php echo h($tiempo['nombre_tiempo']); ?></button>
                            <?php endforeach; ?>
                        </div>

                        <div class="catalogo-note catalogo-note-grupos">
                            <span>🥦 Verduras</span>
                            <span>🍎 Frutas</span>
                            <span>🌾 Cereales</span>
                            <span>🍗 Proteínas</span>
                            <span>🥛 Lácteos</span>
                            <span>🥑 Grasas</span>
                        </div>

                        <div id="listaPlatillos" class="lista-platillos">
                            <p>Cargando platillos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </section>

    <section id="panelMiMenu" class="panel-principal">
    <section class="screen mi-screen">
        <div class="tab-hero">
            <div class="tab-copy">
                <div class="shape-red"></div><div class="shape-yellow"></div><div class="shape-mint"></div><div class="dot-grid"></div>
                <h2>Mi menú<br>semanal</h2>
                <span class="title-mark"></span>
                <p>Tus comidas, organizadas para que alcances tus metas sin complicaciones.</p>
            </div>
            <div class="food-week-art hero-image-stage" aria-hidden="true">
                <img src="Ui/hero-menu.png" class="hero-section-image hero-img-menu" alt="">
            </div>
        </div>
        <div class="week-toolbar">
            <strong>Semana actual</strong>
            <div class="menu-actions">
                <button id="btnActualizarSemana" class="btn-outline btn-refresh-soft" type="button">Actualizar</button>
                <button id="btnImprimirMenu" class="btn-outline btn-print-menu" type="button">Imprimir / Descargar</button>
            </div>
        </div>
        <div id="menuSemana" class="menu-semana menu-semana-7"><p>Cargando menú semanal...</p></div>

        <section id="listaComprasMenu" class="lista-compras-menu">
            <div class="lista-compras-head">
                <div>
                    <span>Lista de compras</span>
                    <h3>Ingredientes necesarios</h3>
                    <p>Se genera automáticamente con los platillos de tu menú semanal.</p>
                </div>
            </div>
            <div class="lista-compras-body">
                <p class="lista-compras-empty">Agrega platillos a tu menú para generar la lista.</p>
            </div>
        </section>
        <div class="bottom-cards">
            <article class="tips-card tips-card-wide">
                <div class="tips-copy">
                    <h3>Consejos para esta semana</h3>
                    <p>Incluye variedad de colores en tus platos.</p>
                    <p>Hidrátate durante el día.</p>
                    <p>Come con atención y disfruta cada bocado.</p>
                    <p>Organiza tus comidas con anticipación para mantener tu energía durante la semana.</p>
                </div>
                <i></i>
            </article>
        </div>
    </section>
</section>
<section id="panelHidratacion" class="panel-principal">
    <section class="screen hidro-dashboard">
        <section class="hidro-hero">
            <div class="hidro-hero-copy">
                <div class="hidro-shape-blue"></div>
                <div class="hidro-shape-yellow"></div>
                <div class="hidro-dot-grid"></div>
                <h2>Hidratación</h2>
                <span class="title-mark"></span>
                <p>El agua impulsa tu energía, concentración y desempeño. Hidrátate y alcanza tu mejor versión.</p>
            </div>

            <div class="hidro-hero-art hero-image-stage" aria-hidden="true">
                <img src="Ui/hero-hidratacion.png" class="hero-section-image hero-img-hidratacion" alt="">
            </div>

            <article class="hidro-status-card">
                <span class="hidro-status-icon">✓</span>
                <div>
                    <h3 id="hidroEstadoTitulo">¡Todo bien!</h3>
                    <p id="hidroEstadoTexto">Vas por buen camino. Sigue así, tu cuerpo te lo agradece.</p>
                </div>
                <i></i>
            </article>
        </section>

        <section class="hidro-grid">
            <article class="hidro-card hidro-form-card">
                <div class="card-title-inline">
                    <span>♙</span>
                    <h3>Tus datos</h3>
                </div>

                <div class="hidro-form-grid">
                    <label class="hidro-field">
                        <span>Peso</span>
                        <div><input id="pesoAgua" type="number" min="20" max="250" value="60"><small>kg</small></div>
                    </label>

                    <label class="hidro-field">
                        <span>Sexo</span>
                        <select id="sexoAgua">
                            <option value="mujer">Mujer</option>
                            <option value="hombre">Hombre</option>
                            <option value="no_especificar">No especificar</option>
                        </select>
                    </label>

                    <label class="hidro-field">
                        <span>Edad</span>
                        <div><input id="edadAgua" type="number" min="10" max="100" value="22"><small>años</small></div>
                    </label>

                    <label class="hidro-field">
                        <span>Actividad</span>
                        <select id="actividadAgua">
                            <option value="baja">Baja</option>
                            <option value="moderada" selected>Moderada</option>
                            <option value="alta">Alta</option>
                        </select>
                    </label>

                    <label class="hidro-field">
                        <span>Ejercicio</span>
                        <div><input id="ejercicioAgua" type="number" min="0" max="300" step="5" value="30"><small>min</small></div>
                    </label>

                    <label class="hidro-field">
                        <span>Intensidad</span>
                        <select id="intensidadAgua">
                            <option value="baja">Baja</option>
                            <option value="moderada" selected>Moderada</option>
                            <option value="alta">Alta</option>
                        </select>
                    </label>

                    <label class="hidro-field">
                        <span>Clima</span>
                        <select id="climaAgua">
                            <option value="templado" selected>Templado (20–25 °C)</option>
                            <option value="caluroso">Caluroso</option>
                            <option value="humedo">Húmedo</option>
                        </select>
                    </label>

                    <label class="hidro-field">
                        <span>Sudoración</span>
                        <select id="sudoracionAgua">
                            <option value="baja">Baja</option>
                            <option value="moderada" selected>Moderada</option>
                            <option value="alta">Alta</option>
                        </select>
                    </label>
                </div>

                <button id="btnCalcularAgua" class="hidro-main-btn" type="button">Calcular hidratación diaria <span>→</span></button>
            </article>

            <div class="hidro-center">
                <article class="hidro-meta-card">
                    <div class="hidro-meta-copy">
                        <div class="card-title-inline">
                            <span>♢</span>
                            <h3>Meta diaria</h3>
                        </div>
                        <strong id="metaAguaLitros">0.0 L</strong>
                        <p>de <b id="metaAguaTotal">0.0 L</b></p>
                        <div class="hidro-inline-progress">
                            <span>Llevas <b id="aguaConsumidaTexto">0 ml</b></span>
                            <i>•</i>
                            <span>Te faltan <b id="aguaFaltanteTexto">0 ml</b></span>
                        </div>
                        <div class="hidro-bar">
                            <i id="progresoAguaBarra"></i>
                        </div>
                        <div class="hidro-scale">
                            <span>0 L</span>
                            <span id="metaAguaMitad">0 L</span>
                            <span id="metaAguaFinal">0 L</span>
                        </div>
                    </div>

                    <div class="hidro-ring" id="hidroRing">
                        <div>
                            <strong id="porcentajeAgua">0%</strong>
                            <small>de tu meta</small>
                        </div>
                    </div>
                </article>

                <article class="hidro-registro-card">
                    <div class="card-title-inline">
                        <span>♢</span>
                        <h3>Registro de consumo</h3>
                    </div>

                    <div class="hidro-add-buttons">
                        <button type="button" class="btn-add-agua" data-ml="250">♢ +250 ml</button>
                        <button type="button" class="btn-add-agua" data-ml="500">♧ +500 ml</button>
                        <button type="button" class="btn-add-agua" data-ml="750">♙ +750 ml</button>
                        <button type="button" class="btn-add-agua" data-ml="1000">♢ +1 L</button>
                    </div>

                    <div class="hidro-manual hidro-manual-tiempo">
                        <input id="inputAguaHora" type="time">
                        <input id="inputAguaManual" type="number" min="50" max="3000" placeholder="Cantidad en ml">
                        <button id="btnAgregarAguaManual" type="button">Agregar</button>
                    </div>

                    <div class="hidro-table-wrap">
                        <table class="tabla-agua">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Cantidad</th>
                                    <th>Fuente</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAguaBody">
                                <tr>
                                    <td colspan="4">Aún no agregas agua.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button id="btnReiniciarAgua" class="hidro-history-btn" type="button">Reiniciar registro</button>
                </article>
            </div>

            <aside class="hidro-right">
                <article class="hidro-balance-card">
                    <div class="balance-title">
                        <h3>Tu día en balance</h3>
                        <span>ⓘ</span>
                    </div>

                    <div class="hidro-balance-grid">
                        <div>
                            <span class="balance-icon morning">☀</span>
                            <small>Mañana</small>
                            <strong>35%</strong>
                            <em id="aguaManana">0 ml</em>
                            <i></i>
                        </div>
                        <div>
                            <span class="balance-icon afternoon">☀</span>
                            <small>Tarde</small>
                            <strong>45%</strong>
                            <em id="aguaTarde">0 ml</em>
                            <i></i>
                        </div>
                        <div>
                            <span class="balance-icon night">☾</span>
                            <small>Noche</small>
                            <strong>20%</strong>
                            <em id="aguaNoche">0 ml</em>
                            <i></i>
                        </div>
                    </div>
                </article>

                <article class="hidro-habit-card">
                    <div>
                        <h3>Pequeñas elecciones,<br>grandes cambios.</h3>
                        <p>Crea hábitos de hidratación que te acompañan todos los días.</p>
                        <a href="#panelHidratacion">Ver consejos →</a>
                    </div>
                    <div class="water-glass" aria-hidden="true">
                        <span></span>
                        <i></i>
                    </div>
                </article>
            </aside>
        </section>

        <section class="hidro-factors-card">
            <h3>Factores que influyen en tu hidratación</h3>
            <div class="hidro-factors-grid">
                <article>
                    <span>☀</span>
                    <div>
                        <h4>Clima</h4>
                        <p>El calor y la humedad aumentan la pérdida de líquidos. Ajusta tu consumo según el clima del día.</p>
                    </div>
                </article>

                <article>
                    <span>⌁</span>
                    <div>
                        <h4>Ejercicio</h4>
                        <p>A mayor intensidad y duración, más agua necesitas para rendir y recuperarte adecuadamente.</p>
                    </div>
                </article>

                <article>
                    <span>♢</span>
                    <div>
                        <h4>Sudoración</h4>
                        <p>Cada cuerpo es diferente. Conocer tu nivel de sudoración te ayuda a hidratarte de forma más precisa.</p>
                    </div>
                </article>
            </div>
        </section>
    </section>
</section>
<section id="panelRelajacion" class="panel-principal">
    <section class="screen relax-screen">
        <section class="tab-hero relax-hero">
            <div class="tab-copy relax-copy">
                <div class="relax-shape relax-shape-a"></div>
                <div class="relax-shape relax-shape-b"></div>
                <h2>Pausa y respira.</h2>
                <p>Un momento para ti. Reduce el estrés, mejora tu enfoque y recarga tu energía.</p>
            </div>

            <div class="calm-art hero-image-stage" aria-hidden="true">
                <img src="Ui/hero-relajacion.png" class="hero-section-image hero-img-relajacion" alt="">
            </div>

            <article class="meta-card relax-card">
                <div class="relax-card-head">
                    <h3>Meta diaria</h3>
                    <span>✿</span>
                </div>
                <small>Relajación</small>
                <strong><b id="relaxWeeklyMinutes">0</b> <small>/ <span id="relaxWeeklyTarget">10</span> min</small></strong>
                <div class="bar"><i id="relaxWeeklyBar"></i></div>
                <p id="relaxWeeklyText">0% de tu meta diaria</p>
                <div class="relax-goal-editor">
                    <label for="relaxGoalInput">Modificar meta diaria</label>
                    <div>
                        <input id="relaxGoalInput" type="number" min="5" max="120" step="5" value="10">
                        <button type="button" id="btnGuardarMetaRelajacion">Guardar</button>
                    </div>
                </div>
            </article>
        </section>

        <div class="relax-grid">
            <article class="breathing-main-card">
                <div class="breathing-card-head">
                    <div>
                        <h3 id="breathingRoutineTitle">Respiración diafragmática</h3>
                        <p id="breathingRoutineSubtitle">5 minutos · calma corporal</p>
                    </div>
                    <span id="breathingRoutineBadge">5 min</span>
                </div>

                <div class="breathing-wrap">
                    <div class="breathing-player">
                        <div class="breathing-visual" id="breathingVisual">
                            <div class="breathing-orbit"></div>
                            <div class="breathing-circle" id="breathingCircle">
                                <strong id="breathingInstruction">Inhala</strong>
                                <small id="breathingCounter">4 segundos</small>
                                <button type="button" id="btnBreathingPlay" class="btn-breathing-play">
                                    <span id="breathingIcon">▶</span>
                                </button>
                            </div>
                        </div>

                        <div class="breathing-time-row">
                            <span id="breathingElapsedText">00:00</span>
                            <div class="breathing-progress"><div id="breathingProgress"></div></div>
                            <span id="breathingTotalText">05:00</span>
                        </div>

                        <div class="breathing-controls">
                            <button type="button" id="btnBreathingReset" class="btn-breathing-reset">Reiniciar</button>
                            <button type="button" id="btnBreathingFinish" class="btn-breathing-finish">Finalizar sesión</button>
                        </div>
                    </div>

                    <aside class="breathing-steps">
                        <h4>Cómo funciona</h4>
                        <div id="breathingPhaseList"></div>
                    </aside>
                </div>

                <div class="breathing-advice">
                    <span>♧</span>
                    <p id="breathingAdviceText">Si te distraes, vuelve suavemente a tu respiración.</p>
                </div>
            </article>

            <div class="relax-side">
                <article class="routine-panel">
                    <div class="routine-panel-head">
                        <h3>Rutinas de respiración</h3>
                        <button type="button" id="btnShowAllRoutines">Ver todas</button>
                    </div>

                    <div class="mini-routines" id="relaxRoutineCards">
                        <button type="button" class="relax-routine-card activo" data-routine="diafragmatica">
                            <i></i>
                            <span>5 min</span>
                            <b>Respiración diafragmática</b>
                            <small>Activa el abdomen y suelta tensión.</small>
                            <em>▶</em>
                        </button>

                        <button type="button" class="relax-routine-card" data-routine="cuadrada">
                            <i></i>
                            <span>3–5 min</span>
                            <b>Respiración 4-4-4-4</b>
                            <small>Equilibra el ritmo con pausas iguales.</small>
                            <em>▶</em>
                        </button>

                        <button type="button" class="relax-routine-card" data-routine="cuatroSeis">
                            <i></i>
                            <span>5 min</span>
                            <b>Respiración 4-6</b>
                            <small>Exhalación lenta para bajar tensión.</small>
                            <em>▶</em>
                        </button>

                        <button type="button" class="relax-routine-card" data-routine="cuatroSieteOcho">
                            <i></i>
                            <span>2–4 min</span>
                            <b>Respiración 4-7-8</b>
                            <small>Ritmo profundo para relajarte.</small>
                            <em>▶</em>
                        </button>

                        <button type="button" class="relax-routine-card" data-routine="rutinaDiez">
                            <i></i>
                            <span>10 min</span>
                            <b>Rutina de 10 minutos</b>
                            <small>Combina diafragmática, 4-6, cuadrada y respiración libre.</small>
                            <em>▶</em>
                        </button>
                    </div>
                </article>

                <article class="mood-panel">
                    <h3>¿Cómo te sientes hoy?</h3>
                    <p>Elige cómo te sientes para recibir mejores recomendaciones.</p>
                    <p id="relaxRecommendationText" class="relax-recommendation">Selecciona una emoción y te recomendaré una rutina.</p>
                    <div class="mood-chips">
                        <button type="button" data-recommend="diafragmatica">☺ Tranquilo</button>
                        <button type="button" data-recommend="cuadrada">☁ Ansioso</button>
                        <button type="button" data-recommend="cuatroSeis">☹ Estresado</button>
                        <button type="button" data-recommend="cuatroSieteOcho">☾ Cansado</button>
                        <button type="button" data-recommend="rutinaDiez">✧ Motivado</button>
                        <button type="button" data-recommend="cuadrada">✿ Abrumado</button>
                    </div>
                </article>

                <article class="benefits-panel">
                    <h3>Beneficios de relajarte</h3>
                    <div class="benefits">
                        <span><b>✿</b>Reduce el estrés<small>Disminuye la ansiedad y relaja tu cuerpo.</small></span>
                        <span><b>◎</b>Mejora el enfoque<small>Aumenta tu claridad y concentración.</small></span>
                        <span><b>☾</b>Mejora tu sueño<small>Duerme mejor y despierta con más energía.</small></span>
                    </div>
                </article>
            </div>
        </div>
</section>
</section>
<section id="panelEjercicio" class="panel-principal">
    <section class="screen exercise-screen">
        <section class="exercise-hero">
            <div class="exercise-hero-copy">
                <div class="shape-red"></div>
                <div class="shape-yellow"></div>
                <div class="shape-mint"></div>
                <div class="dot-grid"></div>
                <h2>Actívate hoy,<br>siéntete mejor</h2>
                <span class="title-mark"></span>
                <p>Cada movimiento cuenta. Tu cuerpo y tu mente te lo agradecerán.</p>
            </div>

            <div class="exercise-hero-art hero-image-stage" aria-hidden="true">
                <img src="Ui/hero-ejercicio.png" class="hero-section-image hero-img-ejercicio" alt="">
            </div>

            <article class="exercise-meta-card">
                <div class="exercise-meta-head">
                    <h3>Meta activa</h3>
                    <span>◎</span>
                </div>
                <div class="exercise-meta-body">
                    <div class="exercise-ring" id="exerciseRing">
                        <strong id="exerciseProgressPercent">0%</strong>
                    </div>
                    <div>
                        <strong><b id="exerciseMinutesDone">0</b> <small>/ <span id="exerciseGoalText">200</span> min</small></strong>
                        <p>Meta semanal de minutos activos</p>
                        <div class="note exercise-note">
                            <span>★</span>
                            <div>
                                <b id="exerciseStatusTitle">Empieza hoy</b>
                                <small id="exerciseStatusText">Elige una rutina para sumar minutos.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="btnExerciseStartMeta">Registrar actividad <span>＋</span></button>
            </article>
        </section>

        <section class="exercise-stats">
            <article>
                <span>◷</span>
                <small>Minutos activos</small>
                <strong id="exerciseStatMinutes">0 min</strong>
                <em>Esta semana</em>
            </article>
            <article>
                <span>⌁</span>
                <small>Intensidad promedio</small>
                <strong id="exerciseStatIntensity">Suave</strong>
                <em id="exerciseStatIntensityNote">Ideal para iniciar</em>
            </article>
            <article>
                <span>♨</span>
                <small>Calorías estimadas</small>
                <strong id="exerciseStatCalories">0 kcal</strong>
                <em>Esta semana</em>
            </article>
        </section>

        <section class="exercise-main-grid">
            <article class="exercise-routines-panel">
                <div class="exercise-panel-head">
                    <div>
                        <h3>Rutinas recomendadas</h3>
                        <p>Explora rutinas rápidas y efectivas para tu día.</p>
                    </div>
                    <button type="button" id="btnExerciseViewAll">Ver todas las rutinas →</button>
                </div>

                <div class="exercise-cards" id="exerciseRoutineCards">
                    <article class="exercise-routine-card activo" data-routine="sedentaria">
                        <div class="exercise-card-visual visual-walk"><i></i></div>
                        <span>10 min</span>
                        <h4>Rutina suave para personas sedentarias</h4>
                        <p><b>Fácil</b> · Bajo impacto para empezar con seguridad.</p>
                        <button type="button" class="btn-exercise-start">Comenzar <em>→</em></button>
                    </article>

                    <article class="exercise-routine-card" data-routine="despertar">
                        <div class="exercise-card-visual visual-move"><i></i></div>
                        <span>5 min</span>
                        <h4>Activar el cuerpo al despertar</h4>
                        <p><b>Fácil</b> · Movimiento suave para iniciar el día.</p>
                        <button type="button" class="btn-exercise-start">Comenzar <em>→</em></button>
                    </article>

                    <article class="exercise-routine-card" data-routine="calorias">
                        <div class="exercise-card-visual visual-burn"><i></i></div>
                        <span>10 min</span>
                        <h4>Rutina rápida para quemar calorías</h4>
                        <p><b>Media</b> · Circuito de 30 segundos por ejercicio.</p>
                        <button type="button" class="btn-exercise-start">Comenzar <em>→</em></button>
                    </article>
                </div>

                <article class="exercise-player-card">
                    <div class="exercise-player-top">
                        <div>
                            <h3 id="exercisePlayerTitle">Rutina suave para personas sedentarias</h3>
                            <p id="exercisePlayerSubtitle">10 minutos · bajo impacto</p>
                        </div>
                        <span id="exercisePlayerBadge">10 min</span>
                    </div>

                    <div class="exercise-player-body">
                        <div class="exercise-current">
                            <span id="exerciseCurrentIcon">🚶</span>
                            <strong id="exerciseCurrentStep">Presiona comenzar</strong>
                            <small id="exerciseCurrentTime">00:00</small>
                        </div>

                        <ol id="exerciseStepsList" class="exercise-steps-list"></ol>
                    </div>

                    <div class="exercise-progress-row">
                        <span id="exerciseElapsedText">00:00</span>
                        <div class="exercise-session-progress"><i id="exerciseSessionProgress"></i></div>
                        <span id="exerciseTotalText">10:00</span>
                    </div>

                    <div class="exercise-player-actions">
                        <button type="button" id="btnExercisePlay">Comenzar</button>
                        <button type="button" id="btnExerciseReset">Reiniciar</button>
                        <button type="button" id="btnExerciseFinish">Finalizar rutina</button>
                    </div>
                </article>
            </article>
        </section>
    </section>
</section>
<section id="panelCita" class="panel-principal">
    <section class="screen cita-screen contacto-screen">
        <section class="contact-hero">
            <div class="contact-hero-copy">
                <div class="shape-red"></div>
                <div class="shape-yellow"></div>
                <div class="shape-mint"></div>
                <div class="dot-grid"></div>
                <h2>Agenda tu cita</h2>
                <span class="title-mark"></span>
                <p>Contacta directamente el área que necesitas. Nutrición, Lab Fisioterapia y Psicología atienden en Campus III de 7:00 a 19:00.</p>
            </div>

            <div class="contact-hero-art hero-image-stage" aria-hidden="true">
                <img src="Ui/hero-agenda.png" class="hero-section-image hero-img-agenda" alt="">
            </div>
        </section>

        <section class="contact-layout">
            <article class="contact-services-card contact-services-real">
                <div class="contact-section-head">
                    <div>
                        <h3>Contactos por área</h3>
                        <p>Elige el servicio y comunícate por llamada, WhatsApp o correo.</p>
                    </div>
                </div>

                <div class="contact-service-grid contact-service-grid-real">
                    <div id="contactoNutricion" class="contact-service-item contact-nutricion">
                        <span>🍏</span>
                        <h4>Nutrición</h4>
                        <p>Orientación nutricional y seguimiento de hábitos alimenticios.</p>

                        <ul class="contact-data-list">
                            <li><b>Tel:</b> <a href="tel:9383811018">938 381 1018 EXT 2307</a></li>
                            <li><b>WhatsApp:</b> <a href="https://wa.me/529383042369" target="_blank" rel="noopener">938 304 2369</a></li>
                            <li><b>Correo:</b> <a href="mailto:Laboratorio_nutricio@hotmail.com">Laboratorio_nutricio@hotmail.com</a></li>
                            <li><b>Horario:</b> 7:00 - 19:00</li>
                            <li><b>Ubicación:</b> Universidad Campus III, Av. Central s/n Esq. Fracc Mundo Maya</li>
                        </ul>

                        <div class="contact-card-actions">
                            <a href="tel:9383811018">Llamar</a>
                            <a href="https://wa.me/529383042369" target="_blank" rel="noopener">WhatsApp</a>
                            <a href="mailto:Laboratorio_nutricio@hotmail.com?subject=Cita%20Nutrición">Correo</a>
                        </div>
                    </div>

                    <div id="contactoFisioterapia" class="contact-service-item contact-fisio">
                        <span>🦵</span>
                        <h4>Lab Fisioterapia</h4>
                        <p>Atención y orientación en recuperación física y fisioterapia.</p>

                        <ul class="contact-data-list">
                            <li><b>Tel:</b> <a href="tel:9382611094">938 261 1094</a></li>
                            <li><b>Correo:</b> <a href="mailto:fisioterapiaunacar@gmail.com">fisioterapiaunacar@gmail.com</a></li>
                            <li><b>Horario:</b> 7:00 - 19:00</li>
                            <li><b>Ubicación:</b> Universidad Campus III, Av. Central s/n Esq. Fracc Mundo Maya</li>
                        </ul>

                        <div class="contact-card-actions">
                            <a href="tel:9382611094">Llamar</a>
                            <a href="mailto:fisioterapiaunacar@gmail.com?subject=Cita%20Fisioterapia">Correo</a>
                        </div>
                    </div>

                    <div id="contactoPsicologia" class="contact-service-item contact-psico">
                        <span>✿</span>
                        <h4>Psicología</h4>
                        <p>Atención psicológica y acompañamiento emocional estudiantil.</p>

                        <ul class="contact-data-list">
                            <li><b>Tel:</b> <a href="tel:9383811018">938 381 1018 EXT 2305</a></li>
                            <li><b>WhatsApp:</b> <a href="https://wa.me/529382611072" target="_blank" rel="noopener">938 261 1072</a></li>
                            <li><b>Correo:</b> <a href="mailto:atencionpsicologicaunacar@gmail.com">atencionpsicologicaunacar@gmail.com</a></li>
                            <li><b>Horario:</b> 7:00 - 19:00</li>
                            <li><b>Ubicación:</b> Universidad Campus III, Av. Central s/n Esq. Fracc Mundo Maya</li>
                        </ul>

                        <div class="contact-card-actions">
                            <a href="tel:9383811018">Llamar</a>
                            <a href="https://wa.me/529382611072" target="_blank" rel="noopener">WhatsApp</a>
                            <a href="mailto:atencionpsicologicaunacar@gmail.com?subject=Cita%20Psicología">Correo</a>
                        </div>
                    </div>
                </div>
            </article>

            <aside class="contact-side">
<article class="contact-requirements-card">
                    <h3>Antes de acudir</h3>
                    <p>Confirma disponibilidad antes de presentarte.</p>
                    <p>Lleva matrícula o identificación escolar.</p>
                    <p>Explica brevemente el motivo de tu cita.</p>
                    <p>El horario general registrado es 7:00 - 19:00.</p>
                </article>
            </aside>
        </section>
    </section>
</section>
</main>

<div id="toast" class="toast oculto"></div>

<script src="planificador_menu.js?v=limpieza_obsoletos_20260617"></script>

</body>
</html>
