<?php

use yii\db\Migration;

class m260701_111824_move_mobile_whitelisted_domains_to_urls extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $settingsManager = Yii::$app->settings;
        $domains = $settingsManager->get('whiteListedDomains');
        $domainsArray = array_filter(
            array_map(
                'trim',
                explode(',', (string)$domains),
            ),
            'strlen', // Remove empty values
        );
        $urls = [];
        foreach ($domainsArray as $domain) {
            // Add a wildcard to the domain to match any path
            $urls[] = (rtrim($domain, '/') . '/') . '*';
        }
        $settingsManager->setSerialized('whiteListedUrls', $urls);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260701_111824_move_mobile_whitelisted_domains_to_urls cannot be reverted.\n";

        return false;
    }
}
