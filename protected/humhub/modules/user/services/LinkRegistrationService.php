<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\Invite;
use humhub\modules\user\Module;
use Yii;
use yii\base\Exception;

/**
 * LinkRegistrationService is responsible for registrations (Global or per Space) using an Invite Link.
 *
 * @since 1.15
 */
final class LinkRegistrationService
{
    const SETTING_VAR_ENABLED = 'auth.internalUsersCanInviteByLink';
    const SETTING_VAR_SPACE_TOKEN = 'inviteToken';
    const SETTING_VAR_TOKEN = 'registration.inviteToken';
    private ?Space $space;

    public function __construct(?Space $space = null)
    {
        $this->space = $space;
    }

    public function isValid(string $token): bool
    {
        return ($this->isEnabled() && $this->getToken() === $token);
    }

    public function isEnabled(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return (!empty($module->settings->get(self::SETTING_VAR_ENABLED)));
    }

    public function getToken(): ?string
    {
        if ($this->space) {
            // TODO: Find better solution
            Yii::$app->setLanguage($this->space->ownerUser->language);

            return $this->space->settings->get(self::SETTING_VAR_SPACE_TOKEN);
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return $module->settings->get(self::SETTING_VAR_TOKEN);
    }

    public function setNewToken(): string
    {
        $newToken = Yii::$app->security->generateRandomString(Invite::LINK_TOKEN_LENGTH);
        if ($this->space) {
            $this->space->settings->set(self::SETTING_VAR_SPACE_TOKEN, $newToken);
        } else {
            /** @var Module $module */
            $module = Yii::$app->getModule('user');

            $module->settings->set(self::SETTING_VAR_TOKEN, $newToken);
        }

        return $newToken;
    }

    public function convertToInvite(string $email): Invite
    {
        // Deleting any previous email invitation or abandoned link invitation
        $oldInvite = Invite::findOne(['email' => $email]);
        if ($oldInvite !== null) {
            $oldInvite->delete();
        }

        $invite = new Invite([
            'email' => $email,
            'scenario' => 'invite',
            'language' => Yii::$app->language,
        ]);

        $invite->source = Invite::SOURCE_INVITE_BY_LINK;
        if ($this->space) {
            $invite->space_invite_id = $this->space->id;
        }

        if (!$invite->save()) {
            throw new Exception('Could not create invite!');
        }

        return $invite;
    }

}
