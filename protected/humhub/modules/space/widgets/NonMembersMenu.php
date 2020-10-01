<?php


namespace humhub\modules\space\widgets;


use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\space\models\Space;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use Yii;
use yii\base\Exception;

class NonMembersMenu extends LeftNavigation
{
    /** @var Space */
    public $space;

    /** @var Space */
    public $id = 'space-main-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->space) {
            $this->space = ContentContainerHelper::getCurrent(Space::class);
        }

        if (!$this->space) {
            throw new Exception('Could not instance space menu without space!');
        }

        $this->panelTitle = Yii::t('SpaceModule.base', '<strong>Space</strong> menu');

        $this->addEntry(new MenuLink([
            'label' => Yii::t('SpaceModule.base', 'Stream'),
            'url' => $this->space->createUrl('/space/space/home'),
            'icon' => 'bars',
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('space', 'space', ['index', 'home']),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('SpaceModule.base', 'About Space'),
            'url' => $this->space->createUrl('/space/space/about'),
            'icon' => 'info-circle',
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('space', 'space', ['about']),
        ]));

        parent::init();
    }
}
