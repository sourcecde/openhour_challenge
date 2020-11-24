<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tenants".
 *
 * @property int $id
 * @property string $title
 *
 * @property Stores[] $stores
 */
class Tenants extends \yii\db\ActiveRecord implements HasOpenHoursInterface
{
    use HasOpenHoursTrait;

    public function hasParent(): bool
    {
        return false;
    }

    public function getParentType(): ?string
    {
        return null;
    }

    public function getParentId(): ?int
    {
        return null;
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenants';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string'],
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
        ];
    }

    /**
     * Gets query for [[Stores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStores()
    {
        return $this->hasMany(Stores::className(), ['tenant_id' => 'id']);
    }
}
