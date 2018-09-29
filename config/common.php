<?php

use yii\di\Reference;

return [
    Psr\Container\ContainerInterface::class => Reference::to('container'),
    'container' => function ($container) {
        return $container;
    },

    yii\di\Factory::class => Reference::to('factory'),
    'factory' => [
        '__class' => yii\di\Factory::class,
        '__construct()' => [
            0 => [],
            1 => Reference::to('container'),
        ],
    ],

    yii\di\Injector::class => Reference::to('injector'),
    'injector' => [
        '__class' => yii\di\Injector::class,
    ],

    yii\base\Application::class => Reference::to('app'),
    'app' => [
        'id' => $params['app.id'],
        'name' => $params['app.name'],
        'language' => $params['app.language'],
        'params' => $params,
    ],

    Psr\Log\LoggerInterface::class => Reference::to('logger'),
    'logger' => [
    ],

    yii\base\Aliases::class => Reference::to('aliases'),
    'aliases' => array_merge($aliases, [
        '__class'   => yii\base\Aliases::class,
        '@root'     => YII_ROOT,
        '@vendor'   => '@root/vendor',
        '@public'   => '@root/public',
        '@runtime'  => '@root/runtime',
        '@bower'    => '@vendor/bower-asset',
        '@npm'      => '@vendor/npm-asset',
    ]),

    yii\base\ErrorHandler::class => Reference::to('errorHandler'),
    'errorHandler' => [
    ],

    yii\base\View::class => Reference::to('view'),
    'view' => [
    ],

    yii\base\Request::class => Reference::to('request'),
    'request' => [
    ],

    yii\base\Response::class => Reference::to('response'),
    'response' => [
    ],

    yii\profile\ProfilerInterface::class => Reference::to('profiler'),
    'profiler' => [
        '__class' => yii\profile\Profiler::class,
    ],

    'security' => [
        '__class' => yii\base\Security::class,
    ],

    yii\i18n\LocaleInterface::class => Reference::to('locale'),
    'locale' => [
        '__class' => yii\i18n\Locale::class,
        '__construct()' => [
            'localeString' => $params['i18n.locale'],
        ],
    ],
    'formatter' => [
        '__class' => yii\i18n\Formatter::class,
    ],
    'translation' => [
        '__class' => yii\i18n\Translation::class,
        'translations' => [
            '*' => [
                '__class' => yii\i18n\PhpMessageSource::class,
            ],
        ],
    ],
    'i18n' => [
        '__class' => yii\i18n\I18N::class,
        '__construct()' => [
            'locale' => Reference::to('locale'),
            'encoding' => $params['i18n.locale'],
            'timezone' => $params['i18n.timezone'],
        ],
    ],

    'mutex' => [
        '__class' => yii\mutex\FileMutex::class
    ],

];
