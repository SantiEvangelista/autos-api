<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Diesel Tokens
    |--------------------------------------------------------------------------
    |
    | Todas las formas en que las distintas marcas nombran motores diesel.
    | Se reemplazan por "Diesel" (word boundary, case-insensitive).
    |
    */
    'diesel_tokens' => ['TDi', 'TDI', 'DSL', 'HDI', 'CDI', 'TDCI'],

    /*
    |--------------------------------------------------------------------------
    | Abbreviations
    |--------------------------------------------------------------------------
    |
    | Abreviaturas simples con reemplazo directo (word boundary, case-insensitive).
    | Para agregar un nuevo mapeo, solo agregar una entrada al array.
    |
    */
    'abbreviations' => [
        'COMF' => 'Comfortline',
        'G2'   => 'Gen.2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Preserve Case
    |--------------------------------------------------------------------------
    |
    | Tokens que NO se pasan a Title Case al humanizar el nombre.
    | Se preservan exactamente como están listados acá.
    |
    */
    'preserve_case' => [
        // Transmisión
        'CVT', 'AT', 'MT', 'DSG',

        // Motor
        'CV', 'TSI', 'FSI', 'THP', 'MSI', 'JTS', 'TBI',

        // Cilindros
        'V6', 'V8', 'V10', 'V12',

        // Tracción
        '4X2', '4X4', '4WD', 'AWD',

        // Cabina
        'D/C', 'C/S',

        // Puertas
        '2P', '3P', '4P', '5P',

        // Seguridad / equipamiento
        'ABS', 'ESP', 'LED', 'GPS', 'GNC',

        // Transmisión compuesta
        '5MT', '6MT', '6AT', '7AT', '8AT', '9AT',

        // Válvulas
        '8V', '16V',

        // Otros
        'ABG', 'CRO', 'PLC',

        // Generados por el humanizador
        'Gen.2',
    ],
];
