<?php
/**
 * Lista delle costanti
*/

return [

    'ACTIVE' => 1,
    'NO_ACTIVE' => 0,

    'SUPER_ACTION' => [
        'LOGIN' => "Login",
        'UPDATE' => "Update",
        'INSERT' => "Insert",
        'DELETE' => "Delete",
    ],


    'OBIETTIVO' => [
        '1' => [
            'icon' => 'fas fa-stopwatch',
            'text' => 'Prestazioni sanitarie',
            'tooltip' => 'Riduzione dei tempi delle liste di attesa delle prestazioni sanitarie ',
            'route' => "/tempiListeAttesa",
            'enable' => true,
        ],
        '2' => [
            'icon' => 'fas fa-bed-pulse',
            'text' => 'Esiti',
            'tooltip' => 'Esiti',
            'route' => null, //route('chart-esiti')
            'enable' => false,
        ],
        '3' => [
            'icon' => 'fa-solid fa-person-pregnant',
            'text' => 'Checklist punti nascita',
            'tooltip' => 'Rispetto degli standard di sicurezza dei punti nascita',
            'route' => "/showObiettivo/3",
            'enable' => true,
        ],
        '4' => [
            'icon' => 'fas fa-truck-medical',
            'text' => 'Sovraffollamento PS',
            'tooltip' => 'Pronto Soccorso - Gestione del sovraffollamento',
            'route' => null, //route('chart-ps')
            'enable' => false,
        ],
        '5' => [
            'icon' => 'fas fa-heartbeat',
            'text' => 'Screening',
            'tooltip' => 'Screening oncologici',
            'route' => "/screening",
            'enable' => true,
        ],
        '6' => [
            'icon' => 'fas fa-hand-holding-medical',
            'text' => 'Donazioni',
            'tooltip' => 'Donazione sangue, plasma, organi e tessuti',
            'route' => null ,//route('indexDonazioni')
            'enable' => false,
        ],
        '7' => [
            'icon' => 'fas fa-file-medical',
            'text' => 'Fascicolo Sanitario Elettronico',
            'tooltip' => 'Fascicolo Sanitario Elettronico',
            'route' => null,
            'enable' => false,
        ],
        '8' => [
            'icon' => 'fas fa-check-circle',
            'text' => 'Percorso attuativo di certificabilità',
            'tooltip' => 'Percorso attuativo di certificabilità (P.A.C.)',
            'route' => "/showObiettivo/8",
            'enable' => true,
        ],
        '9' => [
            'icon' => 'fas fa-pills',
            'text' => 'Farmaci',
            'tooltip' => 'Approvvigionamento farmaci e gestione I ciclo di terapia',
            'route' => "/farmaciIndex",
            'enable' => true,
        ],
        '10' => [
            'icon' => 'fas fa-tasks',
            'text' => 'Garanzia dei LEA',
            'tooltip' => 'Area della Performance: garanzia dei LEA nell\'Area della Prevenzione, dell\'Assistenza Territoriale e dell\'Assistenza Ospedaliera secondo il Nuovo Sistema di Garanzia (NSG)',
            'route' => null, //route('indexLEA')
            'enable' => false,
        ]
    ],


    'MESI' => [
        'Gennaio' => 1,
        'Febbraio' => 2,
        'Marzo' => 3,
        'Aprile' => 4,
        'Maggio' => 5,
        'Giugno' => 6,
        'Luglio' => 7,
        'Agosto' => 8,
        'Settembre' => 9,
        'Ottobre' => 10,
        'Novembre' => 11,
        'Dicembre' => 12,
    ],
];