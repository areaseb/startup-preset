<?php

return [
    'env' => '1',//1 => produzione, 0 => demo, -1 => test
    'trasmittente' => false,
    'piva_trasmittente' => '01879020517',
    'version' => 'FPR12',
    'types' => [
        'F' => 'TD01',
        'A' => 'TD04',
    ],
    'sendables' => [0,2,3,4,5],
    'status' => [
        0 => "Da inviare",
        1 => "Presa in carico",
        2 => "Errore laborazione",
        3 => "Inviata",
        4 => "Scartata",
        5 => "Non consegnata",
        6 => "Recapito impossibile",
        7 => "Consegnata",
        8 => "Accettata",
        9 => "Rifiutata",
        10 => "Decorrenza termini"
    ],
    'status_feic' => [
        0 => "not_sent",
        1 => "accepted",
        2 => "error",
        3 => "sent",
        4 => "discarded",
        5 => "not_delivered",
        6 => "missing",
        7 => "accepted_2",
        8 => "accepted_3",
        9 => "rejected",
        10 => "no_response"
    ],
    'vat_feic' => [
        1 => 21,
        3 => 35,
        12 => 46,
        4 => 287745
    ],
    'payment_methods' => [
        "RIDI" => "MP01",
        "ASSE" => "MP02",
        "BOFM" => "MP05",
        "POSS" => "MP08",
        "BOAN" => "MP05",
        "BO15" => "MP05",
        "BO30" => "MP05",
        "BO60" => "MP05",
        "BOVF" => "MP05",
        "BO2P" => "MP05",
        "BO3P" => "MP05",
        "BO5P" => "MP05",
        "BO3F" => "MP05",
        "BO6F" => "MP05",
        "LECR" => "MP05",
        "RBFM" => "MP12",
        "RB3M" => "MP12",
        "RB4M" => "MP12",
        "RB6M" => "MP12",
    ],
    'payment_modes' => [
        "MP01" => "C",
        "MP02" => "A",
        "MP03" => "A",
        "MP04" => "C",
        "MP05" => "B",
        "MP06" => "M",
        "MP07" => "O",
        "MP08" => "P",
        "MP09" => "R",
        "MP10" => "R",
        "MP11" => "R",
        "MP12" => "I",
        "MP13" => "O",
        "MP14" => "",
        "MP15" => "",
        "MP16" => "R",
        "MP17" => "R",
        "MP18" => "L",
        "MP19" => "S",
        "MP20" => "S",
        "MP21" => "S",
        "MP22" => "",
        "MP23" => "G"
    ],
];
