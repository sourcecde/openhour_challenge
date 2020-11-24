<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "exceptions".
 *
 * @property int $id
 * @property int $entity_id
 * @property string $entity_type
 * @property string $from
 * @property string $to
 * @property int $is_open
 * @property string|null $reason
 */
class Exceptions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'exceptions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'validateOverlap'],
            [['from'], 'validateFrom'],
            [['entity_id', 'entity_type', 'from', 'to', 'is_open'], 'required'],
            [['entity_id', 'is_open'], 'integer'],
            [['from', 'to'], 'safe'],
            [['reason'], 'string'],
            [['entity_type'], 'string', 'max' => 16],
            [['entity_id', 'entity_type', 'from', 'to'], 'unique', 'targetAttribute' => ['entity_id', 'entity_type', 'from', 'to']],
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
            'from' => 'From',
            'to' => 'To',
            'is_open' => 'Is Open',
            'reason' => 'Reason',
        ];
    }


    /**
     * @param $attribute
     */
    public function validateFrom($attribute)
    {
        if (strtotime($this->from) >= strtotime($this->to)) {
            $this->addError($attribute, 'from time must be before to');
        }
    }

    /**
     * Checks for overlaps (overlaps still can happen with parents (overlaping on store level with station level is OK))
     * @param $attribute
     */
    public function validateOverlap($attribute)
    {
        $from = date("Y-m-d H:i:s", strtotime($this->from));
        $to = date("Y-m-d H:i:s", strtotime($this->to));

        if ($overlaps = Exceptions::find()
            ->where(['entity_id' => $this->entity_id, 'entity_type' => $this->entity_type])
            ->andWhere("
            exceptions.from BETWEEN '$from' AND '$to' 
            OR exceptions.to BETWEEN '$from' AND '$to'
            OR '$from' BETWEEN exceptions.from AND exceptions.to
            OR '$to' BETWEEN exceptions.from AND exceptions.to
            ")
            ->all()) {
            foreach ($overlaps as $overlap) {
                if ($overlap->id === $this->id) {
                    continue;
                }
                $this->addError($attribute, "overlaps with id: $overlap->id, from: $overlap->from, to: $overlap->to, reason: $overlap->reason");
            }
        }
    }
}
