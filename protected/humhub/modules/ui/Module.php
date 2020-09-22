<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui;

use Yii;

/**
 * This module provides general user interface components.
 *
 * @since 1.3
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('UiModule.base', 'User Interface');
    }

}
