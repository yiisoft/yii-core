<?php

$_ENV['TEST_RUNTIME_PATH'] = $_ENV['TEST_RUNTIME_PATH'] ?? dirname(__DIR__) . '/runtime';

return [
    'app' => [
        'id' => 'yii-build',
        'basePath' => __DIR__,
        'controllerNamespace' => 'yii\build\controllers',
    //    'enableCoreCommands' => false,
    ],
    'aliases' => [
        '@runtime'           => $_ENV['TEST_RUNTIME_PATH'],
    ],
];
