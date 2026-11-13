<?php
/**
 * TaskFlow – Smart Task Management System
 * Global Footer Template Partial
 */

declare(strict_types=1);

if (!defined('TASKFLOW_APP')) {
    define('TASKFLOW_APP', true);
}
?>
    </div><!-- /.container -->
</main>

<footer class="bg-white border-top py-4 mt-auto">
    <div class="container text-center">
        <div class="row align-items-center gy-2">
            <div class="col-md-6 text-md-start">
                <span class="fw-bold text-dark">Task<span class="text-primary">Flow</span></span>
                <span class="text-muted small ms-2">© <?= date('Y'); ?> RTIH Amaravati Foundation. All rights reserved.</span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge bg-light text-secondary border small px-2 py-1 me-1">Core PHP 8+</span>
                <span class="badge bg-light text-secondary border small px-2 py-1 me-1">MySQL PDO</span>
                <span class="badge bg-light text-secondary border small px-2 py-1 me-1">Bootstrap 5</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle small px-2 py-1">CSRF & XSS Protected</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom Application Scripts -->
<script src="<?= e(base_url('assets/js/script.js')); ?>"></script>
</body>
</html>
