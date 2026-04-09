<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Carrocería: prefijo → slug
    |--------------------------------------------------------------------------
    |
    | Ordenados de más largo a más corto para que startsWith() en el frontend
    | matchee el prefijo más específico primero.
    |
    */

    'prefixes' => [
        'PATAGONICA'  => 'pickup',
        'C/EXTEN.'    => 'pickup',
        'C/EXT.'      => 'pickup',
        'C/PLUS'      => 'pickup',
        'SL-ROADSTER' => 'convertible',
        'GT-ROADSTER' => 'convertible',
        'C-ROADSTER'  => 'convertible',
        'L1H1PANEL'   => 'utilitario',
        'SPORTBACK'   => 'familiar',
        'MULTISPACE'  => 'familiar',
        'CABRIOLET'   => 'convertible',
        'COMMUTER'    => 'utilitario',
        'COMBINATO'   => 'utilitario',
        'GT-COUPE'    => 'coupe',
        'A-COUPE'     => 'coupe',
        'ROADSTER'    => 'convertible',
        'WEEKEND'     => 'familiar',
        'MINIBUS'     => 'utilitario',
        'FURGON'      => 'utilitario',
        'TOURER'      => 'familiar',
        'SPYDER'      => 'convertible',
        'CABRIO'      => 'convertible',
        'CHASIS'      => 'utilitario',
        'BREAK'       => 'familiar',
        'AVANT'       => 'familiar',
        'WAGON'       => 'familiar',
        'RURAL'       => 'familiar',
        'COUPE'       => 'coupe',
        'TRUCK'       => 'utilitario',
        'COMBI'       => 'utilitario',
        'U-VAN'       => 'utilitario',
        'C/EXT'       => 'pickup',
        'CONV'        => 'convertible',
        'GRAN'        => 'coupe',
        'KOUP'        => 'coupe',
        'PICK'        => 'pickup',
        'D/C'         => 'pickup',
        'C/C'         => 'pickup',
        'C/D'         => 'pickup',
        'C/S'         => 'pickup',
        'SWB'         => 'utilitario',
        'VAV'         => 'utilitario',
        'VAN'         => 'utilitario',
        'BOX'         => 'utilitario',
        'SW '         => 'familiar',
        'CC '         => 'coupe',
        'SL '         => 'convertible',
        '5P'          => 'suv',
        '4P'          => 'sedan',
        '3P'          => '3p',
        '2P'          => '2p',
        // Mercedes numéricos (utilitarios: Sprinter, Vito)
        '311'         => 'utilitario',
        '313'         => 'utilitario',
        '314'         => 'utilitario',
        '316'         => 'utilitario',
        '308'         => 'utilitario',
        '411'         => 'utilitario',
        '413'         => 'utilitario',
        '414'         => 'utilitario',
        '415'         => 'utilitario',
        '416'         => 'utilitario',
        '417'         => 'utilitario',
        '515'         => 'utilitario',
        '516'         => 'utilitario',
        '517'         => 'utilitario',
        // Changan y Land Rover
        '201'         => 'suv',
        '110'         => 'suv',
    ],

    'labels' => [
        'suv'          => 'SUV',
        'sedan'        => 'Sedán',
        'pickup'       => 'Pickup',
        'utilitario'   => 'Utilitario',
        'coupe'        => 'Coupé',
        'convertible'  => 'Convertible',
        'familiar'     => 'Familiar',
        '3p'           => '3 Puertas',
        '2p'           => '2 Puertas',
    ],

    /*
    |--------------------------------------------------------------------------
    | Combustible: keyword → slug
    |--------------------------------------------------------------------------
    |
    | Se busca con includes() case-insensitive sobre version_raw.
    | Ordenados de más específico a menos para que eléctrico/híbrido
    | se detecten antes que diesel.
    | Nafta es implícito (cuando ningún keyword matchea).
    |
    */

    'fuel_keywords' => [
        'ELECTRICO'   => 'electrico',
        'ELECTRIC'    => 'electrico',
        'E-POWER'     => 'electrico',
        'MILD-HYBRID' => 'hibrido',
        'MILDHYBRID'  => 'hibrido',
        'HYBRID'      => 'hibrido',
        'PHEV'        => 'hibrido',
        'MHEV'        => 'hibrido',
        'TDCI'        => 'diesel',
        'CRDI'        => 'diesel',
        'DIESEL'      => 'diesel',
        'TDI'         => 'diesel',
        'CDI'         => 'diesel',
        'HDI'         => 'diesel',
        'DCI'         => 'diesel',
        'DSL'         => 'diesel',
    ],

    'fuel_labels' => [
        'nafta'     => 'Nafta',
        'diesel'    => 'Diesel',
        'hibrido'   => 'Híbrido',
        'electrico' => 'Eléctrico',
    ],

];
