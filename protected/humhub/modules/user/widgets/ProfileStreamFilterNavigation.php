<?php


namespace humhub\modules\user\widgets;


use humhub\modules\stream\widgets\WallStreamFilterNavigation;
use humhub\modules\ui\filter\widgets\RadioFilterInput;
use humhub\modules\user\Module;
use humhub\modules\user\stream\filters\IncludeAllContributionsFilter;
use Yii;

/**
 * Stream filter navigation for profile streams. The profile stream adds some additional filters as a scope.
 */
class ProfileStreamFilterNavigation extends WallStreamFilterNavigation
{

    /**
     * Extra filter category for profile scope
     * @since 1.6
     */
    const FILTER_BLOCK_SCOPE = 'scope';

    /**
     * @inheritDoc
     */
    protected function initFilterBlocks()
    {
        parent::initFilterBlocks();
        $this->initScopeFilterBlock();
    }

    /**
     * Initializes the profile scope filter block
     * @since 1.6
     */
    protected function initScopeFilterBlock()
    {
        if(!$this->isScopeFilterSupported()) {
            return;
        }

        $this->addFilterBlock('scope', [
            'title' => Yii::t('StreamModule.filter', 'Scope'),
            'sortOrder' => 90
        ], static::PANEL_COLUMN_2);
    }

    /**
     * @inheritDoc
     */
    protected function initFilters()
    {
        parent::initFilters();
        $this->initScopeFilter();
    }

    /**
     * Initializes the profile scope filter
     * @since 1.6
     */
    protected function initScopeFilter()
    {
        if(!$this->isScopeFilterSupported()) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->addFilter([
            'id' => IncludeAllContributionsFilter::SCOPE_ALL,
            'title' => Yii::t('UserModule.base', 'Show all content'),
            'class' => RadioFilterInput::class,
            'category' => 'scope',
            'radioGroup' => 'scope',
            'force' => true,
            'sortOrder' => 500,
            'checked' => $module->includeAllUserContentsOnProfile
        ], static::FILTER_BLOCK_SCOPE);

        $this->addFilter([
            'id' => IncludeAllContributionsFilter::SCOPE_PROFILE,
            'title' => Yii::t('UserModule.base', 'Profile posts only'),
            'class' => RadioFilterInput::class,
            'category' => 'scope',
            'radioGroup' => 'scope',
            'force' => true,
            'sortOrder' => 510,
            'checked' => !$module->includeAllUserContentsOnProfile
        ], static::FILTER_BLOCK_SCOPE);
    }

    /**
     * @return bool scope filter is currently not supported for guest users
     */
    protected function isScopeFilterSupported()
    {
        return !Yii::$app->user->isGuest;
    }
}
