    </div><!-- Cierra content-body -->

    <!-- El footer queda al final cuando el contenido es corto -->
    <footer class="text-center text-muted py-3 mt-auto border-top small">
        &copy; <?= date('Y') ?> <?= APP_NAME ?> — <?= UNIVERSIDAD ?>
    </footer>

</div><!-- Cierra content -->
</div><!-- Cierra wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const button = document.getElementById("sidebarCollapse");
        const sidebar = document.getElementById("sidebar");
        if (button && sidebar) {
            button.addEventListener("click", function() {
                sidebar.classList.toggle("collapsed");
            });
        }
    });
</script>
</body>
</html>
