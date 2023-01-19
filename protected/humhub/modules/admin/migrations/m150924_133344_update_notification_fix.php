<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/licences
 */

use humhub\components\Migration;

class m150924_133344_update_notification_fix extends Migration
{
    public function up()
    {
        $this->update('notification', ['class' => 'humhub\modules\admin\notifications\NewVersionAvailable'], ['class' => 'HumHubUpdateNotification']);
    }

    public function down()
    {
        echo "m150924_133344_update_notification_fix cannot be reverted.\n";

        return false;
    }
}
