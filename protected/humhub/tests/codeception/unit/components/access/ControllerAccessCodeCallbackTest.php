<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\access;

use humhub\components\access\AccessValidator;
use humhub\components\access\ControllerAccess;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidConfigException;

/**
 * The codeCallback mechanism was removed in 1.19 (replaced by user gates,
 * see docs/develop/user-gates.md). A legacy validator still declaring and
 * setting a codeCallback must fail loudly instead of being silently ignored.
 */
class ControllerAccessCodeCallbackTest extends HumHubDbTestCase
{
    public function testLegacyCodeCallbackValidatorThrows()
    {
        $access = new class (['action' => 'test']) extends ControllerAccess {
            public function init()
            {
                parent::init();
                $this->registerValidator(LegacyCodeCallbackValidator::class);
            }
        };
        $access->setRules([['legacyCallback']]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches('/codeCallback/');

        $access->run();
    }
}

class LegacyCodeCallbackValidator extends AccessValidator
{
    public $name = 'legacyCallback';

    /**
     * Legacy pattern: validator re-declares the (removed) property and expects
     * the core to invoke the callback after failed validation.
     */
    public $codeCallback = 'someEnforcementMethod';

    public function run()
    {
        return false;
    }
}
