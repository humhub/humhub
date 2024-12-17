<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\components\SettingsManager;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;

/**
 * LinkRegistrationService is responsible for registrations (Global or per Space) using an Invite Link.
 *
 * @since 1.15
 */
final class LinkRegistrationService
{
    public const SETTING_VAR_ENABLED = 'auth.internalUsersCanInviteByLink';
    public const SETTING_VAR_SPACE_TOKEN = 'inviteToken';
    public const SETTING_VAR_ADMIN_TOKEN = 'registration.inviteToken';
    public const SETTING_VAR_PEOPLE_TOKEN = 'people.inviteToken';

    public const TARGET_ADMIN = 'admin';
    public const TARGET_PEOPLE = 'people';

    private ?Space $space;
    private ?string $token;
    public ?string $target = null;

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
        $this->initTarget();
    }

    private function initTarget(): void
    {
        if ($this->token && $this->target === null) {
            if ($this->token === $this->getSettings()->get(self::SETTING_VAR_ADMIN_TOKEN)) {
                $this->target = self::TARGET_ADMIN;
            } elseif ($this->token === $this->getSettings()->get(self::SETTING_VAR_PEOPLE_TOKEN)) {
                $this->target = self::TARGET_PEOPLE;
            }
        }
    }

    public function isValid(): bool
    {
        return $this->isEnabled() && $this->getStoredToken() === $this->token;
    }

    public function isEnabled(): bool
    {
        if ($this->target === self::TARGET_ADMIN) {
            // The link registration with token from Administration is always enabled
            return true;
        }

        return (bool) $this->getSettings()->get(self::SETTING_VAR_ENABLED, false);
    }

    public function getStoredToken(): ?string
    {
        if ($this->space) {
            return $this->space->settings->get(self::SETTING_VAR_SPACE_TOKEN);
        }

        if ($this->target === self::TARGET_ADMIN) {
            return $this->getSettings()->get(self::SETTING_VAR_ADMIN_TOKEN);
        }

        if ($this->target === self::TARGET_PEOPLE) {
            return $this->getSettings()->get(self::SETTING_VAR_PEOPLE_TOKEN);
        }

        return null;
    }

    public function setNewToken(): string
    {
        $newToken = Yii::$app->security->generateRandomString(Invite::LINK_TOKEN_LENGTH);
        if ($this->space) {
            $this->space->settings->set(self::SETTING_VAR_SPACE_TOKEN, $newToken);
        } else {
            $settingName = $this->target === 'admin'
                ? self::SETTING_VAR_ADMIN_TOKEN
                : self::SETTING_VAR_PEOPLE_TOKEN;
            $this->getSettings()->set($settingName, $newToken);
        }

        return $newToken;
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function convertToInvite(string $email): ?Invite
    {
        // Deleting any previous email invitation or abandoned link invitation
        $oldInvites = Invite::findAll(['email' => $email]);
        foreach ($oldInvites as $oldInvite) {
            $oldInvite->delete();
        }

        $invite = new Invite([
            'email' => $email,
            'language' => Yii::$app->language,
        ]);
        $invite->skipCaptchaValidation = true;
        $invite->source = Invite::SOURCE_INVITE_BY_LINK;
        if ($this->space) {
            $invite->space_invite_id = $this->space->id;
        }

        if ($invite->isRegisteredUser()) {
            $invite->sendAlreadyRegisteredUserMail();
            return null;
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

    private function getSettings(): SettingsManager
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('user');
        return $module->settings;
    }
}
