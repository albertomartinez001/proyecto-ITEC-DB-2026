<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador', 'docente']);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$rol = rolActual();
$buscar = trim($_GET['q'] ?? '');
$resultados = [];
$errorBusqueda = null;

$escapar = function ($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};

if ($buscar !== '') {
    $parametros = [];
    $numeroParametro = 1;

    // Crea un parámetro distinto para cada condición de búsqueda.
    $nuevoParametro = function () use (&$parametros, &$numeroParametro, $buscar) {
        $nombre = ':q' . $numeroParametro++;
        $parametros[$nombre] = '%' . $buscar . '%';
        return $nombre;
    };

    try {
        $consultas = [];

        // Estudiantes. Si es docente, solo busca estudiantes de sus secciones.
        $joinEstudiantes = '';
        $filtroEstudiantes = '';

        if ($rol === 'docente') {
            $joinEstudiantes = "
                JOIN asignacion_est ae ON ae.estudiante_id_est = e.id_est
                JOIN seccion s_est ON s_est.id_sec = ae.seccion_id_sec
                JOIN docente d_est ON d_est.id_dte = s_est.docente_id_dte
            ";
            $filtroEstudiantes = 'd_est.usuario_iduser = :id_docente_est AND ';
            $parametros[':id_docente_est'] = (int)$_SESSION['usuario']['iduser'];
        }

        $condicionesEstudiantes = [
            'LOWER(e.nombre) LIKE LOWER(' . $nuevoParametro() . ')',
            'LOWER(e.apellido) LIKE LOWER(' . $nuevoParametro() . ')',
            'TO_CHAR(e.carnet) LIKE ' . $nuevoParametro()
        ];

        $consultas[] = "
            SELECT DISTINCT
                'Estudiante' AS tipo,
                TO_CHAR(e.carnet) AS codigo,
                e.nombre || ' ' || e.apellido AS nombre,
                'Carnet: ' || TO_CHAR(e.carnet) AS detalle
            FROM estudiante e
            $joinEstudiantes
            WHERE $filtroEstudiantes
                (" . implode(' OR ', $condicionesEstudiantes) . ")
        ";

        // Docentes: se muestran sus nombres, sin exponer su DPI.
        $condicionesDocentes = [
            'LOWER(d.nombre) LIKE LOWER(' . $nuevoParametro() . ')',
            'LOWER(d.apellido) LIKE LOWER(' . $nuevoParametro() . ')'
        ];

        $consultas[] = "
            SELECT
                'Docente' AS tipo,
                CAST(NULL AS VARCHAR2(30)) AS codigo,
                d.nombre || ' ' || d.apellido AS nombre,
                'Docente registrado' AS detalle
            FROM docente d
            WHERE " . implode(' OR ', $condicionesDocentes);

        // Cursos.
        $condicionesCursos = [
            'LOWER(c.nombre) LIKE LOWER(' . $nuevoParametro() . ')',
            'TO_CHAR(c.cod_cso) LIKE ' . $nuevoParametro(),
            'LOWER(ca.nombrecrra) LIKE LOWER(' . $nuevoParametro() . ')'
        ];

        $consultas[] = "
            SELECT
                'Curso' AS tipo,
                TO_CHAR(c.cod_cso) AS codigo,
                c.nombre AS nombre,
                'Carrera: ' || ca.nombrecrra ||
                ' | Créditos: ' || TO_CHAR(c.creditos) AS detalle
            FROM curso c
            JOIN pensum p ON p.id_psum = c.pensum_id_psum
            JOIN carrera ca ON ca.id_crra = p.carrera_id_crra
            WHERE " . implode(' OR ', $condicionesCursos);

        // Carreras y vigencia de su pensum.
        $condicionesCarreras = [
            'LOWER(ca.nombrecrra) LIKE LOWER(' . $nuevoParametro() . ')',
            'TO_CHAR(p.a_vigencia) LIKE ' . $nuevoParametro()
        ];

        $consultas[] = "
            SELECT DISTINCT
                'Carrera' AS tipo,
                TO_CHAR(ca.id_crra) AS codigo,
                ca.nombrecrra AS nombre,
                CASE
                    WHEN p.a_vigencia IS NULL THEN 'Sin pensum'
                    ELSE 'Vigencia: ' || TO_CHAR(p.a_vigencia)
                END AS detalle
            FROM carrera ca
            LEFT JOIN pensum p ON p.carrera_id_crra = ca.id_crra
            WHERE " . implode(' OR ', $condicionesCarreras);

        // Secciones. Los docentes solo ven las que tienen asignadas.
        $condicionesSecciones = [
            'TO_CHAR(s.id_sec) LIKE ' . $nuevoParametro(),
            'LOWER(s.semestre) LIKE LOWER(' . $nuevoParametro() . ')',
            'TO_CHAR(s.anio) LIKE ' . $nuevoParametro(),
            'TO_CHAR(c.cod_cso) LIKE ' . $nuevoParametro(),
            'LOWER(c.nombre) LIKE LOWER(' . $nuevoParametro() . ')',
            'LOWER(d.nombre) LIKE LOWER(' . $nuevoParametro() . ')',
            'LOWER(d.apellido) LIKE LOWER(' . $nuevoParametro() . ')'
        ];

        $filtroSecciones = '';

        if ($rol === 'docente') {
            $filtroSecciones = ' AND d.usuario_iduser = :id_docente_sec';
            $parametros[':id_docente_sec'] = (int)$_SESSION['usuario']['iduser'];
        }

        $consultas[] = "
            SELECT
                'Sección' AS tipo,
                TO_CHAR(s.id_sec) AS codigo,
                c.nombre AS nombre,
                'Sección ' || s.semestre || ' ' || TO_CHAR(s.anio) ||
                ' | Docente: ' || d.nombre || ' ' || d.apellido AS detalle
            FROM seccion s
            JOIN curso c ON c.id_cso = s.curso_id_cso
            JOIN docente d ON d.id_dte = s.docente_id_dte
            WHERE (" . implode(' OR ', $condicionesSecciones) . ")
            $filtroSecciones
        ";

        $sql = "
            SELECT tipo, codigo, nombre, detalle
            FROM (
                " . implode("\nUNION ALL\n", $consultas) . "
            )
            ORDER BY tipo, nombre
            FETCH FIRST 100 ROWS ONLY
        ";

        $stmt = $db->prepare($sql);

        foreach ($parametros as $nombre => $valor) {
            $tipoParametro = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($nombre, $valor, $tipoParametro);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log('Error en búsqueda académica: ' . $e->getMessage());
        $errorBusqueda = 'No se pudo completar la búsqueda. Revisa la conexión con la base de datos.';
    }
}
?>

<h3 class="mb-1">
    <i class="bi bi-search"></i> Búsqueda
</h3>
<p class="text-muted mb-4">
    Busca estudiantes, docentes, cursos, carreras o secciones.
</p>

<form method="get"
      action="<?= BASE_URL ?>modulos/busqueda/index.php"
      class="card p-3 mb-4">

    <div class="row g-2">
        <div class="col-md-9">
            <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Escribe un nombre, carnet, código o año..."
                value="<?= $escapar($buscar) ?>"
                required
            >
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Buscar
            </button>

            <?php if ($buscar !== ''): ?>
                <a href="<?= BASE_URL ?>modulos/busqueda/index.php"
                   class="btn btn-outline-secondary">
                    Limpiar
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($errorBusqueda !== null): ?>

    <div class="alert alert-danger">
        <?= $escapar($errorBusqueda) ?>
    </div>

<?php elseif ($buscar === ''): ?>

    <div class="alert alert-info">
        Escribe algo en el cuadro de búsqueda para ver resultados.
    </div>

<?php elseif (empty($resultados)): ?>

    <div class="alert alert-warning">
        No se encontraron resultados para
        <strong><?= $escapar($buscar) ?></strong>.
    </div>

<?php else: ?>

    <div class="card">
        <div class="card-header bg-white">
            Resultados para <strong><?= $escapar($buscar) ?></strong>
            <span class="badge bg-primary ms-2"><?= count($resultados) ?></span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>Tipo</th>
                        <th>Código / Carnet</th>
                        <th>Nombre</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $resultado): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $escapar($resultado['tipo']) ?>
                                </span>
                            </td>
                            <td><?= $escapar($resultado['codigo']) ?></td>
                            <td><?= $escapar($resultado['nombre']) ?></td>
                            <td><?= $escapar($resultado['detalle']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (count($resultados) === 100): ?>
        <p class="small text-muted mt-2">
            Se muestran hasta 100 resultados. Prueba con una búsqueda más específica.
        </p>
    <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>