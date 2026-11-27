<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\modules\file\validators\FileValidator;
use humhub\widgets\CoreJsConfig;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class CoreJsConfigTest extends HumHubDbTestCase
{
    public function testTheFileConfigCarriesTheSizeLimitOfUploads()
    {
        Yii::$app->getModule('file')->settings->set('maxFileSize', 1024 * 1024);

        CoreJsConfig::widget();

        $this->assertSame(1024 * 1024, $this->jsConfig()['file']['upload']['maxFileSize']);
        // The limit the upload is validated against
        $this->assertSame((int)(new FileValidator())->getSizeLimit(), $this->jsConfig()['file']['upload']['maxFileSize']);
    }

    /**
     * The config registered with the view, before it is written into the page
     */
    private function jsConfig(): array
    {
        return (fn() => $this->jsConfig)->call(Yii::$app->view);
    }
}
