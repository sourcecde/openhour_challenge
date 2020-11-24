<?php

namespace app\controllers;

class TenantsController extends BaseRestController
{
    use OpenHoursTrait;

    public $modelClass = 'app\models\Tenants';
}
