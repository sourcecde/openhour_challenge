<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%open_hours}}`.
 */
class m200811_001640_create_open_hours_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%open_hours}}', [
            'id' => $this->primaryKey(),
            'entity_id' => $this->integer()->notNull(),
            'entity_type' => $this->string(16)->notNull(),
            'week_day' => $this->string(4)->notNull(),
            'from' => $this->time()->notNull(),
            'to' => $this->time()->notNull(),
        ]);

        $this->createIndex('idx-open_hours-entity_id-entity_type-week_day-from-to', 'open_hours', ['entity_id', 'entity_type', 'week_day', 'from', 'to'],true);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%open_hours}}');
    }
}
