<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stations}}`.
 */
class m200811_001627_create_stations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stations}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
            'store_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey('fk_stations_store', 'stations', 'store_id', 'stores', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stations}}');
    }
}
