<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\Link;
use Yii;

/**
 * SpaceDirectoryIcons shows footer icons for spaces cards
 *
 * @since 1.9
 * @author Luke
 */
class SpaceDirectoryIcons extends Widget
{
    /**
     * @var Space
     */
    public $space;

    /**
     * @var string $separator Separator between icons
     */
    public string $separator = ' ';

    /**
     * @var array $icons An icon can be HTML code or object convertable to string
     */
    protected array $icons = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addMembersIcon();
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return implode($this->separator, $this->icons);
    }

    protected function addMembersIcon(): void
    {
        if ($this->space->getAdvancedSettings()->hideMembers) {
            return;
        }

        $membersCount = Yii::$app->runtimeCache->getOrSet(
            __METHOD__ . Yii::$app->user->id . '-' . $this->space->id,
            fn() => $this->space->getMemberListService()->getReadableQuery()->count(),
        );

        $text = ' <span>' . Yii::$app->formatter->asShortInteger($membersCount) . '</span>';
        $class = 'fa fa-users';

        $membership = $this->space->getMembership();
        if ($membership && $membership->isPrivileged()) {
            $icon = Link::withAction($text, 'ui.modal.load', $this->space->createUrl('/space/membership/members-list'))
                ->cssClass($class);
        } else {
            $icon = Html::tag('span', $text, ['class' => $class]);
        }

        $this->addIcon($icon);
    }

    /**
     * Add an icon to this widget
     *
     * @param mixed $icon HTML code or object convertable to string
     * @return void
     */
    public function addIcon($icon): void
    {
        $this->icons[] = $icon;
    }

}
