<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\listing\ListContext;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\space\components\SpaceList;
use humhub\widgets\Icon;
use humhub\widgets\VueWidget;

/**
 * The spaces browser (`/spaces`) as a `<spaces-browser>` island (`vue/SpacesBrowser.vue`).
 * Only cheap props are rendered — the filter definitions ({@see SpaceList::definitions()}),
 * the page header actions ({@see SpacesHeaderActions} as data), the button classes and
 * the icon markup; the spaces and the viewer's states load after mounting (see "Initial data:
 * embed or load" in `docs/develop/ui-js-vuejs-components.md`). Until then the placeholder shows
 * the page header and skeleton space cards.
 *
 * @since 1.20
 */
class SpacesBrowser extends VueWidget
{
    /**
     * @var array the classes of the card's buttons — the `MembershipButton` (`buttonClass`,
     * `pendingClass`, `memberClass`, `togglerClass`, `groupClass`), the `FollowButton`
     * (`followClass`, `followingClass`) and the disabled placeholder shown while the states load
     * (`placeholderClass`). The defaults are the design system's space card: Join primary, Follow
     * accent, the states (Member, Pending, Following) light. A theme may change them before
     * the widget runs.
     */
    public array $buttonClasses = [
        'buttonClass' => 'btn btn-primary',
        'pendingClass' => 'btn btn-light',
        'memberClass' => 'btn btn-light',
        'togglerClass' => 'btn btn-primary',
        'groupClass' => 'btn-group',
        'followClass' => 'btn btn-accent',
        'followingClass' => 'btn btn-light',
        'placeholderClass' => 'btn btn-light',
    ];

    protected string $component = 'SpacesBrowser';

    protected ?string $assetBundle = SpaceVueAsset::class;

    private ?SpacesHeaderActions $headerActions = null;

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        return [
            'filters' => (new SpaceList())->definitions(ListContext::forCurrentUser(SpaceList::PURPOSE_DIRECTORY)),
            'actions' => $this->getActions(),
            'buttons' => $this->buttonClasses,
            'icons' => [
                'check' => Icon::get('check')->asString(),
                'clock' => Icon::get('clock')->asString(),
                'user' => Icon::get('user')->asString(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getPlaceholder(): string
    {
        return $this->render('spacesBrowserPlaceholder', [
            'headerActions' => $this->getHeaderActions(),
        ]);
    }

    private function getActions(): array
    {
        return $this->getHeaderActions()->getEntriesData();
    }

    private function getHeaderActions(): SpacesHeaderActions
    {
        return $this->headerActions ??= new SpacesHeaderActions();
    }
}
