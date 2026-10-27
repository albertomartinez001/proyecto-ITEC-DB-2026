<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$carreras = $db->query("SELECT nombrecrra FROM carrera ORDER BY nombrecrra")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12 text-center py-4 bg-primary text-white rounded shadow-sm">
        <h1 class="display-5"><i class="bi bi-mortarboard-fill"></i> <?= UNIVERSIDAD ?></h1>
        <p class="lead">Excelencia académica al servicio de Guatemala</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light"><h5 class="mb-0"><i class="bi bi-info-circle text-primary"></i> Sobre Nosotros</h5></div>
            <div class="card-body">
                <p>La <strong>Universidad Reiss de Guatemala</strong> es una institución comprometida con la formación de profesionales íntegros, con visión humanista y capacidad técnica. A través de nuestro sistema <strong>ITEC Academy</strong>, facilitamos la gestión académica y el seguimiento continuo de nuestros estudiantes.</p>
                <p class="mb-0">Nuestro objetivo es centralizar la información académica y facilitar el acceso, organización y seguimiento de los procesos educativos.</p>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light"><h5 class="mb-0"><i class="bi bi-geo-alt text-danger"></i> Contacto y Ubicación</h5></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3"><strong><i class="bi bi-geo-alt-fill"></i> Dirección:</strong><br>5ta Avenida 10-50, Zona 10<br>Ciudad de Guatemala</li>
                    <li class="mb-3"><strong><i class="bi bi-telephone-fill"></i> Teléfono:</strong><br>+502 2222-3333</li>
                    <li class="mb-3"><strong><i class="bi bi-envelope-fill"></i> Correo:</strong><br>info@reiss.edu.gt</li>
                    <li><strong><i class="bi bi-clock-fill"></i> Horario:</strong><br>Lunes a Viernes, 07:00 - 19:00</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light"><h5 class="mb-0"><i class="bi bi-journal-bookmark-fill text-success"></i> Carreras Disponibles</h5></div>
            <div class="card-body">
                <?php if ($carreras): ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                        <?php foreach ($carreras as $c): ?>
                            <div class="col"><div class="border rounded p-3 bg-light"><i class="bi bi-check-circle-fill text-success me-2"></i> <?= htmlspecialchars($c['nombrecrra']) ?></div></div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Próximamente estaremos publicando nuestras carreras.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>