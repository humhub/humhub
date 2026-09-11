<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\widgets;

use humhub\modules\notification\assets\NotificationVueAsset;
use humhub\modules\notification\services\NotificationWindowService;
use humhub\widgets\Icon;
use humhub\widgets\VueWidget;
use Yii;
use yii\helpers\Url;

/**
 * The notification menu of the top navigation: bell, unread badge and dropdown.
 *
 * A Vue island (`NotificationMenu`, see `docs/develop/ui-js-vuejs-components.md`) since 1.19 —
 * this widget renders its mount point and hands over what only the server knows: the first page
 * of notifications (so the first paint costs no request), the two routes the menu links to, and
 * the rendered icon markup (the icon provider is pluggable, so a client cannot build it).
 *
 * The element keeps its id and class (`#notification_widget.btn-group`), like the ids inside the
 * island itself, because theme CSS and the product tour address them.
 *
 * @author buddha
 * @since 1.1
 */
class Overview extends VueWidget
{
    protected string $component = 'NotificationMenu';

    protected ?string $assetBundle = NotificationVueAsset::class;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     */
    protected function getOptions(): array
    {
        return ['id' => 'notification_widget', 'class' => 'btn-group'];
    }

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        return [
            'initial' => (new NotificationWindowService())->window(NotificationWindowService::MENU_PAGE_SIZE),
            'pageSize' => NotificationWindowService::MENU_PAGE_SIZE,
            'overviewUrl' => Url::to(['/notification/overview']),
            'settingsUrl' => Url::to(['/notification/user']),
            'bellIconHtml' => Icon::get('bell')->asString(),
            'checkIconHtml' => Icon::get('check')->asString(),
            'cogIconHtml' => Icon::get('cog')->asString(),
        ];
    }
}
