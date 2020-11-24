<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "open_hours".
 *
 * @property int $id
 * @property int $entity_id
 * @property string $entity_type
 * @property string $week_day
 * @property string $from
 * @property string $to
 */
class OpenHours extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'open_hours';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['week_day', 'from', 'to'], 'validateOverlap'],
            [['from'], 'validateFrom'],
            ['week_day', 'validateWeekDay'],
            [['entity_id', 'entity_type', 'week_day', 'from', 'to'], 'required'],
            [['entity_id'], 'integer'],
            [['from', 'to'], 'safe'],
            [['entity_type'], 'string', 'max' => 16],
            [['week_day'], 'string', 'max' => 4],
            [['entity_id', 'entity_type', 'week_day', 'from', 'to'], 'unique', 'targetAttribute' => ['entity_id', 'entity_type', 'week_day', 'from', 'to']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_id' => 'Entity ID',
            'entity_type' => 'Entity Type',
            'week_day' => 'Week Day',
            'from' => 'From',
            'to' => 'To',
        ];
    }

    /**
     * @param $attribute
     */
    public function validateWeekDay($attribute)
    {
        $valid_items = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        if (!in_array($this->$attribute, $valid_items)) {
            $this->addError($attribute, 'can only be one of: ' . implode(', ', $valid_items));
        }
    }

    /**
     * Checks for from be < to
     *
     * @param $attribute
     */
    public function validateFrom($attribute)
    {
        if ($this->from >= $this->to) {
            $this->addError($attribute, 'from time must be before to');
        }
    }

    /**
     * Checks for overlaps (overlaps can not happen with same entity and same week day)
     * e.g. For an entity (store, tenant or station) we can not have "Thu 12-14" and "thu 13-15"
     * overlaps still can happen with parents (overlaping on store level with station level is OK)
     * @param $attribute
     */
    public function validateOverlap($attribute)
    {
        if ($overlaps = OpenHours::find()
            ->where(['entity_id' => $this->entity_id, 'entity_type' => $this->entity_type, 'week_day' => $this->week_day])
            ->andWhere("
            open_hours.from BETWEEN '$this->from' AND '$this->to' 
            OR open_hours.to BETWEEN '$this->from' AND '$this->to'
            OR '$this->from' BETWEEN open_hours.from AND open_hours.to
            OR '$this->to' BETWEEN open_hours.from AND open_hours.to
            ")
            ->all()) {
            foreach ($overlaps as $overlap) {
                if ($overlap->id === $this->id) {
                    continue;
                }
                $this->addError($attribute, "overlaps with id: $overlap->id, week_day: $overlap->week_day, from: $overlap->from, to: $overlap->to");
            }
        }
    }
}
