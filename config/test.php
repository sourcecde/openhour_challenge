<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/test_db.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'language' => 'en-US',
    'components' => [
        'db' => $db,
        'mailer' => [
            'useFileTransport' => true,
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
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
        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            // but if you absolutely need it set cookie domain to localhost
            /*
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
        ],
    ],
    'params' => $params,
];
