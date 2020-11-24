<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "stores".
 *
 * @property int $id
 * @property string $title
 * @property int $tenant_id
 *
 * @property Stations[] $stations
 * @property Tenants $tenant
 */
class Stores extends \yii\db\ActiveRecord implements HasOpenHoursInterface
{
    use HasOpenHoursTrait;

    public function hasParent(): bool
    {
        return true;
    }

    public function getParentType(): ?string
    {
        return 'Tenants';
    }

    public function getParentId(): ?int
    {
        return $this->tenant_id;
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'tenant_id'], 'required'],
            [['title'], 'string'],
            [['tenant_id'], 'integer'],
            [['tenant_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenants::className(), 'targetAttribute' => ['tenant_id' => 'id']],
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
            'tenant_id' => 'Tenant ID',
        ];
    }

    /**
     * Gets query for [[Stations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStations()
    {
        return $this->hasMany(Stations::className(), ['store_id' => 'id']);
    }

    /**
     * Gets query for [[Tenant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTenant()
    {
        return $this->hasOne(Tenants::className(), ['id' => 'tenant_id']);
    }
}
