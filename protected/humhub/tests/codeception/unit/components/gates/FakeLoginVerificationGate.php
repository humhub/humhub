<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\gates;

use humhub\components\gates\LoginVerificationGateInterface;

class FakeLoginVerificationGate extends FakeGate implements LoginVerificationGateInterface
{
}
