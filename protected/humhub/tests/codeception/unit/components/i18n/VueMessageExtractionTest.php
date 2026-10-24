<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\tests\codeception\unit\components\i18n;

use humhub\commands\MessageController;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class VueMessageExtractionTest extends HumHubDbTestCase
{
    public function testExtractsMessagesFromVueFiles()
    {
        $file = Yii::getAlias('@runtime') . '/VueExtractionTest.vue';
        file_put_contents($file, <<<'VUE'
<template>
    <span>{{ label }}</span>
</template>
<script>
import { i18n } from '@humhub/vue';
export default {
    computed: {
        label() {
            return i18n.t('TestModule.base', 'Hello Vue');
        },
    },
};
</script>
VUE);

        $controller = new class ('message', Yii::$app) extends MessageController {
            public function extractFromFile($fileName)
            {
                return $this->extractMessages($fileName, ['Yii::t']);
            }
        };

        $messages = $controller->extractFromFile($file);
        unlink($file);

        $this->assertSame(['Hello Vue'], $messages['TestModule.base']);
    }

    public function testFileDiscoveryIncludesVueFiles()
    {
        $config = require Yii::getAlias('@humhub/config/i18n.php');

        $this->assertContains('*.vue', $config['only']);
    }
}
