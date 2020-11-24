<?php

namespace app\controllers;

class StoresController extends BaseRestController
{
    use OpenHoursTrait;

    public $modelClass = 'app\models\Stores';

    public function actions()
    {
        $actions = parent::actions();

        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['tenant_id' => null]))
                    ->addRule('tenant_id', 'integer');
            },
        ];

        return $actions;
    }
}
