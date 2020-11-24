<?php

namespace app\controllers;

use yii\filters\Cors;
use yii\rest\ActiveController;


class BaseRestController extends ActiveController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];

        return $behaviors;
    }
}
