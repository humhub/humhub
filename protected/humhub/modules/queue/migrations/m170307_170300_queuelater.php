<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\db\Migration;

class m170307_170300_queuelater extends Migration
{
    public $tableName = '{{%queue}}';

    public function up()
    {
        $this->addColumn($this->tableName, 'timeout', $this->integer()->defaultValue(0)->notNull()->after('created_at'));
    }

    public function down()
    {
        echo "m170307_170300_queuelater cannot be reverted.\n";

        return false;
    }
}
