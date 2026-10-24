<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\helpers\Html;
use humhub\modules\content\assets\ContentContainerAsset;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\Icon;
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\helpers\Url;

/**
 * The "Turn off/on notifications" entry of a content's context menu ({@see WallEntryControls}).
 *
 * Rendered markup carries both anchors, one of them hidden: the `content.container` script
 * swaps them after the switch without reloading the entry. A client rendering the menu itself
 * gets the one for the current state ({@see self::describe()}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 0.10
 */
class NotificationSwitchLink extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $content;

    /**
     * @var bool whether the current user receives notifications for the content
     */
    private bool $following = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->user->isGuest) {
            $this->setIsVisible(false);
            return;
        }

        $this->following = (bool)$this->content->isFollowedByUser(Yii::$app->user->id, true);
        $switch = $this->anchor(!$this->following);

        $this->setLabel($switch['label']);
        $this->setIcon($switch['icon']);
        $this->setUrl($switch['url']);
        $this->setHtmlOptions($switch['options']);
    }

    /**
     * @inheritdoc
     */
    public function renderEntry($extraHtmlOptions = [])
    {
        ContentContainerAsset::register(Yii::$app->view);

        $html = '';

        foreach ([false, true] as $turnOn) {
            $anchor = $this->anchor($turnOn);
            $options = $anchor['options'];
            Html::addCssClass($options, $extraHtmlOptions['class'] ?? []);

            // Only the anchor for the switch the user can make now is shown.
            if ($turnOn === $this->following) {
                Html::addCssClass($options, 'd-none');
            }

            $html .= Html::a(Icon::get($anchor['icon']) . ' ' . Html::encode($anchor['label']), $anchor['url'], $options);
        }

        return $html;
    }

    /**
     * The anchor that turns notifications on or off.
     *
     * @return array{label: string, icon: string, url: string, options: array}
     */
    private function anchor(bool $turnOn): array
    {
        $contentId = $this->content->content->id;
        $url = Url::to(['/content/content/notification-switch', 'id' => $contentId, 'switch' => $turnOn ? 1 : 0]);

        return [
            'label' => $turnOn
                ? Yii::t('ContentModule.base', 'Turn on notifications')
                : Yii::t('ContentModule.base', 'Turn off notifications'),
            'icon' => $turnOn ? 'bell' : 'bell-off',
            'url' => $url,
            'options' => [
                'id' => ($turnOn ? 'notification_on_' : 'notification_off_') . $contentId,
                'class' => $turnOn ? 'turnOnNotifications' : 'turnOffNotifications',
                'data-action-click' => $turnOn ? 'content.container.turnOnNotifications' : 'content.container.turnOffNotifications',
                'data-action-url' => $url,
                'data-content-id' => $contentId,
            ],
        ];
    }
}
