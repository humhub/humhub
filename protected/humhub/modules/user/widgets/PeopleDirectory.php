<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\listing\ListContext;
use humhub\modules\friendship\assets\FriendshipVueAsset;
use humhub\modules\user\assets\UserVueAsset;
use humhub\modules\user\components\UserList;
use humhub\modules\user\Module;
use humhub\widgets\Icon;
use humhub\widgets\VueWidget;
use Yii;

/**
 * The People directory (`/people`) as a `<people-directory>` island (`vue/PeopleDirectory.vue`).
 * Only cheap props are rendered — the filter definitions ({@see UserList::definitions()}),
 * the toolbar actions ({@see PeopleHeadingButtons} as data), the button classes, which of
 * following and friendship the platform offers and the icon markup; the users and the viewer's
 * states load after mounting (see "Initial data: embed or load" in
 * `docs/develop/ui-js-vuejs-components.md`). Until then the placeholder shows the page toolbar
 * and skeleton people cards.
 *
 * The friendship module's bundle registers the card's friendship action (the entry
 * `friendship` of the `user.card-actions` slot, its `FriendshipButton`), so it is registered
 * along — also while the friendship system is off, when the action renders nothing.
 *
 * @since 1.20
 */
class PeopleDirectory extends VueWidget
{
    /**
     * @var array the classes of the card's buttons — the `FriendshipButton` (`friendClass` of
     * "add friend", `friendStateClass` of the states that follow — pending, received request,
     * friends —, `friendTogglerClass` and `friendGroupClass` of the received request's dropdown),
     * the `UserFollowButton` (`followClass`, `followingClass`) and the disabled placeholder shown
     * while the states load (`placeholderClass`). The defaults are the design system's people
     * card: Add friend primary, Follow accent, the states light. A theme may change them before
     * the widget runs.
     */
    public array $buttonClasses = [
        'friendClass' => 'btn btn-primary',
        'friendStateClass' => 'btn btn-light',
        'friendTogglerClass' => 'btn btn-light',
        'friendGroupClass' => 'btn-group',
        'followClass' => 'btn btn-accent',
        'followingClass' => 'btn btn-light',
        'placeholderClass' => 'btn btn-light',
    ];

    protected string $component = 'PeopleDirectory';

    protected ?string $assetBundle = UserVueAsset::class;

    private ?array $actions = null;

    /**
     * @inheritdoc
     */
    public function run()
    {
        FriendshipVueAsset::register($this->getView());

        return parent::run();
    }

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return [
            'filters' => (new UserList())->definitions(ListContext::forCurrentUser(UserList::PURPOSE_DIRECTORY)),
            'actions' => $this->getActions(),
            'buttons' => $this->buttonClasses,
            'followEnabled' => !$module->disableFollow,
            'friendshipEnabled' => $this->isFriendshipEnabled(),
            'icons' => [
                'check' => Icon::get('check')->asString(),
                'plus' => Icon::get('plus')->asString(),
                'clock' => Icon::get('clock')->asString(),
                'times' => Icon::get('x')->asString(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getPlaceholder(): string
    {
        return $this->render('peopleDirectoryPlaceholder', [
            'actions' => $this->getActions(),
        ]);
    }

    private function isFriendshipEnabled(): bool
    {
        return Yii::$app->getModule('friendship')->isFriendshipEnabled();
    }

    private function getActions(): array
    {
        return $this->actions ??= (new PeopleHeadingButtons())->getEntriesData();
    }
}
