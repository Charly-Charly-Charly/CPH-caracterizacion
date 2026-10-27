    </div> <!-- End Container -->
    
    <footer class="bg-light text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Sistema Laguna Larga. COPADEH.</p>
        </div>
    </footer>

    <!-- Chart.js and SheetJS for Data Analysis -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Si hubo un error al guardar, rellena de nuevo el formulario con lo que la
        // persona ya había escrito (window.__datosFormulario lo define includes/header.php).
        (function () {
            if (!window.__datosFormulario) return;
            var datos = window.__datosFormulario;
            var forms = document.querySelectorAll('form');
            forms.forEach(function (form) {
                Object.keys(datos).forEach(function (nombre) {
                    var valor = datos[nombre];

                    if (Array.isArray(valor)) {
                        var seleccionados = form.querySelectorAll('[name="' + nombre + '[]"], [name="' + nombre + '"]');
                        seleccionados.forEach(function (campo) {
                            if (campo.type === 'checkbox' || campo.type === 'radio') {
                                campo.checked = valor.indexOf(campo.value) !== -1;
                            }
                        });
                        return;
                    }

                    var campo = form.querySelector('[name="' + nombre + '"]');
                    if (!campo) return;

                    if (campo.type === 'checkbox') {
                        campo.checked = true;
                    } else if (campo.type === 'radio') {
                        var radios = form.querySelectorAll('[name="' + nombre + '"]');
                        radios.forEach(function (r) { r.checked = (r.value === String(valor)); });
                    } else {
                        campo.value = valor;
                    }
                });
            });
        })();

        // Form validations
        (function () {
          'use strict'
          var forms = document.querySelectorAll('.needs-validation')
          Array.prototype.slice.call(forms)
            .forEach(function (form) {
              form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                  event.preventDefault()
                  event.stopPropagation()
                }
                form.classList.add('was-validated')
              }, false)
            })
        })()
    </script>
</body>
</html>
