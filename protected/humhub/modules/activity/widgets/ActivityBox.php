<?php

namespace humhub\modules\activity\widgets;

use humhub\components\Widget;
use humhub\modules\activity\assets\ActivityVueAsset;
use humhub\modules\activity\services\ActivityWindowService;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\widgets\PanelMenu;
use humhub\widgets\VueComponent;

/**
 * The "Latest activities" panel of the dashboard sidebar and of a container's profile.
 *
 * A Vue island (`ActivityBox`, see `docs/develop/ui-js-vuejs-components.md`) since 1.20 — this
 * widget renders its mount point and hands over what only the server knows: the first page of
 * activities, the container the box is scoped to and the rendered `PanelMenu`, whose entries
 * modules contribute server-side.
 *
 * That page is a full one (`$initLimit`), which is what keeps the box request-free until
 * someone scrolls: a shorter page would leave the island's load-more sentinel inside the
 * 400px-high box and have it fetch the rest immediately.
 *
 * The panel keeps its id and classes (`#panel-activities`, `#activity-box-content.activities`),
 * like the markup of an entry, because theme CSS addresses them.
 *
 * @since 1.1
 */
class ActivityBox extends Widget
{
    /**
     * @var string id of the element the panel menu collapses - see the `collapse` class in
     * `ActivityBox.vue`, which is what `ui.panel.PanelMenu` looks for first.
     */
    public const COLLAPSE_ID = 'panel-activities-body';

    public ?ContentContainerActiveRecord $contentContainer = null;
    public int $initLimit = ActivityWindowService::PAGE_SIZE;

    public function run()
    {
        return VueComponent::widget([
            'name' => 'ActivityBox',
            'assetBundle' => ActivityVueAsset::class,
            'props' => [
                'initial' => (new ActivityWindowService())->window(
                    $this->initLimit,
                    null,
                    $this->contentContainer?->contentContainerRecord,
                ),
                'containerGuid' => $this->contentContainer?->guid ?? '',
                'pageSize' => ActivityWindowService::PAGE_SIZE,
                // `PanelMenu` derives its collapse id from the view context, which used to be
                // this widget's own view and is now whatever view renders the island's mount
                // point - so it is given explicitly. The id lands on the element the menu
                // collapses and keys its remembered state in local storage.
                'panelMenuHtml' => PanelMenu::widget(['collapseId' => self::COLLAPSE_ID]),
            ],
        ]);
    }
}
