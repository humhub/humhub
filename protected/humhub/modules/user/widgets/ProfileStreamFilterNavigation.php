<?php


namespace humhub\modules\user\widgets;


use humhub\modules\stream\widgets\WallStreamFilterNavigation;
use humhub\modules\user\Module;
use humhub\modules\user\stream\filters\IncludeAllContributionsFilter;
use Yii;

class ProfileStreamFilterNavigation extends WallStreamFilterNavigation
{

    protected function initFilters()
    {
        parent::initFilters();

        // IncludeAllContributionsFilter currently only supported for non guest users
        if(Yii::$app->user->isGuest) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->addFilter([
            'id' => IncludeAllContributionsFilter::ID,
            'title' => Yii::t('UserModule.base', 'Include all content'),
            'sortOrder' => 500,
            'checked' => $module->includeAllUserContentsOnProfile
        ], static::FILTER_BLOCK_BASIC);
    }

}
