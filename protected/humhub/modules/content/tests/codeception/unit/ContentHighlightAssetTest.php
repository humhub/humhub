<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\assets\ContentHighlightAsset;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * @since 1.20
 */
class ContentHighlightAssetTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();
        $this->closeSession();
    }

    protected function _after()
    {
        $this->closeSession();
        parent::_after();
    }

    private function closeSession(): void
    {
        $session = Yii::$app->session;
        if ($session->getIsActive()) {
            $session->close();
        }
        unset($_COOKIE[$session->getName()]);
    }

    public function testNoSessionIsStartedWithoutSessionId()
    {
        $this->assertFalse(Yii::$app->session->getIsActive());
        $this->assertFalse(Yii::$app->session->getHasSessionId());

        new ContentHighlightAsset();

        $this->assertFalse(Yii::$app->session->getIsActive());
    }

    public function testKeywordIsReadAndRemovedFromActiveSession()
    {
        Yii::$app->session->open();
        Yii::$app->session->set('contentHighlight', 'needle');

        new ContentHighlightAsset();

        $this->assertNull(Yii::$app->session->get('contentHighlight'));
    }
}
