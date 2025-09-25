<?php

use humhub\models\UrlOembed;
use yii\db\Migration;

/**
 * Class m201217_071828_fix_oembed_setting
 */
class m201217_081828_fix_oembed_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Fix ONLY sample OEmbed endpoints if their URLs still have the HTTP protocol instead of HTTPS
        $sampleProviderPrefixes = [
            'vimeo.com',
            'youtube.com',
            'youtu.be',
            'soundcloud.com',
            'slideshare.net',
        ];
        $updateProviders = false;
        $providers = UrlOembed::getProviders();
        foreach ($providers as $providerPrefix => $providerUrl) {
            if (in_array($providerPrefix, $sampleProviderPrefixes)
                && stripos($providerUrl, 'http://') === 0) {
                $providers[$providerPrefix] = 'https' . substr($providerUrl, 4);
                $updateProviders = true;
            }
        }
        if ($updateProviders) {
            UrlOembed::setProviders($providers);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201217_071828_fix_oembed_setting cannot be reverted.\n";

        return false;
    }
}
