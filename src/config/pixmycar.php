<?php

return [

    'api' => [
        "url" => "https://citadelle-ws.pixmycar.com/4DWSDL/",
        "pseudo" => env('PIXMYCAR_PSEUDO'),
        "password" => env('PIXMYCAR_PASSWORD'),
    ],

    'model_vehicule' => [
        'class' => \App\Models\Vehicule\Vehicule::class,
        'identifiant' => 'immatriculation',
    ],

];
