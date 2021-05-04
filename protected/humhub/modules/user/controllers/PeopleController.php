<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\user\components\PeopleQuery;
use Yii;

/**
 * PeopleController displays users directory
 *
 * @since 1.9
 */
class PeopleController extends Controller
{

    /**
     * @inheritdoc
     */
    public $subLayout = '@user/views/people/_layout';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('UserModule.base', 'People'),
        ]);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ];
    }

    /**
     * Action to display people page
     */
    public function actionIndex()
    {
        $peopleQuery = new PeopleQuery();

        return $this->render('index', [
            'people' => $peopleQuery,
            'showInviteButton' => !Yii::$app->user->isGuest && Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite'),
        ]);
    }

}