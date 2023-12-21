<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
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
    private ?string $token;

    public static function createFromRequest(): LinkRegistrationService
    {
        $token = (string)Yii::$app->request->get('token');
        $spaceId = (int)Yii::$app->request->get('spaceId');

        if (!$token && Yii::$app->session->has(LinkRegistrationService::class . '::token')) {
            $token = Yii::$app->session->get(LinkRegistrationService::class . '::token');
            $spaceId = Yii::$app->session->get(LinkRegistrationService::class . '::spaceId', null);
        }

        return new LinkRegistrationService($token, Space::findOne(['id' => $spaceId]));
    }


    public function __construct(?string $token = null, ?Space $space = null)
    {
        $this->token = $token;
        $this->space = $space;
    }

    public function isValid(): bool
    {
        return ($this->isEnabled() && $this->getStoredToken() === $this->token);
    }

    public function isEnabled(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return (!empty($module->settings->get(self::SETTING_VAR_ENABLED)));
    }

    public function getStoredToken(): ?string
    {
        if ($this->space) {
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
        $invite->skipCaptchaValidation = true;
        $invite->source = Invite::SOURCE_INVITE_BY_LINK;
        if ($this->space) {
            $invite->space_invite_id = $this->space->id;
        }

        if (!$invite->save()) {
            throw new Exception('Could not create invite!');
        }

        return $invite;
    }

    public function storeInSession()
    {
        Yii::$app->session->set(get_class($this) . '::token', $this->token);
        Yii::$app->session->set(get_class($this) . '::spaceId', $this->space->id ?? null);
    }

    /**
     * @throws Exception
     */
    public function inviteToSpace(?User $user): bool
    {
        if ($this->space && $user) {
            $this->space->inviteMember($user->id, $this->space->ownerUser->id, false);
            return true;
        }
        return false;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }
}
