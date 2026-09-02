<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\user\serializers\UserSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UnauthorizedHttpException;

/**
 * The caller's own account data (see `docs/develop/concept-api.md`) — everything here is
 * about the authenticated user themself, never about another user, so none of it is
 * guest-accessible.
 *
 * @since 1.20
 */
class AccountController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected bool $enableSessionAuth = true;

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
                    'blocked-users' => ['GET', 'HEAD'],
                ],
            ],
        ]);
    }

    /**
     * The authenticated user.
     */
    public function actionIndex()
    {
        return UserSerializer::short($this->getIdentity());
    }

    /**
     * The ids of the users the caller has blocked.
     *
     * Clients need this to reproduce the platform's blocked-author masking — hiding content
     * from blocked authors is a display concern, so the API ships unmasked payloads plus this
     * list instead of masking server-side (which would need a second request to reveal, and
     * would still not be an access boundary). Empty when user blocking is disabled by an
     * administrator.
     */
    public function actionBlockedUsers()
    {
        return ['results' => array_map('intval', $this->getIdentity()->getBlockedUserIds())];
    }

    /**
     * @throws UnauthorizedHttpException
     */
    protected function getIdentity()
    {
        $identity = Yii::$app->user->getIdentity();

        if ($identity === null) {
            throw new UnauthorizedHttpException();
        }

        return $identity;
    }
}
