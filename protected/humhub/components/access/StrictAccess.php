<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 27.07.2017
 * Time: 23:25
 */

namespace humhub\components\access;

/**
 * StrictAccess should be used by all controllers which don't allow guest access if guest mode is inactive.
 * There are only some controllers which require guest access even if guest mode is active as Login, Registration etc.
 *
 * @package humhub\components\access
 */
class StrictAccess extends ControllerAccess
{
    public function getFixedRules()
    {
        $fixed = parent::getFixedRules();
        $fixed[] = [self::RULE_STRICT];
        return $fixed;
    }

}