<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\components\bootstrap\ModuleAutoLoader;
use Yii;

/**
 * Class ModuleAutoLoaderTest
 */
class ModuleAutoLoaderTest extends Unit
{
    /** @var array list of expected core modules */
    public const EXPECTED_CORE_MODULES = [
        'humhub\modules\activity\Module' => 'activity',
        'humhub\modules\admin\Module' => 'admin',
        'humhub\modules\comment\Module' => 'comment',
        'humhub\modules\content\Module' => 'content',
        'humhub\modules\dashboard\Module' => 'dashboard',
        'humhub\modules\file\Module' => 'file',
        'humhub\modules\friendship\Module' => 'friendship',
        'humhub\modules\installer\Module' => 'installer',
        'humhub\modules\ldap\Module' => 'ldap',
        'humhub\modules\like\Module' => 'like',
        'humhub\modules\live\Module' => 'live',
        'humhub\modules\marketplace\Module' => 'marketplace',
        'humhub\modules\notification\Module' => 'notification',
        'humhub\modules\post\Module' => 'post',
        'humhub\modules\queue\Module' => 'queue',
        'humhub\modules\space\Module' => 'space',
        'humhub\modules\stream\Module' => 'stream',
        'humhub\modules\topic\Module' => 'topic',
        'humhub\modules\tour\Module' => 'tour',
        'humhub\modules\ui\Module' => 'ui',
        'humhub\modules\user\Module' => 'user',
        'humhub\modules\web\Module' => 'web',
    ];

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Assert that locateModules find all core modules
     */
    public function testCoreModuleLoading()
    {
        $modules = array_column(
            ModuleAutoLoader::locateModules(),
            'id',
        );

        /* assert that every core module is found by module loader. expected result of array_diff is an empty array. */
        $this->assertEmpty(array_diff(self::EXPECTED_CORE_MODULES, $modules), 'expected core modules are not resolved by auto loader.');
    }

    /**
     * Test that an invalid path for module loading leads to an exception
     */
    public function testInvalidModulePath()
    {
        array_push(Yii::$app->params['moduleAutoloadPaths'], '/dev/null');

        try {
            ModuleAutoLoader::locateModules();
            $this->fail('no expection when invalid path for moduleAutoloadPaths');
        } catch (\ErrorException $e) {
        }

        array_pop(Yii::$app->params['moduleAutoloadPaths']);
    }


    /**
     * Return test cases for module loading by path
     * @return array
     */
    public function dataModuleLoadingByPath()
    {
        return [
            ['@humhub/modules', count(self::EXPECTED_CORE_MODULES)],
            ['@humhub/invalid', 0],
            ['@invalid/folder', 0],
        ];
    }
}
