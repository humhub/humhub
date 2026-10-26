<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers\api;

use humhub\components\api\DataTableController;
use humhub\components\table\DataTable;
use humhub\modules\oauth\components\table\AdminGrantTable;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * The grants of all users as rows of the administration's grant table (`AdminGrantTable`):
 * `GET admin/oauth-grant` answers one page of them; `POST admin/oauth-grant/<id>/revoke` revokes
 * a grant (`{removed: true}`); `POST admin/oauth-grant/bulk/revoke` revokes the selected ones
 * (`ids`, or `all` of the list's filters in the query — with at least one filter set).
 *
 * For those who may manage the settings ({@see AdminGrantTable::canAccess()}).
 *
 * @since 1.20
 */
class AdminGrantController extends DataTableController
{
    /**
     * @inheritdoc
     */
    protected function createTable(): DataTable
    {
        return Yii::createObject(AdminGrantTable::class);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'revoke' => ['POST'],
                    'bulk-revoke' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionRevoke($id)
    {
        $grant = Grant::findOne(['id' => (int)$id]);
        if ($grant === null) {
            throw new NotFoundHttpException();
        }

        (new GrantService())->revoke($grant);

        return ['removed' => true];
    }

    /**
     * Revokes the selected grants.
     */
    public function actionBulkRevoke()
    {
        // All grants of all users only through a filter: never by accident
        $all = in_array(Yii::$app->request->getBodyParam('all'), [true, 1, '1', 'true'], true);
        if ($all && array_filter(array_intersect_key($this->listParams(), array_flip(['q', 'client', 'scope', 'type'])), static fn($value) => $value !== '') === []) {
            return $this->validationErrors(['all' => [Yii::t('OauthModule.base', 'Set a filter to revoke all matching grants.')]]);
        }

        $service = new GrantService();

        return $this->bulkAction(
            'revoke',
            static fn(array $ids) => Grant::find()->where(['id' => $ids]),
            static function (Grant $grant) use ($service): bool {
                $service->revoke($grant);
                return true;
            },
            static fn(int $done) => Yii::t('OauthModule.base', '{count} grants revoked.', ['count' => $done]),
        );
    }
}
