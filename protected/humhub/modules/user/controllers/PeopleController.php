<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\space\permissions\SpaceDirectoryAccess;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\permissions\PeopleAccess;
use humhub\modules\user\widgets\PeopleCard;use Yii;
use yii\helpers\Url;
use yii\web\HttpException;

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
            ['validatePeopleAccess', 'actions' => ['index', 'load-more']]
        ];
    }

    public function validatePeopleAccess($rule, $access)
    {
        if (Yii::$app->user->isGuest && AuthHelper::isGuestAccessPeoplePageEnabled()) {
            return true;
        }

        if (Yii::$app->user->can(PeopleAccess::class)) {
            return true;
        }

        return false;
    }

    /**
     * Action to display people page
     */
    public function actionIndex()
    {
        $peopleQuery = new PeopleQuery();

        $urlParams = Yii::$app->request->getQueryParams();
        unset($urlParams['page']);
        array_unshift($urlParams, '/user/people/load-more');
        $this->getView()->registerJsConfig('cards', [
            'loadMoreUrl' => Url::to($urlParams),
        ]);

        return $this->render('index', [
            'people' => $peopleQuery,
            'showInviteButton' => !Yii::$app->user->isGuest && Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite'),
        ]);
    }

    /**
     * Action to load cards for next page by AJAX
     */
    public function actionLoadMore()
    {
        $peopleQuery = new PeopleQuery();

        $peopleCards = '';
        foreach ($peopleQuery->all() as $user) {
            $peopleCards .= PeopleCard::widget(['user' => $user]);
        }

        return $peopleCards;
    }

}
