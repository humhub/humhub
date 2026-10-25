<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\BaseController;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\modules\admin\components\UserTable;
use humhub\modules\user\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The rows of the administration's user table (Administration › Users, see
 * `docs/api/src/admin.yaml`): `GET admin/user` answers the {@see UserTable} rows of the users of
 * `AdminUserList` (its filters and sorts as query parameters) in the paginated envelope;
 * `POST admin/user/<id>/enable|disable` change a user's status and answer its new row.
 *
 * For those who may see the table ({@see UserTable::canAccess()}): who may manage users or groups,
 * as the web user administration.
 *
 * @since 1.20
 */
class UserController extends BaseController
{
    public const MAX_PAGE_SIZE = 100;

    private ?UserTable $table = null;

    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'HEAD'],
                    'enable' => ['POST'],
                    'disable' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!$this->getTable()->canAccess()) {
            throw new ForbiddenHttpException();
        }

        return true;
    }

    private function getTable(): UserTable
    {
        return $this->table ??= Yii::createObject(UserTable::class);
    }

    public function actionIndex()
    {
        $table = $this->getTable();

        try {
            $users = $table->getList()->build($this->listParams(), ListContext::forCurrentUser())->query();
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        $pagination = $this->handlePagination($users, 25, self::MAX_PAGE_SIZE);

        return $this->returnPagination($pagination, array_map($table->row(...), $users->all()));
    }

    public function actionEnable($id)
    {
        return $this->changeStatus((int)$id, 'enable', User::STATUS_ENABLED);
    }

    public function actionDisable($id)
    {
        return $this->changeStatus((int)$id, 'disable', User::STATUS_DEACTIVATED);
    }

    /**
     * Sets the status of a user whose row offers the action `$actionId`, and answers the new row.
     */
    private function changeStatus(int $id, string $actionId, int $status): array
    {
        $user = User::findOne(['id' => $id]);
        if ($user === null) {
            throw new NotFoundHttpException(Yii::t('AdminModule.user', 'User not found!'));
        }

        $table = $this->getTable();
        $action = $table->getAction($actionId);
        if ($action === null || !$action->isVisibleFor($user)) {
            throw new ForbiddenHttpException();
        }

        $user->status = $status;
        if (!$user->save()) {
            return $this->validationErrors($user);
        }

        return ['row' => $table->row($user)];
    }
}
