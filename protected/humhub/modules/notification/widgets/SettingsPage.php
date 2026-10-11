<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\widgets;

use humhub\components\api\ApiRules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\notification\assets\NotificationVueAsset;
use humhub\modules\notification\controllers\api\SettingsController;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\widgets\VueWidget;
use Yii;

/**
 * The notification settings page: the `NotificationSettings` island, on the account settings
 * page (`scope = user`, the current user's settings) and on the administration settings page
 * (`scope = global`, the defaults for everyone).
 *
 * The first paint comes from {@see NotificationSettingsService::toArray()}, the same payload
 * `GET /api/v2/notification/settings` answers; the island saves and resets through that API.
 *
 * @since 1.20
 */
class SettingsPage extends VueWidget
{
    public const SCOPE_USER = 'user';
    public const SCOPE_GLOBAL = SettingsController::SCOPE_GLOBAL;

    /**
     * @var string `user` (the current user's settings) or `global` (the defaults for everyone)
     */
    public string $scope = self::SCOPE_USER;

    protected string $component = 'NotificationSettings';

    protected ?string $assetBundle = NotificationVueAsset::class;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     */
    protected function getOptions(): array
    {
        return ['id' => 'notification-settings'];
    }

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        $global = $this->scope === self::SCOPE_GLOBAL;
        $query = $global ? '?' . http_build_query(['scope' => self::SCOPE_GLOBAL]) : '';

        return [
            'initial' => (new NotificationSettingsService($global ? null : Yii::$app->user->getIdentity()))->toArray(),
            'settingsUrl' => ApiRules::url('notification/settings') . $query,
            'resetUrl' => $global ? null : ApiRules::url('notification/settings/reset'),
            'resetAllUrl' => $global && Yii::$app->user->can(ManageSettings::class) && Yii::$app->user->can(ManageUsers::class) ? ApiRules::url('notification/settings/reset-all') : null,
            'scope' => $global ? self::SCOPE_GLOBAL : self::SCOPE_USER,
        ];
    }
}
