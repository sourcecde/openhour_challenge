<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tenants}}`.
 */
class m200811_001550_create_tenants_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tenants}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tenants}}');
    }
}
