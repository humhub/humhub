<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\access;

use humhub\modules\user\helpers\AuthHelper;
use Yii;

class GuestAccessValidator extends AccessValidator
{
    public $name = 'guestAccess';

    public $code = 403;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->access->isGuest() && !AuthHelper::isGuestAccessEnabled()) {
            $this->code = 401;
            return false;
        }

        if (!$this->access->isGuest()) {
            return true;
        }

        // If there is a guest restriction rule only return true if there is an action related rule
        foreach ($this->filterRelatedRules() as $rule) {
            if ($this->isActionRelated($rule)) {
                return true;
            }
        }

        return false;
    }
}
