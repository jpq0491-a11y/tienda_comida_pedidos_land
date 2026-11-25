/*
 * JavaScript del checkout:
 * 1. Borrador: guarda lo que el cliente escribe en sessionStorage para que no se pierda
 *    si sale a modificar el carrito y regresa.
 *    - sessionStorage se borra al cerrar la pestaña (no deja datos en computadoras compartidas).
 *    - Nada se manda al servidor hasta que el cliente confirma el pedido.
 * 2. Muestra la dirección y "¿con cuánto pagas?" solo si elige entrega a domicilio.
 * 3. Calcula el cambio en vivo. Es solo un aviso: el servidor vuelve a validar la cantidad.
 */
(function () {
    const formulario = document.getElementById('formulario-checkout');

    // Este archivo solo se carga en el checkout, pero por si acaso
    if (!formulario) {
        return;
    }

    const CLAVE = 'borrador-checkout';
    const CAMPOS = [
        'nombre_cliente', 'telefono', 'notas',
        'calle', 'numero_exterior', 'numero_interior', 'colonia', 'referencia', 'paga_con',
    ];

    const datosDomicilio = document.getElementById('datos-domicilio');
    // Calle, número exterior y colonia: obligatorios solo con domicilio
    const camposRequeridos = datosDomicilio.querySelectorAll('[data-requerido]');
    const pagaCon = formulario.elements['paga_con'];
    const cambio = document.getElementById('cambio');

    // Se trabaja en centavos (enteros): 0.1 + 0.2 en JavaScript da 0.30000000000000004
    const totalCentavos = Math.round(parseFloat(formulario.dataset.total) * 100);
    const formatoPesos = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

    /* ---------- Borrador ---------- */

    // Algunos navegadores bloquean sessionStorage (por ejemplo en modo privado estricto)
    function leerBorrador() {
        try {
            return JSON.parse(sessionStorage.getItem(CLAVE)) || {};
        } catch (error) {
            return {};
        }
    }

    function guardarBorrador() {
        const borrador = { tipo_entrega: formulario.elements['tipo_entrega'].value };
        CAMPOS.forEach(function (nombre) {
            borrador[nombre] = formulario.elements[nombre].value;
        });

        try {
            sessionStorage.setItem(CLAVE, JSON.stringify(borrador));
        } catch (error) {
            // Si no se puede guardar, el formulario sigue funcionando normal
        }
    }

    /* ---------- Entrega a domicilio ---------- */

    function esDomicilio() {
        return formulario.elements['tipo_entrega'].value === 'domicilio';
    }

    function mostrarDatosDomicilio() {
        const domicilio = esDomicilio();
        datosDomicilio.classList.toggle('d-none', !domicilio);
        camposRequeridos.forEach(function (campo) {
            campo.required = domicilio;
        });

        // En sucursal no se pregunta con cuánto paga: se vacía para que no se mande
        if (!domicilio) {
            pagaCon.value = '';
            calcularCambio();
        }
    }

    function calcularCambio() {
        cambio.classList.remove('text-success', 'text-danger');

        if (pagaCon.value === '' || isNaN(parseFloat(pagaCon.value))) {
            cambio.textContent = '';
            return;
        }

        const pagaCentavos = Math.round(parseFloat(pagaCon.value) * 100);
        const diferencia = pagaCentavos - totalCentavos;

        if (diferencia >= 0) {
            cambio.textContent = diferencia === 0
                ? 'Pagas con el monto exacto.'
                : 'Tu cambio: ' + formatoPesos.format(diferencia / 100);
            cambio.classList.add('text-success');
        } else {
            cambio.textContent = 'Te faltan ' + formatoPesos.format(-diferencia / 100);
            cambio.classList.add('text-danger');
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

    // El radio siempre trae valor ("recoger" por defecto), así que se usa el borrador
    // solo si el servidor no regresó el formulario con errores
    if (!('conErrores' in formulario.dataset) && borrador.tipo_entrega) {
        formulario.elements['tipo_entrega'].value = borrador.tipo_entrega;
    }

    mostrarDatosDomicilio();
    calcularCambio();

    // 2. Cada vez que el cliente escribe o elige algo, se actualiza la vista y se guarda el borrador
    formulario.addEventListener('change', function (evento) {
        if (evento.target.name === 'tipo_entrega') {
            mostrarDatosDomicilio();
            guardarBorrador();
        }
    });
    pagaCon.addEventListener('input', calcularCambio);
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
