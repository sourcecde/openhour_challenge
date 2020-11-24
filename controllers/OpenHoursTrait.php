<?php

namespace app\controllers;

use app\models\Exceptions;
use app\models\OpenHours;

trait OpenHoursTrait
{
    /**
     * Create an open hour for an entity (station, store, tenant)
     *
     * @param $entity_id
     * @return OpenHours|array
     */
    public function actionAddOpenHour($entity_id)
    {
        $model = new OpenHours();

        $post_data = \Yii::$app->request->post();

        foreach ($model->attributes() as $attr) {
            if (isset($post_data[$attr])) {
                $model->{$attr} = $post_data[$attr];
            }
        }

        $model->entity_id = $entity_id;
        $model->entity_type = $this->getEntityType();

        if ($model->validate() && $model->save()) {
            return $model;
        }

        \Yii::$app->response->setStatusCode(422);
        return $model->errors;
    }

    /**
     * Removes an open hour from an entity (station, store, tenant)
     *
     * @param $entity_id
     * @param $id
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRemoveOpenHour($entity_id, $id)
    {
        if (!$model = OpenHours::findOne(['id' => $id, 'entity_id' => $entity_id])) {
            \Yii::$app->response->setStatusCode(404);
            return 'not found';
        }

        $model->delete();
        return 'ok';
    }

    /**
     * Updates 'week_day', 'from' or 'to' of an open hour
     *
     * @param $entity_id
     * @param $id
     * @return OpenHours|array|string|null
     */
    public function actionUpdateOpenHour($entity_id, $id)
    {
        if (!$model = OpenHours::findOne(['id' => $id, 'entity_id' => $entity_id, 'entity_type' => $this->getEntityType()])) {
            \Yii::$app->response->setStatusCode(404);
            return 'not found';
        }

        $post_data = \Yii::$app->request->post();

        foreach (['week_day', 'from', 'to'] as $attr) {
            if (isset($post_data[$attr])) {
                $model->{$attr} = $post_data[$attr];
            }
        }

        if ($model->validate() && $model->save()) {
            return $model;
        }

        \Yii::$app->response->setStatusCode(422);
        return $model->errors;
    }

    /**
     * Returns all open hours of an entity (station, store, tenant)
     *
     * @param $entity_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGetOpenHours($entity_id)
    {
        return OpenHours::find()->where(['entity_id' => $entity_id])->select(['id', 'week_day', 'from', 'to'])->all();
    }

    /**
     * Create an exception hour for an entity (station, store, tenant)
     *
     * @param $entity_id
     * @return Exceptions|array
     */
    public function actionAddException($entity_id)
    {
        $model = new Exceptions();

        $post_data = \Yii::$app->request->post();

        foreach ($model->attributes() as $attr) {
            if (isset($post_data[$attr])) {
                $model->{$attr} = $post_data[$attr];
            }
        }

        $model->entity_id = $entity_id;
        $model->entity_type = $this->getEntityType();

        if ($model->validate() && $model->save()) {
            return $model;
        }

        \Yii::$app->response->setStatusCode(422);
        return $model->errors;
    }

    /**
     * Removes an exception from an entity (station, store, tenant)
     *
     * @param $entity_id
     * @param $id
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRemoveException($entity_id, $id)
    {
        if (!$model = Exceptions::findOne(['id' => $id, 'entity_id' => $entity_id])) {
            \Yii::$app->response->setStatusCode(404);
            return 'not found';
        }

        $model->delete();
        return 'ok';
    }

    /**
     * Updates 'from', 'to', 'reason' or 'is_open' of an exception
     *
     * @param $entity_id
     * @param $id
     * @return Exceptions|array|string|null
     */
    public function actionUpdateException($entity_id, $id)
    {

        if (!$model = Exceptions::findOne(['id' => $id, 'entity_id' => $entity_id, 'entity_type' => $this->getEntityType()])) {
            \Yii::$app->response->setStatusCode(404);
            return 'not found';
        }

        $post_data = \Yii::$app->request->post();

        foreach (['from', 'to', 'reason', 'is_open'] as $attr) {
            if (isset($post_data[$attr])) {
                $model->{$attr} = $post_data[$attr];
            }
        }

        if ($model->validate() && $model->save()) {
            return $model;
        }

        \Yii::$app->response->setStatusCode(422);
        return $model->errors;
    }

    /**
     * Returns all exception hours of an entity (station, store, tenant)
     *
     * @param $entity_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGetExceptions($entity_id)
    {
        return Exceptions::find()->where(['entity_id' => $entity_id])->select(['id', 'from', 'to', 'reason', 'is_open'])->all();
    }

    /**
     * @return string|null
     */
    private function getEntityType()
    {
        try {
            return (new \ReflectionClass($this->modelClass))->getShortName();
        } catch (\Exception $e) {
            return null;
        }
    }
}
