<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\Controller;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use Yii;
use yii\data\Pagination;
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
            'people' => Yii::t('UserModule.base', 'People'),
        ]);

        parent::init();
    }

    /**
     *
     */
    public function actionIndex()
    {
        $keyword = (string)Yii::$app->request->get('keyword');

        $query = User::find()->visible()->search($keyword);

        // Restrict to group
        $group = null;
        $groupId = (int)Yii::$app->request->get('groupId');
        if ($groupId) {
            $group = Group::findOne(['id' => $groupId, 'show_at_directory' => 1]);
            if ($group === null) {
                throw new HttpException(404);
            }
            $query->isGroupMember($group);
        }

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 10]);

        // Order
        $query->joinWith('profile');
        if (empty($this->module->memberListSortField) || $this->module->memberListSortField === 'lastname' || $this->module->memberListSortField === 'firstname') {
            // Fallback to default value
            $query->addOrderBy('profile.lastname');
        } else {
            $query->addOrderBy($this->module->memberListSortField);
        }

        return $this->render('index', [
            'keyword' => $keyword,
            'group' => $group,
            'users' => $query->offset($pagination->offset)->limit($pagination->limit)->all(),
            'pagination' => $pagination,
            'showInviteButton' => !Yii::$app->user->isGuest && Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite'),
        ]);
    }

}