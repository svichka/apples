<?php

use yii\db\Migration;

/**
 * Class m190929_170130_apple
 */
class m190929_170130_apple extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->createTable('{{%apple}}', [
			'id' => $this->primaryKey(),
			'color' => $this->string()->notNull(),
			'created_at' => $this->integer()->notNull(),
			'fallen_at' => $this->integer()->notNull()->defaultValue(0),
			'size' => $this->float()->notNull()->defaultValue(1),
		]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropTable('{{%apple}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190929_170130_apple cannot be reverted.\n";

        return false;
    }
    */
}
