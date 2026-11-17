# Sabor de Casa — Tienda en línea de comida

Proyecto para la materia PPI. Página web de un negocio de comida donde los clientes ven el menú, arman su pedido sin crear cuenta y eligen recogerlo en la sucursal o recibirlo a domicilio. El administrador gestiona el menú y los pedidos desde un panel.

## Funcionalidades

| Módulo | Descripción |
|---|---|
| Landing | Portada del negocio, quiénes somos, horario y ubicación de la sucursal. |
| Menú | Productos agrupados por categoría. |
| Carrito | Carrito de invitado guardado en la sesión, sin necesidad de cuenta. |
| Checkout | Datos del cliente, tipo de entrega y pago solo en efectivo, con cálculo de cambio. |
| Entrega a domicilio | Solo se acepta si la dirección está a 15 km o menos de la sucursal (mapa con Leaflet y cálculo con la fórmula de Haversine). |
| Panel admin | Login, CRUD de categorías y productos, lista de pedidos pendientes y realizados, y cambio de estado. |

## Tecnologías

- Laravel 13 y PHP 8.3
- Starter kit de Vue (Inertia + Tailwind + Fortify) solo para la autenticación
- Blade + Bootstrap 5 para la tienda y el panel admin
- SQLite
- Pest para las pruebas
- Leaflet + OpenStreetMap para el mapa de entrega

## Estado

En desarrollo. Las instrucciones de instalación y uso se agregarán al terminar el proyecto.
