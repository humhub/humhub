<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\libs\Html;
use humhub\modules\ui\widgets\BaseImage;
use humhub\modules\user\models\User;
use humhub\modules\user\services\IsOnlineService;
use Yii;

/**
 * Image shows the user profile image
 *
 * @since 1.2
 * @author Luke
 */
class Image extends BaseImage
{
    /**
     * @var User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public $link = true;

    public bool $hideOnlineStatus = false;

    public bool $showSelfOnlineStatus = false;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun() && $this->user instanceof User;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->user->status === User::STATUS_SOFT_DELETED) {
            $this->link = false;
        }

        Html::addCssClass($this->imageOptions, 'img-rounded');
        Html::addCssStyle($this->imageOptions, 'width: ' . $this->width . 'px; height: ' . $this->height . 'px');

        if ($this->tooltipText || $this->showTooltip) {
            $this->imageOptions['data-toggle'] = 'tooltip';
            $this->imageOptions['data-placement'] = 'top';
            $this->imageOptions['data-html'] = 'true';
            $this->imageOptions['data-original-title'] = $this->tooltipText ?: Html::encode($this->user->displayName);
            Html::addCssClass($this->imageOptions, 'tt');
        }

        $this->imageOptions['data-contentcontainer-id'] = $this->user->contentcontainer_id;

        $this->imageOptions['alt'] = Yii::t('base', 'Profile picture of {displayName}', ['displayName' => Html::encode($this->user->displayName)]);
        $html = Html::img($this->user->getProfileImage()->getUrl(), $this->imageOptions);

        $isOnlineService = new IsOnlineService($this->user);
        if (
            !$this->hideOnlineStatus
            && ($this->showSelfOnlineStatus || $this->user->id !== Yii::$app->user->id)
            && $isOnlineService->isEnabled()
        ) {
            $imgSize = 'img-size-medium';
            if ($this->width < 28) {
                $imgSize = 'img-size-small';
            } elseif ($this->width > 48) {
                $imgSize = 'img-size-large';
            }
            if ($this->link) {
                Html::addCssClass($this->linkOptions, ['has-online-status', $imgSize]);
            } else {
                Html::addCssClass($this->htmlOptions, ['has-online-status', $imgSize]);
            }
            $userIsOnline = $isOnlineService->getStatus();
            $html .= Html::tag('span', '', [
                'class' => ['tt user-online-status', $userIsOnline ? 'user-is-online' : 'user-is-offline'],
                'title' => $userIsOnline
                    ? Yii::t('UserModule.base', 'Online')
                    : Yii::t('UserModule.base', 'Offline'),
            ]);
        }

        if ($this->link) {
            $html = Html::a($html, $this->user->getUrl(), $this->linkOptions);
        }

        return Html::tag('span', $html, $this->htmlOptions);
    }

}
