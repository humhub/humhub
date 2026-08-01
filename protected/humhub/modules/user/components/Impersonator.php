<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\admin\Module as AdminModule;
use humhub\modules\user\models\User as UserModel;
use Yii;
use yii\base\Behavior;

/**
 * Impersonator behavior provides actions to impersonate users by admin
 *
 * @since 1.10
 * @property-read UserModel|null $impersonator Admin user who impersonate the current User
 * @property bool $isImpersonated Whether this user is impersonated by admin currently.
 * @property-read bool $isPrivateContentRestricted Whether private content must be hidden from the current session.
 * @author luke
 */
class Impersonator extends Behavior
{
    /**
     * @var User
     */
    public $owner;

    protected bool $impersonated = false;

    private ?bool $privateContentRestricted = null;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            User::EVENT_BEFORE_SWITCH_IDENTITY => 'onBeforeSwitchIdentity',
        ];
    }

    /**
     * Resets the memoized [[getIsPrivateContentRestricted()]] result whenever the identity changes.
     *
     * @since 1.19
     */
    public function onBeforeSwitchIdentity()
    {
        $this->privateContentRestricted = null;
    }

    /**
     * Determines if the current user can impersonate the given user.
     *
     * @param UserModel $user
     * @return bool
     */
    public function canImpersonate(UserModel $user): bool
    {
        if ($this->owner->isGuest) {
            return false;
        }

        return $this->owner->getIdentity()->canImpersonate($user);
    }

    /**
     * @return bool True if this user is impersonated by admin currently
     */
    public function getIsImpersonated(): bool
    {
        return $this->impersonated || $this->getImpersonator() !== null;
    }

    public function setIsImpersonated($impersonated)
    {
        $this->impersonated = $impersonated;
        $this->privateContentRestricted = null;
    }

    /**
     * Determines if the current session is an impersonation which must not have access to private content,
     * see [[AdminModule::$impersonateMode]].
     *
     * @return bool
     * @since 1.19
     */
    public function getIsPrivateContentRestricted(): bool
    {
        if ($this->privateContentRestricted === null) {
            /* @var AdminModule $adminModule */
            $adminModule = Yii::$app->getModule('admin');
            // The `session` component is only available in web applications, impersonation is session based
            $this->privateContentRestricted = Yii::$app->has('session')
                && $this->getIsImpersonated()
                && $adminModule->isImpersonatePrivateContentDenied();
        }

        return $this->privateContentRestricted;
    }

    /**
     * Get admin user who impersonate current user
     *
     * @return UserModel|null
     */
    public function getImpersonator(): ?UserModel
    {
        if ($this->owner->isGuest) {
            return null;
        }

        $impersonator = Yii::$app->session->get('impersonator');

        if (!($impersonator instanceof UserModel)) {
            return null;
        }

        if (!$impersonator->canImpersonate($this->owner->getIdentity())) {
            return null;
        }

        return $impersonator;
    }

    /**
     * Impersonate the given user with storing current user in session in order to sing in back
     *
     * @param UserModel $user
     * @return bool
     */
    public function impersonate(UserModel $user): bool
    {
        if (!$this->canImpersonate($user)) {
            return false;
        }

        $impersonator = $this->owner->getIdentity();

        /* @var AdminModule $adminModule */
        $adminModule = Yii::$app->getModule('admin');
        if ($adminModule->isImpersonateLogged()) {
            Yii::warning(sprintf(
                'User "%s" (ID: %d) impersonates user "%s" (ID: %d).',
                $impersonator->displayName,
                $impersonator->id,
                $user->displayName,
                $user->id,
            ), 'user');
        }

        Yii::$app->session->set('impersonator', $impersonator);
        $this->setIsImpersonated(true);
        $this->owner->switchIdentity($user);

        return true;
    }

    /**
     * Restore impersonator user from session
     *
     * @return bool
     */
    public function restoreImpersonator(): bool
    {
        if (!($impersonator = $this->getImpersonator())) {
            return false;
        }

        Yii::$app->session->remove('impersonator');
        $this->setIsImpersonated(false);
        $this->owner->switchIdentity($impersonator);

        return true;
    }
}
