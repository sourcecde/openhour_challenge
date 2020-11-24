<?php

use yii\db\Migration;

/**
 * Class m200814_170506_fix_indexes
 */
class m200814_170506_fix_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx-exceptions-entity_id-entity_type-week_day-from-to', 'open_hours');
        $this->createIndex('idx-exceptions-entity_id-entity_type-from-to', 'exceptions', ['entity_id', 'entity_type', 'from', 'to'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200814_170506_fix_indexes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200814_170506_fix_indexes cannot be reverted.\n";

        return false;
    }
    */
}
