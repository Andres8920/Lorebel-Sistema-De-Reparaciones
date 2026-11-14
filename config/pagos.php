<?php

/*
|--------------------------------------------------------------------------
| Configuración del módulo de cobros (Iteración 2 - TFI)
|--------------------------------------------------------------------------
| Las credenciales de Mercado Pago y MODO se cargan en el archivo .env.
| Si no hay credenciales, la pasarela trabaja en MODO SIMULACIÓN: el sistema
| muestra un checkout simulado que permite aprobar o rechazar el pago para
| demostrar el circuito completo sin operar dinero real.
*/

return [

    // Regla de negocio: antes de que el equipo esté "listo" solo se admite una seña
    // de hasta este porcentaje del importe de la orden.
    'sena_maxima_porcentaje' => (float) env('PAGOS_SENA_MAXIMA', 50),

    // Recargo financiero por cantidad de cuotas con tarjeta de crédito (en %).
    'recargos_cuotas' => [
        1 => 0,
        3 => 10,
        6 => 20,
    ],

    // Datos que se muestran al cliente para pagar por transferencia.
    'transferencia' => [
        'titular' => env('PAGOS_TRANSF_TITULAR', 'Lorebel Servicio Técnico'),
        'banco'   => env('PAGOS_TRANSF_BANCO', 'Banco Santa Fe'),
        'cbu'     => env('PAGOS_TRANSF_CBU', '3300000000000000000000'),
        'alias'   => env('PAGOS_TRANSF_ALIAS', 'LOREBEL.TALLER.ROS'),
        'cuit'    => env('PAGOS_TRANSF_CUIT', '20-00000000-0'),
    ],

    'mercado_pago' => [
        'access_token'   => env('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key'     => env('MERCADOPAGO_PUBLIC_KEY'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
        'base_url'       => env('MERCADOPAGO_BASE_URL', 'https://api.mercadopago.com'),
        // Con credenciales de prueba (TEST-...) se usa sandbox_init_point.
        'sandbox'        => (bool) env('MERCADOPAGO_SANDBOX', true),
    ],

    'modo' => [
        // MODO entrega credenciales y documentación de la API al adherir el comercio
        // (directamente o a través de un procesador/agregador). Los endpoints son
        // configurables para adaptarlos a la versión de API que se habilite.
        'client_id'        => env('MODO_CLIENT_ID'),
        'client_secret'    => env('MODO_CLIENT_SECRET'),
        'store_id'         => env('MODO_STORE_ID'),
        'base_url'         => env('MODO_BASE_URL'),
        'ruta_token'       => env('MODO_RUTA_TOKEN', '/v2/stores/companies/token'),
        'ruta_intencion'   => env('MODO_RUTA_INTENCION', '/v2/payment-requests/'),
        'webhook_secret'   => env('MODO_WEBHOOK_SECRET'),
    ],
];
