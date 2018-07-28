<?php

$_ENV['TEST_RUNTIME_PATH'] = $_ENV['TEST_RUNTIME_PATH'] ?? dirname(__DIR__) . '/runtime';

return [
    'app' => [
        'id' => 'testapp',
        'aliases' => [
            '@runtime'           => $_ENV['TEST_RUNTIME_PATH'],
            '@yii/tests/runtime' => $_ENV['TEST_RUNTIME_PATH'],
        ],
    ],
];
