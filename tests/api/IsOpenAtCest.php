<?php

use \app\models\Stations;
use \app\models\Stores;
use \app\models\Tenants;
use \app\models\OpenHours;
use \app\models\Exceptions;

class IsOpenAtCest
{

    public $tenant;
    public $store;
    public $station;
    public $datetime;

    public function _before(ApiTester $I)
    {
        $this->datetime = new DateTime('2020-01-01 12:00:00', new DateTimeZone('UTC'));
        $this->tenant = new Tenants(['title' => 'Test Tenant']);
        $this->tenant->save();
        $this->store = new Stores(['title' => 'Test Store', 'tenant_id' => $this->tenant->id]);
        $this->store->save();
        $this->station = new Stations(['title' => 'Test Station', 'store_id' => $this->store->id]);
        $this->station->save();
    }


    public function _after(ApiTester $I)
    {
        // Cleaning db
        $db = \Yii::$app->db;
        $db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        foreach (['stations', 'stores', 'tenants', 'open_hours', 'exceptions'] as $table) {
            $db->createCommand('TRUNCATE TABLE `' . $table . '`')->execute();
        }
        $db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
    }

    public function IsOpenAtTests(ApiTester $I)
    {
        // At first there is no open hour nor exceptions so it should return false
        $I->sendGET("/stations/{$this->station->id}/is-open-at?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['is_open' => false]);

        // Now we add a open hour in tenant level, the result should be is_open => true
        $tenant_level_open_hour = new OpenHours([
            'entity_type' => 'Tenants',
            'entity_id' => $this->tenant->id,
            'week_day' => $this->datetime->format('D'),
            'from' => '10:00:00',
            'to' => '14:00:00'
        ]);
        $tenant_level_open_hour->save();
        $I->sendGET("/stations/{$this->station->id}/is-open-at?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['is_open' => true]);

        // Check if store level open hour overrides the tenant level open hour, the result should be is_open => false
        $store_level_open_hour = new OpenHours([
            'entity_type' => 'Stores',
            'entity_id' => $this->store->id,
            'week_day' => $this->datetime->format('D'),
            'from' => '10:00:00',
            'to' => '11:00:00']);

        $store_level_open_hour->save();
        $I->sendGET("/stations/{$this->station->id}/is-open-at?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['is_open' => false]);

        // Check if station level open hour overrides the store level open hour, the result should be is_open => true
        $station_level_open_hour = new OpenHours([
            'entity_type' => 'Stations',
            'entity_id' => $this->station->id,
            'week_day' => $this->datetime->format('D'),
            'from' => '11:30:00',
            'to' => '12:30:00'
        ]);

        $station_level_open_hour->save();
        $I->sendGET("/stations/{$this->station->id}/is-open-at?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['is_open' => true]);


        // Check if tenant level exception overrides the store level open hour, the result should be is_open => false
        $tenant_level_exception = new Exceptions([
            'entity_type' => 'Tenants',
            'entity_id' => $this->tenant->id,
            'from' => $this->datetime->format('Y-m-d') . ' 11:45:00',
            'to' => $this->datetime->format('Y-m-d') . ' 12:15:00',
            'is_open' => 0
        ]);

        $tenant_level_exception->save();
        $I->sendGET("/stations/{$this->station->id}/is-open-at?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['is_open' => false]);

        // Check if store level exception overrides the tenant level exception, the result should be is_open => true
        $store_level_exception = new Exceptions([
            'entity_type' => 'Stores',
            'entity_id' => $this->store->id,
            'from' => $this->datetime->format('Y-m-d') . ' 09:00:00',
            'to' => $this->datetime->format('Y-m-d') . ' 15:00:00',
            'is_open' => 1
        ]);

        $store_level_exception->save();
        $I->sendGET("/stations/{$this->station->id}/is-open-at?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['is_open' => true]);

        // Check if station level exception overrides the store level exception, the result should be is_open => false
        $station_level_exception = new Exceptions([
            'entity_type' => 'Stations',
            'entity_id' => $this->station->id,
            'from' => $this->datetime->format('Y-m-d') . ' 11:59:00',
            'to' => $this->datetime->format('Y-m-d') . ' 12:01:00',
            'is_open' => 0
        ]);

        $station_level_exception->save();
        $I->sendGET("/stations/{$this->station->id}/is-open-at?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['is_open' => false]);
    }


}
