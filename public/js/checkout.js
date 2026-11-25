/*
 * Borrador del checkout: guarda lo que el cliente escribe en sessionStorage
 * para que no se pierda si sale a modificar el carrito y regresa.
 *
 * - sessionStorage se borra al cerrar la pestaña (no deja datos en computadoras compartidas).
 * - Nada se manda al servidor hasta que el cliente confirma el pedido.
 */
(function () {
    const formulario = document.getElementById('formulario-checkout');

    // Este archivo solo se carga en el checkout, pero por si acaso
    if (!formulario) {
        return;
    }

    const CLAVE = 'borrador-checkout';
    const CAMPOS = ['nombre_cliente', 'telefono', 'notas'];

    // Algunos navegadores bloquean sessionStorage (por ejemplo en modo privado estricto)
    function leerBorrador() {
        try {
            return JSON.parse(sessionStorage.getItem(CLAVE)) || {};
        } catch (error) {
            return {};
        }
    }

    function guardarBorrador() {
        const borrador = {};
        CAMPOS.forEach(function (nombre) {
            borrador[nombre] = formulario.elements[nombre].value;
        });

        try {
            sessionStorage.setItem(CLAVE, JSON.stringify(borrador));
        } catch (error) {
            // Si no se puede guardar, el formulario sigue funcionando normal
        }
    }

    // 1. Al abrir la página se llenan los campos con el borrador.
    //    Si el campo ya trae valor (old() después de un error de validación), ese tiene prioridad.
    const borrador = leerBorrador();
    CAMPOS.forEach(function (nombre) {
        const campo = formulario.elements[nombre];
        if (campo.value === '' && borrador[nombre]) {
            campo.value = borrador[nombre];
        }
    });

    // 2. Cada vez que el cliente escribe, se guarda el borrador
    formulario.addEventListener('input', guardarBorrador);

    // 3. Al confirmar el pedido ya no hace falta el borrador.
    //    Si el servidor rechaza los datos, old() los vuelve a poner en los campos.
    formulario.addEventListener('submit', function () {
        try {
            sessionStorage.removeItem(CLAVE);
        } catch (error) {
            // Nada que hacer
        }
    });
})();
