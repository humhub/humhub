<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\widgets;

use humhub\modules\oauth\models\Grant;
use humhub\modules\user\widgets\security\SecuritySectionStack;
use Yii;

/**
 * The sections of the "Apps & Tokens" tab.
 *
 * @since 1.20
 */
class AppsSections extends SecuritySectionStack
{
    /**
     * @var \WeakMap<object, array<int, bool>>|null availability per request and user
     */
    private static ?\WeakMap $available = null;

    /**
     * Whether the current user gets the tab: with a listed grant, or when allowed to create tokens.
     *
     * @param bool $refresh whether to recheck after a change within the request
     */
    public static function isAvailable(bool $refresh = false): bool
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return false;
        }

        // Asked by the menu and by the controller of every request
        self::$available ??= new \WeakMap();
        $request = Yii::$app->request;
        $cache = self::$available[$request] ?? [];
        if ($refresh || !isset($cache[$user->id])) {
            $cache[$user->id] = self::hasListedGrants((int)$user->id) || TokensSection::canCreate();
            self::$available[$request] = $cache;
        }

        return $cache[$user->id];
    }

    /**
     * Personal access tokens, also expired ones, and OAuth grants in use, as the sections list them.
     */
    private static function hasListedGrants(int $userId): bool
    {
        return Grant::find()
            ->where(['user_id' => $userId])
            ->andWhere(['or',
                ['type' => Grant::TYPE_PAT],
                ['and', ['type' => Grant::TYPE_OAUTH], ['not', ['last_used_at' => null]]],
            ])
            ->exists();
    }

    public function init()
    {
        $this->addWidget(DevicesSection::class, [], ['sortOrder' => 100]);
        $this->addWidget(ConnectedAppsSection::class, [], ['sortOrder' => 200]);
        $this->addWidget(TokensSection::class, [], ['sortOrder' => 300]);

        parent::init();
    }
}
