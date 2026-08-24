<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\MembershipSerializer;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\VueComponent;
use Yii;

/**
 * The membership button of a space: join, apply for membership, answer an invite, leave.
 *
 * Since 1.20 this widget renders the `MembershipButton` Vue island (see its own docblock) and
 * hands it the current membership state; every transition afterwards happens client side
 * against `/api/v2/space/<id>/membership`. What the widget still owns is what only the server
 * knows: the initial state, the space, the rendered icon markup — and the presentation.
 *
 * ## Presentation
 *
 * The properties below carry it, replacing the per-button option array of earlier versions.
 * A caller sets what its context needs; the space directory shows the member state, the space
 * header hides it (leaving happens through its controls menu) and reloads once the viewer
 * became a member.
 *
 * They are nullable so that a theme customizing the button from an {@see self::EVENT_INIT}
 * handler can supply a default *without* overriding what the call site itself configured:
 *
 * ```php
 * $membershipButton->buttonClass ??= 'btn btn-accent btn-sm';
 * ```
 *
 * Titles, urls and `data-action-*` attributes are not configurable, and no longer need to be:
 * the island renders the button and talks to the API itself, so there is nothing left for a
 * caller to point somewhere else — which is what the option round trip through the client
 * (#8381, #8382) existed for.
 *
 * @author luke
 * @since 0.11
 */
class MembershipButton extends Widget
{
    /**
     * @var string classes of the join / request / accept-invite button
     */
    public const DEFAULT_BUTTON_CLASS = 'btn btn-accent';

    /**
     * @var string classes of the pending and member states
     */
    public const DEFAULT_STATE_CLASS = 'btn btn-accent active';

    /**
     * @var Space
     */
    public $space;

    /**
     * @var string|null classes of the join / request / accept-invite button
     * @since 1.20
     */
    public ?string $buttonClass = null;

    /**
     * @var string|null classes of the "Pending" button of an open application
     * @since 1.20
     */
    public ?string $pendingClass = null;

    /**
     * @var string|null classes of the "Member"/"Owner" button
     * @since 1.20
     */
    public ?string $memberClass = null;

    /**
     * @var string|null classes of the dropdown toggle next to "Accept Invite"
     * @since 1.20
     */
    public ?string $togglerClass = null;

    /**
     * @var string|null classes of the button group of the invite state
     * @since 1.20
     */
    public ?string $groupClass = null;

    /**
     * @var bool|null whether the member state is rendered at all. The space header hides it
     * (leaving happens through its controls menu), the space directory shows it.
     * @since 1.20
     */
    public ?bool $showMemberState = null;

    /**
     * @var bool|null whether the page is reloaded once the viewer became a member. Becoming a
     * member changes the whole page around the button (its menu, what the viewer may see), so
     * the space header asks for it.
     * @since 1.20
     */
    public ?bool $reloadOnJoin = null;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest || $this->space->isBlockedForUser()) {
            return '';
        }

        return VueComponent::widget([
            'name' => 'MembershipButton',
            'assetBundle' => SpaceVueAsset::class,
            'props' => [
                'spaceId' => $this->space->id,
                'spaceName' => $this->space->getDisplayName(),
                'spaceUrl' => $this->space->createUrl(),
                'initial' => MembershipSerializer::state($this->space),
                'buttonClass' => $this->buttonClass ?? self::DEFAULT_BUTTON_CLASS,
                'pendingClass' => $this->pendingClass ?? self::DEFAULT_STATE_CLASS,
                'memberClass' => $this->memberClass ?? self::DEFAULT_STATE_CLASS,
                'togglerClass' => $this->togglerClass ?? self::DEFAULT_BUTTON_CLASS,
                'groupClass' => $this->groupClass ?? 'btn-group',
                'showMemberState' => (bool)$this->showMemberState,
                'reloadOnJoin' => (bool)$this->reloadOnJoin,
                'checkIconHtml' => Icon::get('check')->asString(),
                'clockIconHtml' => Icon::get('clock-o')->asString(),
                'userIconHtml' => Icon::get('user')->asString(),
            ],
        ]);
    }
}
