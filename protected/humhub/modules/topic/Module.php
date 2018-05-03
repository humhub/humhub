<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic;

use humhub\modules\topic\permissions\ManageTopics;
use Yii;
use humhub\modules\topic\permissions\AddTopic;
use humhub\modules\space\models\Space;

/**
 * Admin Module
 */
class Module extends \humhub\components\Module
{

    /**
     * @var string defines the icon for topics used in badges etc.
     */
    public $icon = 'fa-star';

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof Space) {
            return [new AddTopic, new ManageTopics];
        }

        return [];
    }
}
