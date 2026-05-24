<?php
date_default_timezone_set('America/Mexico_City');
session_start();


if (!isset($_SESSION['usuario'])) {
  header('Location: ../login.php');
  exit();
}
date_default_timezone_set('America/Mexico_City');

  require_once '../config/config.php';
      $database = new Database();
      $conn = $database->connect();


// Obtener cantidad de alumnos
$sqlAlumnos = "SELECT COUNT(*) FROM alumnos";
$resultAlumnos = $conn->query($sqlAlumnos);
$totalAlumnos = 0;
if ($resultAlumnos) {
  $fila = $resultAlumnos->fetch_row();
  $totalAlumnos = $fila[0];
}

//cantidad de datos fisicos calculados
$sqlDatos = "SELECT COUNT(*) FROM datos_fisicos_alumnos";
$resultDatos = $conn->query($sqlDatos);
$totalDatos = 0;
if ($resultDatos) {
  $dts = $resultDatos->fetch_row();
  $totalDatos = $dts[0];
}

//consulta para alumnos que tienen todo resuelto
$sqlTotalCompletos = "
    SELECT COUNT(DISTINCT a.matricula_alum) as total_alumnos_completos
    FROM alumnos a
    INNER JOIN datos_fisicos_alumnos dfa ON a.matricula_alum = dfa.matricula_alum
    INNER JOIN dass d ON a.matricula_alum = d.matricula_alum
    INNER JOIN estilo_de_vida edv ON a.matricula_alum = edv.matricula_alum
";

$resultCompletos = $conn->query($sqlTotalCompletos);
$totalAlumnosCompletos = 0;

if ($resultCompletos) {
    // Obtenemos la fila del resultado
    $row = $resultCompletos->fetch_assoc();
    // Extraemos el valor usando el alias que definimos en el SQL
    $totalAlumnosCompletos = $row['total_alumnos_completos'];
}

if ($conn->connect_errno) {
  die("Error de conexión: " . $conn->connect_error);
}

$usuario = $_SESSION['usuario'];

// Consulta para obtener nombre, foto y rol
$sql = "SELECT nombre_admi, foto, rol FROM administradores WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($nombre_admi, $foto, $rol);
$stmt->fetch();
$stmt->close();

// Guardar en sesión
$_SESSION['nombre_admi'] = $nombre_admi;
$_SESSION['rol'] = $rol;

// Si no existe foto, usar default
$foto_base64 = $foto ? base64_encode($foto) : base64_encode(file_get_contents(__DIR__ . "/../images/escudo_UNACAR.png"));


$conn->close();
?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú Principal - UNACAR</title>
  <link rel="stylesheet" href="../css/menu.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

  <style>
    .dropdown-menu-center {
      position: absolute !important;
      top: 50% !important;
      left: 50% !important;
      right: auto !important;
      transform: translate(-50%, -50%) !important;
      z-index: 1000 !important;
    }

    .dropdown-menu-center.show {
      display: block !important;
    }
  </style>
</head>

