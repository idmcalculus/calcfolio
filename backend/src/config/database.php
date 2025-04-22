<?php

return [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/../../' . $_ENV['DB_PATH'],
    'prefix' => '',
];
