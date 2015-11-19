<?php

namespace tests\codeception\unit\models;

use Yii;
use yii\codeception\DbTestCase;
use Codeception\Specify;
use humhub\models\Setting;

/**
 * SettingTest
 *
 * @package humhub.tests.unit.models
 * @since 0.9
 * @group core
 * 
 * @author luke
 */
class SettingTest extends DbTestCase
{

    use Specify;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'setting' => [ 'class' => \tests\codeception\fixtures\SettingFixture::className()],
        ];
    }

    //public $fixtures = array(':setting');

    public function testSetGet()
    {

        $this->assertTrue((Setting::Get('theme') == 'HumHub'));
        // Without Module
        $this->assertTrue((Setting::Get('theme') == 'HumHub'));

        Setting::Set('theme', 'HumHub2');
        $this->assertTrue((Setting::Get('theme') == 'HumHub2'));
        $this->assertTrue((Setting::Get('theme', '') == 'HumHub2'));

        // Module
        $this->assertFalse((Setting::Get('expireTime') == '3600'));
        $this->assertTrue((Setting::Get('expireTime', 'cache') == '3600'));

        Setting::Set('expireTime', '3601', 'cache');
        $this->assertTrue((Setting::Get('expireTime', 'cache') == '3601'));

        // Create
        Setting::Set('newSetting', 'newValue');
        $this->assertTrue((Setting::Get('newSetting') == 'newValue'));

        Setting::Set('newSetting', 'newValue2', 'someModuleId');
        $this->assertTrue((Setting::Get('newSetting', 'someModuleId') == 'newValue2'));
    }

    public function testTextSettings()
    {
        $longText = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";
        Setting::SetText('longText', $longText);
        Setting::SetText('longText', $longText . "2", 'testModule');

        $this->assertEquals(Setting::GetText('longText'), $longText);
        $this->assertEquals(Setting::GetText('longText', 'testModule'), $longText . "2");
    }

    public function testInstalled()
    {
        $this->assertTrue(Setting::IsInstalled());
    }

}
