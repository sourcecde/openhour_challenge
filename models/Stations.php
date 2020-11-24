<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "stations".
 *
 * @property int $id
 * @property string $title
 * @property int $store_id
 *
 * @property Stores $store
 */
class Stations extends \yii\db\ActiveRecord implements HasOpenHoursInterface
{
    use HasOpenHoursTrait;

    public function hasParent(): bool
    {
        return true;
    }

    public function getParentType(): ?string
    {
        return 'Stores';
    }

    public function getParentId(): ?int
    {
        return $this->store_id;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'store_id'], 'required'],
            [['title'], 'string'],
            [['store_id'], 'integer'],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stores::className(), 'targetAttribute' => ['store_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'store_id' => 'Store ID',
        ];
    }

    /**
     * Gets query for [[Store]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(Stores::className(), ['id' => 'store_id']);
    }
}
