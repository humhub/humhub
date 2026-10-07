<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Yii;

/**
 * Notifies a member that their role in a space was changed. The source is the member's
 * {@see Membership}; the payload's `groupId` names the new role (falling back to the membership's
 * current `group_id`), so a later change does not rewrite this sentence. The notification links
 * to the space.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `ChangedRolesMembership` (since 1.3).
 *
 * @since 1.20
 */
final class SpaceRolesChangedNotification extends BaseNotification
{
    use SpaceNotificationTrait {
        getMessageParamsPlain as private traitGetMessageParamsPlain;
        getMessageParamsWeb as private traitGetMessageParamsWeb;
    }

    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::direct();
    }

    /**
     * @inheritdoc
     */
    public static function priority(): NotificationPriority
    {
        return NotificationPriority::Normal;
    }

    /**
     * The space of the membership.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return $this->getSpace()?->getUrl($scheme);
    }

    /**
     * The space of the membership - the notification has no container.
     *
     * @inheritdoc
     */
    public function getSpace(): ?Space
    {
        return $this->sourceRecord instanceof Membership ? $this->sourceRecord->space : null;
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('SpaceModule.notification', '{displayName} changed your role to {roleName} in the space {spaceName}.', [
            'displayName' => $params['displayName'],
            'roleName' => $params['roleName'],
            'spaceName' => $params['spaceName'],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParamsPlain(int $maxLength): array
    {
        return array_merge($this->traitGetMessageParamsPlain($maxLength), [
            'roleName' => $this->getRoleName(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParamsWeb(): array
    {
        return array_merge($this->traitGetMessageParamsWeb(), [
            'roleName' => Html::strong(Html::encode($this->getRoleName())),
        ]);
    }

    /**
     * The name of the role the member got; its id when the role is unknown.
     */
    private function getRoleName(): string
    {
        $groupId = (string)($this->payload['groupId']
            ?? ($this->sourceRecord instanceof Membership ? $this->sourceRecord->group_id : ''));

        return Space::getUserGroups()[$groupId] ?? $groupId;
    }
}
