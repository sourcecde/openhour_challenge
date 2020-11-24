<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '-sK8MhXx3BuKeyJLhNW59Yy9LeN2fdju',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Handle Options
                'OPTIONS <x1:[\w\-]+>' => 'site/ok',
                'OPTIONS <x1:[\w\-]+>/<x2:[\w\-]+>' => 'site/ok',
                'OPTIONS <x1:[\w\-]+>/<x2:[\w\-]+>/<x3:[\w\-]+>' => 'site/ok',
                'OPTIONS <x1:[\w\-]+>/<x2:[\w\-]+>/<x3:[\w\-]+>/<x4:[\w\-]+>/<x5:[\w\-]+>' => 'site/ok',

                // Home Page
                '' => 'site/index',

                // Open Hours
                'POST   /<controller:[\w\-]+>/<entity_id:\d+>/open-hours' => '<controller>/add-open-hour',
                'GET    /<controller:[\w\-]+>/<entity_id:\d+>/open-hours' => '<controller>/get-open-hours',
                'PUT    /<controller:[\w\-]+>/<entity_id:\d+>/open-hours/<id:\d+>' => '<controller>/update-open-hour',
                'DELETE /<controller:[\w\-]+>/<entity_id:\d+>/open-hours/<id:\d+>' => '<controller>/remove-open-hour',

                // Exceptions
                'POST   /<controller:[\w\-]+>/<entity_id:\d+>/exceptions' => '<controller>/add-exception',
                'GET    /<controller:[\w\-]+>/<entity_id:\d+>/exceptions' => '<controller>/get-exceptions',
                'PUT    /<controller:[\w\-]+>/<entity_id:\d+>/exceptions/<id:\d+>' => '<controller>/update-exception',
                'DELETE /<controller:[\w\-]+>/<entity_id:\d+>/exceptions/<id:\d+>' => '<controller>/remove-exception',

                // Tenants CRUD
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'tenants',
                    'pluralize' => false
                ],

                // Stores CRUD
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'stores',
                    'pluralize' => false
                ],

                // Stations CRUD
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'stations',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET <id:\d+>/is-open-at' => 'is-open-at',
                        'GET <id:\d+>/next-state-change' => 'next-state-change'
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
