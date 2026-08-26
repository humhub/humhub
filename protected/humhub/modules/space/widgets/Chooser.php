<?php

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\space\models\Space;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\permissions\SpaceDirectoryAccess;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\components\PermissionManager;
use humhub\widgets\VueComponent;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * The space menu of the top navigation: the button showing the current space, and the dropdown
 * with search and the visitor's spaces.
 *
 * Two Vue islands since 1.20 (see `docs/develop/ui-js-vuejs-components.md`), because the two
 * parts sit in different places and the topbar addresses them with child selectors
 * (`.nav > li.nav-item > a.nav-link`): `SpaceChooserToggle` mounts INSIDE the button anchor,
 * `SpaceChooser` IS the dropdown menu. The `<li>`, the anchor and the ids theme CSS and the
 * acceptance tests use (`#space-menu`, `#space-menu-dropdown`) stay exactly where they were.
 *
 * The list itself is not rendered here and not inlined into the page: it is loaded when the menu
 * is first opened, which is what the removed `lazyLoad` property did — see `SpaceChooser.vue`.
 *
 * @package humhub.modules.space.widgets
 */
class Chooser extends Widget
{
    /**
     * @var int spaces the menu asks for per page
     */
    public int $pageSize = 25;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        SpaceVueAsset::register($this->view);

        return Html::tag(
            'li',
            $this->renderToggle() . $this->renderMenu(),
            ['class' => 'nav-item dropdown'],
        );
    }

    /**
     * The button: the current space's image, or the "My spaces" placeholder. The island keeps
     * it in step with pjax navigations; what is rendered here is what a visitor sees before it
     * mounts, so the top menu never jumps.
     */
    private function renderToggle(): string
    {
        $currentSpace = $this->getCurrentSpace();
        $imageHtml = $currentSpace === null
            ? ''
            : Image::widget([
                'space' => $currentSpace,
                'width' => 32,
                'htmlOptions' => ['class' => 'current-space-image'],
            ]);

        $noSpaceIconHtml = Icon::get('dot-circle-o')->asString();

        return Html::a(
            VueComponent::widget([
                'name' => 'SpaceChooserToggle',
                'props' => [
                    'initialImageHtml' => $imageHtml,
                    'noSpaceIconHtml' => $noSpaceIconHtml,
                ],
                'content' => $imageHtml !== ''
                    ? $imageHtml
                    : Html::tag('div', $noSpaceIconHtml . '<br>' . Yii::t('SpaceModule.chooser', 'My spaces'), ['class' => 'no-space']),
            ]),
            '#',
            [
                'id' => 'space-menu',
                'class' => 'nav-link dropdown-toggle',
                'data-bs-toggle' => 'dropdown',
            ],
        );
    }

    /**
     * The dropdown menu. The island is mounted on the menu element itself, so `.dropdown-menu`
     * carries the display Bootstrap toggles and the element exists before Vue does.
     */
    private function renderMenu(): string
    {
        return VueComponent::widget([
            'name' => 'SpaceChooser',
            'options' => [
                'id' => 'space-menu-dropdown',
                'class' => 'dropdown-menu',
            ],
            'props' => [
                'pageSize' => $this->pageSize,
                'createSpaceUrl' => $this->canCreateSpace()
                    ? Url::to(['/space/create/create'])
                    : '',
                'directoryUrl' => Yii::$app->user->can(SpaceDirectoryAccess::class)
                    ? Url::to(['/space/spaces'])
                    : '',
                'directoryIconHtml' => Icon::get('directory')->asString(),
                'resetIconHtml' => Icon::get('times-circle')->asString(),
            ],
        ]);
    }

    /**
     * @throws InvalidConfigException
     */
    private function canCreateSpace(): bool
    {
        /** @var PermissionManager $manager */
        $manager = Yii::$app->user->permissionmanager;

        return $manager->can(new CreatePublicSpace()) || $manager->can(new CreatePrivateSpace());
    }

    /**
     * The shape `space/browse/search-json` answers with — kept here because it is called from
     * outside the chooser as well (the space picker's search route, and modules), even though
     * the chooser island itself no longer renders items server-side.
     *
     * @param Space $space
     * @param bool $withChooserItem whether to include the rendered {@see SpaceChooserItem}
     */
    public static function getSpaceResult($space, $withChooserItem = true, $itemOptions = [])
    {
        $spaceInfo = [
            'guid' => $space->guid,
            'title' => $space->name,
            'tags' => Html::encode(implode(', ', $space->getTags())),
            'image' => Image::widget(['space' => $space, 'width' => 24]),
            'link' => $space->getUrl(),
        ];

        if ($withChooserItem) {
            $options = array_merge(['space' => $space, 'isMember' => false, 'isFollowing' => false], $itemOptions);
            $spaceInfo['output'] = SpaceChooserItem::widget($options);
        }

        return $spaceInfo;
    }

    private function getCurrentSpace(): ?Space
    {
        if (!Yii::$app->controller instanceof ContentContainerController) {
            return null;
        }

        $container = Yii::$app->controller->contentContainer ?? null;

        return $container instanceof Space ? $container : null;
    }
}
