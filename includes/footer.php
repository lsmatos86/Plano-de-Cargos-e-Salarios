<?php
// Arquivo: includes/footer.php (Corrigido)
?>
    
    </main> <footer class="footer">
    <div class="container text-center">
        <span class="text-white-50">
            ITACITRUS | Sistema de Plano de Cargos e Salários &copy; <?php echo date('Y'); ?>
        </span>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>


<?php
/**
 * Lógica para carregar scripts JS específicos da página.
 * Ex: A página cargos_form.php pode definir no topo:
 * $page_scripts = ['../scripts/cargos_form.js'];
 * (Este bloco carregará cargos_form.js DEPOIS do jQuery e Select2)
 */
if (isset($page_scripts) && is_array($page_scripts)) {
    foreach ($page_scripts as $script) {
        // Adiciona um '?' e um timestamp para evitar cache do navegador em desenvolvimento
        $version_query = '?v=' . time(); 
        echo '<script src="' . htmlspecialchars($script) . $version_query . '"></script>' . "\n";
    }
}
?>
</body>
</html>