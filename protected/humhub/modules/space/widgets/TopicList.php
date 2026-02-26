<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\topic\models\Topic;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\space\models\Space;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use Yii;
use yii\base\Exception;

/**
 * The Main Navigation for a space. It includes the Modules the Stream
 *
 * @author Luke
 * @since 0.5
 */
class TopicList extends LeftNavigation
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


        $this->panelTitle = "<b>" . Yii::t('TopicModule.base', 'Topics') . "</b>";
        $topics = Topic::findByContainer($this->space)->all();

        foreach ($topics as $topic) {
            /* @var $topic Topic */
            $this->addEntry(new MenuLink([
                'label' => $topic->name,
                'url' => $topic->getUrl(),
                'icon' => 'fa-tag',
                'sortOrder' => $topic->sort_order,
            ]));
         }

        parent::init();
    }

}
