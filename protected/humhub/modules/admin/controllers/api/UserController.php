<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\components\table\DataTable;
use humhub\modules\admin\components\UserTable;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The rows of the administration's user table (Administration › Users, see
 * `docs/api/src/admin.yaml`): `GET admin/user` answers the {@see UserTable} rows of the users of
 * `AdminUserList` (its filters and sorts as query parameters) in the paginated envelope;
 * `POST admin/user/<id>/enable|disable` change a user's status and answer its new row;
 * `POST admin/user/bulk/enable|disable` change it for the selected users (`ids`, or `all` the
 * users of the list's filters in the query).
 *
 * For those who may see the table ({@see UserTable::canAccess()}): who may manage users or groups,
 * as the web user administration.
 *
 * @since 1.20
 */
class UserController extends DataTableController
{
    public const MAX_BULK_IDS = 1000;

    private const STATUSES = ['enable' => User::STATUS_ENABLED, 'disable' => User::STATUS_DEACTIVATED];

    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(UserTable::class);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'enable' => ['POST'],
                    'disable' => ['POST'],
                    'bulk' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionEnable($id)
    {
        return $this->changeStatus((int)$id, 'enable', self::STATUSES['enable']);
    }

    public function actionDisable($id)
    {
        return $this->changeStatus((int)$id, 'disable', self::STATUSES['disable']);
    }

    /**
     * Enables or disables the selected users — `ids` (at most {@see self::MAX_BULK_IDS}) or `all`,
     * the users of the list's filters (the query parameters, as `GET admin/user` takes them) —
     * each one whose row offers the action; the others are skipped.
     */
    public function actionBulk($action)
    {
        $action = (string)$action;
        $table = $this->getTable();
        $rowAction = $table->getAction($action);
        if ($rowAction === null || $rowAction->bulkUrl === null || !isset(self::STATUSES[$action])) {
            throw new NotFoundHttpException();
        }

        $request = Yii::$app->request;
        if (in_array($request->getBodyParam('all'), [true, 1, '1', 'true'], true)) {
            try {
                // Without the route's own `action`, which Yii puts among the query parameters
                $params = array_diff_key($this->listParams(), ['action' => true]);
                $users = $table->getList()->build($params, ListContext::forCurrentUser())->query();
            } catch (ListValidationException $e) {
                return $this->validationErrors($e->errors);
            }
        } else {
            $ids = $request->getBodyParam('ids');
            if (!is_array($ids) || $ids === [] || count($ids) > self::MAX_BULK_IDS
                || array_filter($ids, static fn($id) => !is_numeric($id)) !== []) {
                return $this->validationErrors(['ids' => [Yii::t('base', 'Invalid value.')]]);
            }
            $users = User::find()->where(['user.id' => array_map('intval', $ids)]);
        }

        $done = 0;
        $skipped = 0;
        foreach ($users->each() as $user) {
            /** @var User $user */
            if (!$rowAction->isVisibleFor($user)) {
                $skipped++;
                continue;
            }
            $user->status = self::STATUSES[$action];
            $user->save() ? $done++ : $skipped++;
        }

        $message = Yii::t('AdminModule.user', '{count, plural, =1{1 user was changed} other{# users were changed}}.', ['count' => $done]);
        if ($skipped > 0) {
            $message .= ' ' . Yii::t('AdminModule.user', '{count, plural, =1{1 user was left out} other{# users were left out}}.', ['count' => $skipped]);
        }

        return ['done' => $done, 'skipped' => $skipped, 'message' => $message];
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
