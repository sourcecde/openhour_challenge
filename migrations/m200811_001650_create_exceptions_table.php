<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%exceptions}}`.
 */
class m200811_001650_create_exceptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%exceptions}}', [
            'id' => $this->primaryKey(),
            'entity_id' => $this->integer()->notNull(),
            'entity_type' => $this->string(16)->notNull(),
            'from' => $this->dateTime()->notNull(),
            'to' => $this->dateTime()->notNull(),
            'is_open' => $this->boolean()->notNull(),
            'reason' => $this->text()
        ]);

        $this->createIndex('idx-exceptions-entity_id-entity_type-week_day-from-to', 'open_hours', ['entity_id', 'entity_type', 'from', 'to'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%exceptions}}');
    }
}