<body>

  <header class="p-3  shadow-sm">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand" href="#">
          <img src="../images/logo_unacar (1).png" alt="Logo UNACAR" style="height:60px;">
        </a>

        <div class="d-flex align-items-center ms-auto">
          <!--<img src="data:image/jpeg;base64,<?php echo $foto_base64; ?>" class="rounded-circle me-3" style="width: 50px; height: 50px;" alt="Perfil">-->
          <div class="text-end me-3">
            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nombre_admi']); ?></div>
            <small class="text-muted"><?php echo htmlspecialchars($_SESSION['rol']); ?></small><br>
            <small class="text-muted">Ingreso: <?php echo date('H:i') . ' - ' . date('d/m/Y'); ?></small>
          </div>
          <form action="../auth/cerrar_sesion.php" method="POST">
            <button type="submit" class="btn btn-danger btn-sm">
              <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </button>
          </form>
        </div>
      </div>
    </nav>
  </header>


  <div class="container mt-4" data-aos="fade-down">
    <div class="row align-items-center justify-content-between text-center">

      <!-- Cuadro izquierdo -->
      <div class="col-md-3 mb-3 mb-md-0">
        <div class="p-3 bg-light border rounded shadow-sm">
          <h6 class="mb-1 text-primary">Total de alumnos Registrados</h6>
          <div class="fw-bold fs-4 text-dark"><?php echo $totalAlumnos; ?></div>
        </div>
      </div>

      <!-- Texto central -->
      <div class="welcome col-md-5">
        <div class="text-center">
          <h1>Bienvenido al Sistema Integral de Salud</h1>
          <h4>Selecciona una opción para continuar</h4>
        </div>
      </div>

      <!-- Cuadro derecho -->
      <div class="col-md-3 mt-3 mt-md-0">
        <div class="p-3 bg-light border rounded shadow-sm">
          <h6 class="mb-1 text-primary">Total de Alumnos con Resultados Completos</h6>
          <div class="fw-bold fs-4 text-dark"><?php echo $totalAlumnosCompletos; ?></div>
        </div> <!-- Puedes cambiarlo -->
      </div>
    </div>

  </div>
  </div>



  <main class="container menu-grid">
    <div class="menu-card" onclick="location.href='../historial/historial_clinico.html'" data-aos="fade-up" data-aos-delay="100">
      <i class="bi bi-file-earmark-medical"></i>
      <h5 class="mt-3">Registro de Historial Clínico</h5>
    </div>

    <div class="menu-card" onclick="location.href='../datos-fisicos/datos_fisicos.html'" data-aos="fade-up" data-aos-delay="200">
      <i class="bi bi-activity"></i>
      <h5 class="mt-3">Registro de Datos Físicos</h5>
    </div>

    <?php if ($_SESSION['rol'] === 'Administrador') { ?>
      <div class="menu-card" onclick="location.href='../estadisticas/estadisticas.html'" data-aos="fade-up" data-aos-delay="300">
        <i class="bi bi-graph-up"></i>
        <h5 class="mt-3">Estadísticas</h5>
      </div>

      <div class="menu-card position-relative" data-aos="fade-up" data-aos-delay="400" style="cursor: pointer;"
        id="cardBusqueda">
        <div class="w-100 d-flex flex-column align-items-center text-center">
          <i class="bi bi-search"></i>
          <h5 class="mt-3">Búsqueda</h5>
        </div>
        <ul class="dropdown-menu dropdown-menu-center text-center" id="menuBusqueda">
          <li><a class="dropdown-item d-flex align-items-center justify-content-center gap-5"
              href="../gestion-alumnos/ListadoAlumnos.html"><i class="bi bi-mortarboard"></i> Alumnos</a></li>
          <li hidden><a class="dropdown-item" href="ListadoDocentes.html"><i class="bi bi-person-badge"></i>
              Docentes</a></li>
          <li hidden><a class="dropdown-item" href="ListadoEmpleados.html"><i class="bi bi-briefcase"></i> Empleados</a>
          </li>
        </ul>
      </div>

      <div class="menu-card" onclick="location.href='panel_control.php'" data-aos="fade-up" data-aos-delay="600">
        <i class="bi bi-people"></i>
        <h5 class="mt-3">Control Del Sistema</h5>
      </div>

      <div class="menu-card position-relative" data-aos="fade-up" data-aos-delay="400" style="cursor: pointer;"
        id="cardResultados">
        <div class="w-100 d-flex flex-column align-items-center text-center">
          <i class="bi bi-clipboard2-check"></i>
          <h5 class="mt-3">Resultados Generales</h5>
        </div>
        <ul class="dropdown-menu dropdown-menu-center text-center" id="menuResultados">
          <li><a class="dropdown-item d-flex align-items-center justify-content-center gap-5"
              href="../reportes/resultadoalumnos.html"><i class="bi bi-mortarboard"></i> Alumnos</a></li>
          <li hidden><a class="dropdown-item" href="ListadoDocentes.html"><i class="bi bi-person-badge"></i> Docentes</a>
          </li>
          <li hidden><a class="dropdown-item" href="ListadoEmpleados.html"><i class="bi bi-briefcase"></i> Empleados</a>
          </li>
        </ul>
      </div>

      <div class="menu-card position-relative" data-aos="fade-up" data-aos-delay="400" style="cursor: pointer;"
        id="cardCredenciales">
        <div class="w-100 d-flex flex-column align-items-center text-center">
          <i class="bi bi-credit-card-2-front-fill"></i>
          <h5 class="mt-3">Credenciales</h5>
        </div>
        <ul class="dropdown-menu dropdown-menu-center text-center" id="menuCredenciales">
          <li><a class="dropdown-item d-flex align-items-center justify-content-center gap-5"
              href="../credenciales/credencialesalumnos.html"><i class="bi bi-mortarboard"></i> Alumnos</a></li>
          <li hidden><a class="dropdown-item" href="ListadoDocentes.html"><i class="bi bi-person-badge"></i> Docentes</a>
          </li>
          <li hidden><a class="dropdown-item" href="ListadoEmpleados.html"><i class="bi bi-briefcase"></i> Empleados</a>
          </li>
        </ul>
      </div>


    <?php } ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Si no hay datos de sesión, simula una sesión
      if (!sessionStorage.getItem('nombre_usuario')) {
        // ⚡ Aquí normalmente colocarías los datos al iniciar sesión real
        sessionStorage.setItem('nombre_usuario', 'Juan Pérez');
        sessionStorage.setItem('rol_usuario', 'Administrativo');
        const ahora = new Date();
        sessionStorage.setItem('hora_fecha_ingreso', `${ahora.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${ahora.toLocaleDateString()}`);
      }

      // Mostrar datos almacenados
      document.getElementById("nombreUsuario").textContent = sessionStorage.getItem('nombre_usuario');
      document.getElementById("rolUsuario").textContent = sessionStorage.getItem('rol_usuario');
      document.getElementById("horaFechaIngreso").textContent = `Ingreso: ${sessionStorage.getItem('hora_fecha_ingreso')}`;

      // Cerrar Sesión
      document.getElementById("cerrarSesion").addEventListener("click", function () {
        sessionStorage.clear(); // Borra datos de sesión
        window.location.href = "login.html"; // Redirige al login
      });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Card Resultados
      document.getElementById('cardResultados').addEventListener('click', function (e) {
        e.stopPropagation();
        const menu = document.getElementById('menuResultados');

        // Cerrar el otro dropdown si está abierto
        document.getElementById('menuBusqueda').classList.remove('show');
        document.getElementById('menuCredenciales').classList.remove('show');

        // Toggle este dropdown
        menu.classList.toggle('show');
      });

      // Card Búsqueda
      document.getElementById('cardBusqueda').addEventListener('click', function (e) {
        e.stopPropagation();
        const menu = document.getElementById('menuBusqueda');

        // Cerrar el otro dropdown si está abierto
        document.getElementById('menuResultados').classList.remove('show');
        document.getElementById('menuCredenciales').classList.remove('show');

        // Toggle este dropdown
        menu.classList.toggle('show');
      });

      document.getElementById('cardCredenciales').addEventListener('click', function(e){
        e.stopPropagation();
        const menu = document.getElementById('menuCredenciales');


        document.getElementById('menuResultados').classList.remove('show');
        document.getElementById('menuBusqueda').classList.remove('show');
        
        menu.classList.toggle('show');
        
      })

      
      document.addEventListener('click', function () {
        document.getElementById('menuResultados').classList.remove('show');
        document.getElementById('menuBusqueda').classList.remove('show');
        document.getElementById('menuCredenciales').classList.remove('show');
      });
    });
  </script>
</body>

</html>