<?php

use \app\models\Stations;
use \app\models\Stores;
use \app\models\Tenants;
use \app\models\OpenHours;
use \app\models\Exceptions;

define("UNKNOWN_MSG", 'unknown! the state will not change in next 365 days');

class NextStateChangeCest
{

    public $tenant;
    public $store;
    public $station;
    public $datetime;

    public function _before()
    {
        $this->datetime = new DateTime('2020-01-01 12:00:00', new DateTimeZone('UTC'));
        $this->tenant = new Tenants(['title' => 'Test Tenant']);
        $this->tenant->save();
        $this->store = new Stores(['title' => 'Test Store', 'tenant_id' => $this->tenant->id]);
        $this->store->save();
        $this->station = new Stations(['title' => 'Test Station', 'store_id' => $this->store->id]);
        $this->station->save();
    }

    public function _after()
    {
        // Cleaning db
        $db = \Yii::$app->db;
        $db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        foreach (['stations', 'stores', 'tenants', 'open_hours', 'exceptions'] as $table) {
            $db->createCommand('TRUNCATE TABLE `' . $table . '`')->execute();
        }
        $db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
    }

    public function NextStateChangeTests(ApiTester $I)
    {
        // At first there is no open hour or exceptions so it should return unknown!
        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => UNKNOWN_MSG]);

        // Now we add a open hour in tenant level that cover current time, the result should be 2020-01-01 14:00:00
        $tenant_level_open_hour = new OpenHours([
            'entity_type' => 'Tenants',
            'entity_id' => $this->tenant->id,
            'week_day' => $this->datetime->format('D'),
            'from' => '10:00:00',
            'to' => '14:00:00'
        ]);
        $tenant_level_open_hour->save();

        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => '2020-01-01 14:00:00']);

        // Now we add a open hour in store level that overrides the tenant level open hour and doesn't
        // cover current time, the result should be 2020-01-01 12:25:00
        $store_level_open_hour = new OpenHours([
            'entity_type' => 'Stores',
            'entity_id' => $this->store->id,
            'week_day' => $this->datetime->format('D'),
            'from' => '12:25:00',
            'to' => '14:00:00'
        ]);
        $store_level_open_hour->save();

        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => '2020-01-01 12:25:00']);

        // Now we update the from and to and set those to a time before querying date
        // Then we should get time of next week 2020-01-08 10:10:10 because
        // we have no other open hour in the week

        $store_level_open_hour->from = '10:10:10';
        $store_level_open_hour->to = '10:10:11';
        $store_level_open_hour->save();

        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => '2020-01-08 10:10:10']);

        // Now we add a open hour in station level that cover current time and overrides the store level open hour
        // the result should be 2020-01-01 15:00:00
        $station_level_open_hour = new OpenHours([
            'entity_type' => 'Stations',
            'entity_id' => $this->station->id,
            'week_day' => $this->datetime->format('D'),
            'from' => '08:00:00',
            'to' => '15:00:00'
        ]);


        $station_level_open_hour->save();
        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => '2020-01-01 15:00:00']);

        // Add an open exception to extend the open time to 16:00:00
        $tenant_level_exception = new Exceptions([
            'entity_type' => 'Tenants',
            'entity_id' => $this->tenant->id,
            'from' => $this->datetime->format('Y-m-d') . ' 11:45:00',
            'to' => $this->datetime->format('Y-m-d') . ' 16:00:00',
            'is_open' => 1
        ]);

        $tenant_level_exception->save();

        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => '2020-01-01 16:00:00']);

        // Add an overlapping close exception to decrease the open time to 12:40:00
        $store_level_exception = new Exceptions([
            'entity_type' => 'Stores',
            'entity_id' => $this->store->id,
            'from' => $this->datetime->format('Y-m-d') . ' 12:40:00',
            'to' => $this->datetime->format('Y-m-d') . ' 15:30:00',
            'is_open' => 0
        ]);

        $store_level_exception->save();

        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => '2020-01-01 12:40:00']);

        // Add another overlaping close exception to set current time to closed. It will be opened at 15:30:00
        $station_level_exception = new Exceptions([
            'entity_type' => 'Stations',
            'entity_id' => $this->station->id,
            'from' => $this->datetime->format('Y-m-d') . ' 11:40:00',
            'to' => $this->datetime->format('Y-m-d') . ' 12:45:00',
            'is_open' => 0
        ]);

        $station_level_exception->save();

        $I->sendGET("/stations/{$this->station->id}/next-state-change?time={$this->datetime->getTimestamp()}");
        $I->seeResponseContainsJson(['next_state_change' => '2020-01-01 15:30:00']);
    }

}
