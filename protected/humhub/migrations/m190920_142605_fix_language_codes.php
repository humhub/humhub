<?php

use humhub\modules\file\libs\FileHelper;
use yii\db\Migration;

/**
 * Class m190920_142605_fix_language_codes
 */
class m190920_142605_fix_language_codes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $changedCodes = [
            'en' => 'en-US',
            'en_gb' => 'en-GB',
            'pt_br' => 'pt-BR',
            'nb_no' => 'nb-NO',
            'nn_no' => 'nn-NO',
            'zh_cn' => 'zh-CN',
            'zh_tw' => 'zh-TW',
            'fa_ir' => 'fa-IR'
        ];

        foreach ($changedCodes as $old => $new) {
            $this->update('user', ['language' => $new], ['language' => $old]);
            $this->update('setting', ['value' => $new], ['value' => $old]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190920_142605_fix_language_codes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190920_142605_fix_language_codes cannot be reverted.\n";

        return false;
    }
    */
}
