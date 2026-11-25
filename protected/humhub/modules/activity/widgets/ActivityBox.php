<?php

namespace humhub\modules\activity\widgets;

use humhub\modules\activity\assets\ActivityVueAsset;
use humhub\modules\activity\services\ActivityListService;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\widgets\SidebarCard;
use humhub\widgets\VueWidget;
use Yii;

/**
 * The "Latest activities" card of the dashboard sidebar and of a container's profile.
 *
 * A Vue island (`ActivityBox`, see `docs/develop/ui-js-vuejs-components.md`) since 1.20 — this
 * widget renders the sidebar card (title and ⋮ menu) with the island's mount point as its body
 * and hands over what only the server knows: the first page of activities and the container the
 * box is scoped to.
 *
 * That page is a full one (`$initLimit`), which is what keeps the box request-free until
 * someone scrolls: a shorter page would leave the island's load-more sentinel inside the
 * 400px-high box and have it fetch the rest immediately.
 *
 * The card (`#panel-activities.sidebar-card.panel-activities`) exists before the island mounts -
 * theme CSS, the product tour and tests address it - and the island renders the list.
 * `#activity-box-content.activities` and the markup of an entry are unchanged for the same reason.
 *
 * @since 1.1
 */
class ActivityBox extends VueWidget
{
    /**
     * @var string id of the card's body, which its ⋮ menu collapses; also the key of the
     * remembered state, the same as of the former panel
     */
    public const COLLAPSE_ID = 'panel-activities-body';

    public ?ContentContainerActiveRecord $contentContainer = null;
    public int $initLimit = ActivityListService::PAGE_SIZE;

    protected string $component = 'ActivityBox';

    protected ?string $assetBundle = ActivityVueAsset::class;

    /**
     * @inheritdoc
     *
     * The sidebar card around the island's mount point
     */
    public function run()
    {
        ob_start();
        SidebarCard::begin([
            'title' => Yii::t('ActivityModule.base', '<strong>Latest</strong> activities'),
            'encodeTitle' => false,
            'collapseId' => self::COLLAPSE_ID,
            'options' => ['id' => 'panel-activities', 'class' => 'panel-activities'],
            'bodyOptions' => ['class' => 'p-0 pb-1'],
        ]);
        echo parent::run();
        SidebarCard::end();

        return ob_get_clean();
    }

    /**
     * @inheritdoc
     *
     * A block, as the list it holds (a custom element is inline)
     */
    protected function getOptions(): array
    {
        return ['class' => 'd-block'];
    }

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        return [
            'initial' => (new ActivityListService())->page(
                $this->initLimit,
                null,
                $this->contentContainer?->contentContainerRecord,
            ),
            // The content container id, as `SpaceShort`/`UserShort` carry it — what the
            // endpoint scopes by. The guid is what live events name the container by.
            'containerId' => (int)($this->contentContainer?->contentcontainer_id ?? 0),
            'containerGuid' => $this->contentContainer?->guid ?? '',
            'pageSize' => ActivityListService::PAGE_SIZE,
        ];
    }
}
