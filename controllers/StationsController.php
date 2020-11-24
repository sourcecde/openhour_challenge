<?php

namespace app\controllers;

use app\models\HasOpenHoursInterface;
use app\models\Stations;

class StationsController extends BaseRestController
{
    use OpenHoursTrait;

    public $modelClass = 'app\models\Stations';

    public function actions()
    {
        $actions = parent::actions();

        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['store_id' => null]))
                    ->addRule('store_id', 'integer');
            },
        ];

        return $actions;
    }

    /**
     * @param $id
     * @return array
     */
    public function actionIsOpenAt($id)
    {
        if (!$model = Stations::findOne(['id' => $id])) {
            \Yii::$app->response->setStatusCode(404);
            return ['msg' => 'station not found'];
        }

        if (!$datetime = $this->getRequestedDateTime()) {
            \Yii::$app->response->setStatusCode(400);
            return ['msg' => 'time is not provided or its invalid.'];
        }

        return ['datetime' => $datetime->format("Y-m-d H:i:s"), 'is_open' => $this->getOpenState($model, $datetime)["is_open"]];
    }

    /**
     * @param $id
     * @return array
     */
    public function actionNextStateChange($id)
    {

        if (!$model = Stations::findOne(['id' => $id])) {
            \Yii::$app->response->setStatusCode(404);
            return ['msg' => 'station not found'];
        }

        if (!$datetime = $this->getRequestedDateTime()) {
            \Yii::$app->response->setStatusCode(400);
            return ['msg' => 'time is not provided or its invalid.'];
        }

        // First it generates a timeline for next 365days including open hours and applied exceptions.
        // Overlapping exceptions are took care of.
        $station_flatten_timeline = $this->getFlattenTimeline($model, clone $datetime, 365);
        $is_currently_open = $this->getOpenState($model, $datetime)["is_open"];


        // Get the first opening in timeline and check if the state is changed
        while (count($station_flatten_timeline) > 0) {
            $timeline_item = array_shift($station_flatten_timeline);
            if ($is_currently_open && $datetime->getTimestamp() > $timeline_item['from'] && $datetime->getTimestamp() < $timeline_item['to']) {
                return ['next_state_change' => date('Y-m-d H:i:s', $timeline_item['to']), 'current_state' => 'open', 'next_state' => 'closed'];
            }
            if (!$is_currently_open && $datetime->getTimestamp() < $timeline_item['from']) {
                return ['next_state_change' => date('Y-m-d H:i:s', $timeline_item['from']), 'current_state' => 'closed', 'next_state' => 'open'];
            }
        }

        // State didn't changed in our timeline
        return [
            'next_state_change' => 'unknown! the state will not change in next 365 days',
            'current_state' => ($is_currently_open ? 'open' : 'closed'),
            'next_state' => (!$is_currently_open ? 'open' : 'closed'),
        ];
    }

    /**
     * Generates a timeline of open hours for a period of time.
     * Exception hours are also applied and overlaps are handled
     *
     * @param Stations $model
     * @param \DateTime $start
     * @param int $timeline_duration
     * @return array (e.g. [['from'=><timestamp> , 'to'=><timestamp>]...])
     */
    private function getFlattenTimeline(Stations $model, \DateTime $start, int $timeline_duration): array
    {
        $timeline = [];


        // First we generate timeline with our opening hours
        $week_open_hours = $this->getWeekOpenHours($model);
        while ($timeline_duration--) {
            if ($day_open_hours = $week_open_hours[$start->format('D')]) {
                foreach ($day_open_hours as $open_hour) {
                    $timeline[] = ['from' => strtotime($start->format('Y-m-d ' . $open_hour->from)), 'to' => strtotime($start->format('Y-m-d ' . $open_hour->to))];
                }
            }
            $start->modify('+1 day');
        }


        // Apply the exceptions from tenants down to stations
        list($station_exceptions, $store_exceptions, $tenant_exceptions) = $this->getAllStationTreeExceptionsAfter($model, $start);

        $timeline = $this->applyExceptionsToTimeline($timeline, $tenant_exceptions);
        $timeline = $this->applyExceptionsToTimeline($timeline, $store_exceptions);
        $timeline = $this->applyExceptionsToTimeline($timeline, $station_exceptions);

        return $timeline;
    }

    /**
     * Devides the exceptions into "Open Exceptions" and "Close Exceptions" then apply them to the timeline
     *
     * @param $timeline
     * @param $exceptions
     * @return array
     */
    private function applyExceptionsToTimeline($timeline, $exceptions)
    {
        $open_exceptions = $close_exceptions = [];
        foreach ($exceptions as $exception) {
            if ($exception->is_open) {
                $open_exceptions[] = ['from' => strtotime($exception->from), 'to' => strtotime($exception->to)];
            } else {
                $close_exceptions[] = ['from' => strtotime($exception->from), 'to' => strtotime($exception->to)];
            }
        }

        $timeline = $this->mergeOpenExceptionsWithTimeline($timeline, $open_exceptions);
        $timeline = $this->excludeCloseExceptionsFromTimeline($timeline, $close_exceptions);

        return $timeline;
    }

    /**
     * Merges timeline with $close_exceptions then inverts the timeline from "open hours timeline" to "closed hours timeline",
     * removes the overlaps and then invert it again to "open hours timeline"
     *
     * @param $timeline
     * @param $close_exceptions
     * @return array
     */
    private function excludeCloseExceptionsFromTimeline($timeline, $close_exceptions)
    {
        $close_timeline = [];
        for ($i = 0; $i < (count($timeline) + 1); $i++) {
            $new_from = isset($timeline[$i - 1]) ? $timeline[$i - 1]['to'] : '0';
            $new_to = isset($timeline[$i]) ? $timeline[$i]['from'] : PHP_INT_MAX;

            $close_timeline[] = ['from' => $new_from, 'to' => $new_to];
        }

        $close_timeline = array_merge($close_timeline, $close_exceptions);
        usort($close_timeline, function ($a, $b) {
            return $a['from'] <=> $b['from'];
        });


        $merged_close_timeline = $this->removeOverlapsFromSortedTimeline($close_timeline);
        $final_timeline = [];
        for ($i = 1; $i < count($merged_close_timeline); $i++) {
            $final_timeline[] = ['from' => $merged_close_timeline[$i - 1]['to'], 'to' => $merged_close_timeline[$i]['from']];
        }

        return $final_timeline;
    }

    /**
     * Simply merges the timeline with $open_exceptions then sorts it and remove overlaps
     *
     * @param $timeline
     * @param $open_exceptions
     * @return array
     */
    private function mergeOpenExceptionsWithTimeline($timeline, $open_exceptions)
    {
        $timeline = array_merge($timeline, $open_exceptions);
        usort($timeline, function ($a, $b) {
            return $a['from'] <=> $b['from'];
        });

        return $this->removeOverlapsFromSortedTimeline($timeline);
    }

    /**
     * Removes overlaps in timeline
     *
     * @param $timeline
     * @return array
     */
    private function removeOverlapsFromSortedTimeline($timeline)
    {
        $stack = [];

        while (count($timeline) > 0) {
            $current_item = array_shift($timeline);
            if ($last_item = array_pop($stack)) {
                if ($current_item['from'] <= $last_item['to']) {
                    $current_item['from'] = $last_item['from'];
                    $current_item['to'] = max($last_item['to'], $current_item['to']);
                    array_push($stack, $current_item);
                    continue;
                }
                array_push($stack, $last_item);
            }
            array_push($stack, $current_item);
        }

        return $stack;
    }

    /**
     * Returns all exceptions for a station that their from/to is after $start time
     *
     * @param Stations $station
     * @param \DateTime $start
     * @return array
     */
    private function getAllStationTreeExceptionsAfter(Stations $station, \DateTime $start): array
    {
        $station_exceptions = $station->getAllExceptionsFrom($start) ?? [];
        $store_exceptions = $tenant_exceptions = [];
        if ($store = $station->getParent()) {
            $store_exceptions = $store->getAllExceptionsFrom($start) ?? [];
            if ($tenant = $store->getParent()) {
                $tenant_exceptions = $tenant->getAllExceptionsFrom($start) ?? [];
            }
        }
        return [$station_exceptions, $store_exceptions, $tenant_exceptions];
    }

    /**
     * Returns the weekly schedule of open hours (without exceptions) of a station
     *
     * @param HasOpenHoursInterface $entity
     * @return array
     */
    private function getWeekOpenHours(HasOpenHoursInterface $entity): array
    {
        $result = [];
        foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day) {
            $result[$day] = $this->callMethodInTreeBottomUp('getWeekDayOpenHours', $entity, [$day]);
        }

        return $result;
    }

    /**
     * Retrives the time from request and creates a DateTime instance
     *
     * @return \DateTime|null
     */
    private function getRequestedDateTime(): ?\DateTime
    {
        if ($time = \Yii::$app->request->get('time')) {
            try {
                return new \DateTime("@$time", new \DateTimeZone('UTC'));
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;

    }

    /**
     * Returns the open status of a station in a specific time.
     * First check exceptions then weekly schedule
     *
     * @param HasOpenHoursInterface $entity
     * @param \DateTime $datetime
     * @return array
     */
    private function getOpenState(HasOpenHoursInterface $entity, \DateTime $datetime)
    {
        if ($exception = $this->callMethodInTreeBottomUp('getDateTimeException', $entity, [$datetime])) {
            return ['is_open' => !!$exception->is_open, 'type' => 'exception', 'object' => $exception];
        }

        if ($open_hours = $this->callMethodInTreeBottomUp('getWeekDayOpenHours', $entity, [$datetime->format('D')])) {
            foreach ($open_hours as $open_hour) {
                if (
                    $datetime->getTimestamp() >= strtotime($datetime->format('Y-m-d ' . $open_hour->from))
                    && $datetime->getTimestamp() <= strtotime($datetime->format('Y-m-d ' . $open_hour->to))
                ) {
                    return ['is_open' => true, 'type' => 'open_hour', 'object' => $open_hour];
                }
            }
        }

        return ['is_open' => false, 'type' => 'open_hour', 'object' => null];
    }

    /**
     * Calls a method of HasOpenHoursInterface objects if the result is null then calls
     * on the parent (store, tenant).
     *
     * @param string $method
     * @param HasOpenHoursInterface $entity
     * @param array $args
     * @return mixed|null
     */
    private function callMethodInTreeBottomUp(string $method, HasOpenHoursInterface $entity, array $args)
    {
        if (method_exists($entity, $method)) {
            if ($result = call_user_func_array(array($entity, $method), $args)) {
                return $result;
            }
            if ($parent = $entity->getParent()) {
                return $this->callMethodInTreeBottomUp($method, $parent, $args);
            }
        }
        return null;
    }

}
