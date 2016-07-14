<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\components;

use Yii;
use yii\web\HttpException;

/**
 * Default Space Manage Controller
 *
 * @author luke
 */
class Controller extends \humhub\modules\content\components\ContentContainerController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public $hideSidebar = true;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->adminOnly();
        return parent::beforeAction($action);
    }

    /**
     * Request only allowed for space  admins
     */
    public function adminOnly()
    {
        if (!$this->getSpace()->isAdmin())
            throw new HttpException(403, 'Access denied - Space Administrator only!');
    }

    /**
     * Request only allowed for workspace owner
     */
    public function ownerOnly()
    {
        $workspace = $this->getSpace();

        if (!$workspace->isSpaceOwner() && !Yii::$app->user->isAdmin())
            throw new HttpException(403, 'Access denied - Space Owner only!');
    }

}
