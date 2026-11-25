/*
 * JavaScript del checkout:
 * 1. Borrador: guarda lo que el cliente escribe en sessionStorage para que no se pierda
 *    si sale a modificar el carrito y regresa.
 *    - sessionStorage se borra al cerrar la pestaña (no deja datos en computadoras compartidas).
 *    - Nada se manda al servidor hasta que el cliente confirma el pedido.
 * 2. Muestra la dirección, el mapa y "¿con cuánto pagas?" solo si elige entrega a domicilio.
 * 3. Calcula el cambio en vivo. Es solo un aviso: el servidor vuelve a validar la cantidad.
 * 4. Mapa (Leaflet + OpenStreetMap): el cliente marca su casa con un clic o con
 *    "Usar mi ubicación" y se llenan los inputs hidden latitud y longitud.
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
        'calle', 'numero_exterior', 'numero_interior', 'colonia', 'referencia',
        'latitud', 'longitud', 'paga_con',
    ];

    const datosDomicilio = document.getElementById('datos-domicilio');
    // Calle, número exterior y colonia: obligatorios solo con domicilio
    const camposRequeridos = datosDomicilio.querySelectorAll('[data-requerido]');
    const pagaCon = formulario.elements['paga_con'];
    const cambio = document.getElementById('cambio');

    const contenedorMapa = document.getElementById('mapa-entrega');
    const latitud = formulario.elements['latitud'];
    const longitud = formulario.elements['longitud'];
    const botonUbicacion = document.getElementById('usar-mi-ubicacion');
    const avisoUbicacion = document.getElementById('aviso-ubicacion');
    let mapa = null;
    let marcador = null;
    let ubicacionSucursal = null;
    const formatoKm = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

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

        if (domicilio) {
            crearMapa();
        } else {
            // En sucursal no se pregunta con cuánto paga: se vacía para que no se mande
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

    /* ---------- Mapa ---------- */

    function avisar(texto, esError) {
        avisoUbicacion.textContent = texto;
        avisoUbicacion.classList.toggle('text-danger', esError === true);
    }

    // El mapa se crea la primera vez que se muestra: si se crea dentro de un div oculto,
    // Leaflet no sabe su tamaño y se ve cortado
    function crearMapa() {
        // L es Leaflet; si no cargó (sin internet, por ejemplo) o no hay sucursal, no hay mapa
        if (!contenedorMapa || typeof L === 'undefined') {
            return;
        }

        if (mapa) {
            mapa.invalidateSize(); // por si se ocultó y se volvió a mostrar
            return;
        }

        ubicacionSucursal = L.latLng(parseFloat(contenedorMapa.dataset.latitud), parseFloat(contenedorMapa.dataset.longitud));
        mapa = L.map(contenedorMapa).setView(ubicacionSucursal, 13);

        // Imágenes del mapa de OpenStreetMap; su licencia pide mostrar el crédito
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(mapa);

        // La sucursal con un círculo de los colores de la tienda, distinto al marcador del cliente
        L.circleMarker(ubicacionSucursal, { radius: 9, color: '#8f3520', fillColor: '#b5452a', fillOpacity: 1 })
            .addTo(mapa)
            .bindTooltip(contenedorMapa.dataset.sucursal);

        mapa.on('click', function (evento) {
            colocarMarcador(evento.latlng);
        });

        // Si ya había un punto (borrador u old() tras un error), se vuelve a poner
        if (latitud.value !== '' && longitud.value !== '') {
            const punto = L.latLng(parseFloat(latitud.value), parseFloat(longitud.value));
            colocarMarcador(punto);
            mapa.setView(punto, 15);
        }
    }

    function colocarMarcador(punto) {
        // wrap(): si el mapa se movió "una vuelta al mundo", deja la longitud entre -180 y 180
        punto = punto.wrap();

        if (marcador) {
            marcador.setLatLng(punto);
        } else {
            // draggable: el cliente puede arrastrarlo para ajustar
            marcador = L.marker(punto, { draggable: true, title: 'Lugar de entrega' }).addTo(mapa);
            marcador.on('dragend', function () {
                colocarMarcador(marcador.getLatLng());
            });
        }

        // 7 decimales, como la columna decimal(10, 7) de la base de datos
        latitud.value = punto.lat.toFixed(7);
        longitud.value = punto.lng.toFixed(7);

        // distanceTo() de Leaflet usa la misma fórmula de Haversine que el servidor y regresa metros.
        // Es solo un aviso: el servidor la vuelve a calcular con app/Services/CalculadoraDistancia.php
        const kilometros = ubicacionSucursal.distanceTo(punto) / 1000;
        avisar('Estás a ' + formatoKm.format(kilometros) + ' km de la sucursal (en línea recta).');

        // Los inputs hidden no disparan el evento input: se guarda a mano
        guardarBorrador();
    }

    function usarMiUbicacion() {
        if (!mapa) {
            return;
        }

        // Solo funciona en páginas seguras (https o localhost) y si el cliente da permiso
        if (!navigator.geolocation) {
            avisar('Tu navegador no puede darnos tu ubicación. Márcala con un clic en el mapa.', true);
            return;
        }

        avisar('Buscando tu ubicación...');
        navigator.geolocation.getCurrentPosition(
            function (posicion) {
                const punto = L.latLng(posicion.coords.latitude, posicion.coords.longitude);
                colocarMarcador(punto);
                mapa.setView(punto, 16);
            },
            function () {
                avisar('No pudimos obtener tu ubicación (revisa el permiso del navegador). Márcala con un clic en el mapa.', true);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    /* ---------- Al abrir la página ---------- */

    // 1. Se llenan los campos con el borrador.
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
    botonUbicacion.addEventListener('click', usarMiUbicacion);

    // 3. Al confirmar: los inputs hidden no se pueden marcar como required, así que aquí se revisa
    //    que haya un punto en el mapa (el servidor lo vuelve a validar).
    formulario.addEventListener('submit', function (evento) {
        if (esDomicilio() && mapa && (latitud.value === '' || longitud.value === '')) {
            evento.preventDefault();
            avisar('Marca en el mapa dónde te entregamos tu pedido.', true);
            contenedorMapa.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Ya no hace falta el borrador. Si el servidor rechaza los datos, old() los vuelve a poner.
        try {
            sessionStorage.removeItem(CLAVE);
        } catch (error) {
            // Nada que hacer
        }
    });
})();
