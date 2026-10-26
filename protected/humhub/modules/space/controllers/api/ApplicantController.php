<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\modules\space\models\Membership;
use humhub\modules\space\modules\manage\components\SpaceApplicantTable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * The membership requests of a space as rows of its applicant table (`SpaceApplicantTable`):
 * `GET space/<id>/applicant` answers one page of them;
 * `POST space/<id>/applicant/<userId>/accept|decline` accepts (the user becomes a member) or
 * declines a request (`{removed: true}` either way); `POST space/<id>/applicant/bulk/accept|decline`
 * does so for the selected ones (`ids`, or `all` of the list's filters in the query).
 *
 * For the space's administrators ({@see SpaceApplicantTable::canAccess()}).
 *
 * @since 1.20
 */
class ApplicantController extends MembershipTableController
{
    /**
     * @inheritdoc
     */
    protected function tableClass(): string
    {
        return SpaceApplicantTable::class;
    }

    /**
     * @inheritdoc
     */
    protected function membershipStatus(): int
    {
        return Membership::STATUS_APPLICANT;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'actions' => [
                    'accept' => ['POST'],
                    'decline' => ['POST'],
                    'bulk' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionAccept($userId)
    {
        $this->accept($this->findMembership((int)$userId, 'accept'));

        return ['removed' => true];
    }

    public function actionDecline($userId)
    {
        $this->space()->removeMember($this->findMembership((int)$userId, 'decline')->user_id);

        return ['removed' => true];
    }

    /**
     * Accepts or declines the selected requests (user ids).
     */
    public function actionBulk($action)
    {
        if (!in_array($action, ['accept', 'decline'], true)) {
            throw new NotFoundHttpException();
        }

        $space = $this->space();

        return $this->bulkAction(
            $action,
            $this->findMemberships(...),
            fn(Membership $membership) => $action === 'accept'
                ? $this->accept($membership)
                : (bool)$space->removeMember($membership->user_id),
            static fn(int $done, int $skipped) => $action === 'accept'
                ? Yii::t('SpaceModule.manage', '{count, plural, =1{1 request was accepted} other{# requests were accepted}}.', ['count' => $done])
                : Yii::t('SpaceModule.manage', '{count, plural, =1{1 request was declined} other{# requests were declined}}.', ['count' => $done]),
        );
    }

    private function accept(Membership $membership): bool
    {
        $this->space()->addMember($membership->user_id);

        return true;
    }
}
