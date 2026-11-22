<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\tests\codeception\unit;

use humhub\modules\dav\components\Mount;
use humhub\modules\dav\events\MountsEvent;
use humhub\modules\dav\Module;
use humhub\modules\dav\widgets\DavSection;
use humhub\modules\oauth\Module as OAuthModule;
use humhub\modules\oauth\permissions\CreatePersonalAccessToken;
use Sabre\DAV\SimpleCollection;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * The address of the endpoint in "Devices & Access": for those who may create a token, when
 * a module offers a mount.
 */
class DavSectionTest extends HumHubDbTestCase
{
    private const USER1_GROUP = 2;

    protected function _before()
    {
        parent::_before();
        $this->becomeUser('User1');
        OAuthModule::instance()->settings->set('allowPersonalAccessTokens', true);
        self::setGroupPermission(self::USER1_GROUP, new CreatePersonalAccessToken());
        Event::on(Module::class, Module::EVENT_COLLECT_MOUNTS, [$this, 'collectMounts']);
    }

    protected function _after()
    {
        Event::off(Module::class, Module::EVENT_COLLECT_MOUNTS, [$this, 'collectMounts']);
        parent::_after();
    }

    public function collectMounts(MountsEvent $event): void
    {
        $event->mounts[] = new Mount('test', 'test:read', 'test:write', fn() => new SimpleCollection('test'));
    }

    public function testItShowsTheAddressAndTheUsername()
    {
        $html = DavSection::widget();

        $this->assertStringContainsString('/dav/', $html);
        $this->assertStringContainsString('User1', $html);
    }

    public function testItIsHiddenWithoutAMount()
    {
        // Every handler, also those of the modules of the test installation.
        Event::off(Module::class, Module::EVENT_COLLECT_MOUNTS);

        $this->assertSame('', DavSection::widget());
    }

    public function testItIsHiddenForThoseWhoMayCreateNoToken()
    {
        OAuthModule::instance()->settings->set('allowPersonalAccessTokens', false);

        $this->assertSame('', DavSection::widget());
    }
}
