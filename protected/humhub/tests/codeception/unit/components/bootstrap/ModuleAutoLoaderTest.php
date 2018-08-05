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
    const EXPECTED_CORE_MODULES = [
        'activity',
        'admin',
        'comment',
        'content',
        'dashboard',
        'directory',
        'file',
        'friendship',
        'installer',
        'like',
        'live',
        'notification',
        'post',
        'queue',
        'search',
        'space',
        'stream',
        'topic',
        'tour',
        'ui',
        'user',
    ];

    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCoreModuleLoading()
    {
        $modules = array_column(
            ModuleAutoLoader::locateModules(),
            'id'
        );

        /* assert that every core module is found by module loader. expected result of array_diff is an empty array. */
        $this->assertEmpty(
            array_diff(self::EXPECTED_CORE_MODULES, $modules),
            'expected core modules are not resolved by auto loader.'
        );
    }

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
}
