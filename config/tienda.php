<?php

/*
| Datos del negocio que se muestran en la tienda pública.
| La dirección y el teléfono están en la tabla "sucursales"; aquí va lo que
| no necesita base de datos. Para cambiarlo basta con editar este archivo.
| Se lee con config('tienda.horario').
*/

return [

    'horario' => [
        'Lunes a viernes' => '9:00 a 21:00',
        'Sábado' => '10:00 a 22:00',
        'Domingo' => '10:00 a 18:00',
    ],

];
