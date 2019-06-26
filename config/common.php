<?php

use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Definitions\Reference;

return [
    'container' => function (\Psr\Container\ContainerInterface $container) {
        return $container;
    },

    /// TODO to be removed, use FactoryInterface
    Factory::class => Reference::to('factory'),

    FactoryInterface::class => Reference::to('factory'),
    'factory' => [
        '__class' => Factory::class,
        '__construct()' => [
            'definitions' => [],
            'providers' => [],
            'parent' => Reference::to('container'),
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
        'bootstrap' => [],
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

    yii\i18n\Locale::class => Reference::to('locale'),
    'locale' => [
        '__class' => yii\i18n\Locale::class,
        '__construct()' => [
            'localeString' => $params['i18n.locale'],
        ],
    ],
    'formatter' => [
        '__class' => yii\i18n\Formatter::class,
    ],
    'translator' => [
        '__class' => yii\i18n\Translator::class,
        'translations' => [
            'yii' => [
                '__class' => yii\i18n\PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@yii/messages',
            ],
        ],
    ],

    yii\i18n\I18N::class => Reference::to('i18n'),
    'i18n' => [
        '__class' => yii\i18n\I18N::class,
        '__construct()' => [
            'encoding' => $params['i18n.encoding'],
            'timezone' => $params['i18n.timezone'],
            'locale' => Reference::to('locale'),
            'translator' => Reference::to('translator'),
        ],
    ],

    'mutex' => [
        '__class' => Yiisoft\Mutex\FileMutex::class
    ],

];
