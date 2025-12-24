<?php

return [
    'providers' => [
        \PauloSanda\MultipleCepApi\Providers\ViaCepProvider::class,
        \PauloSanda\MultipleCepApi\Providers\BrasilApiProvider::class,
        \PauloSanda\MultipleCepApi\Providers\OpenCepProvider::class,
    ],

    'timeout' => 3,
    'retry_attempts' => 3,
];