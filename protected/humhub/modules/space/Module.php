<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space;

/**
 * SpaceModule provides all space related classes & functions.
 *
 * @author Luke
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\space\controllers';

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof models\Space) {
            return [
                new permissions\InviteUsers(),
                new permissions\CreatePublicContent,
            ];
        }

        return [
            new permissions\CreatePrivateSpace(),
            new permissions\CreatePublicSpace(),
        ];
    }

}
