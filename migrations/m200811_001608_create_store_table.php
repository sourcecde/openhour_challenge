<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stores}}`.
 */
class m200811_001608_create_store_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stores}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
            'tenant_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_stores_tenant', 'stores', 'tenant_id', 'tenants', 'id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stores}}');
    }
}
