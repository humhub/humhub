<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\services;

use humhub\modules\marketplace\models\Module;
use humhub\modules\marketplace\widgets\ModuleFilters;
use Yii;

/**
 * @since 1.15
 */
class FilterService
{
    private Module $module;
    private string $moduleId;
    private int $categoryId;
    private array $tags;

    public function __construct(Module $module, ?int $categoryId = null, ?array $tags = null)
    {
        $this->module = $module;

        $this->moduleId = $moduleId ?? Yii::$app->request->get('id', '');

        $this->categoryId = $categoryId ?? Yii::$app->request->get('categoryId', 0);

        if (is_array($tags)) {
            $this->tags = $tags;
        } else {
            $tags = Yii::$app->request->get('tags', ModuleFilters::getDefaultValue('tags'));
            $this->tags = empty($tags) ? [] : explode(',', $tags);
        }
    }

    public function isFiltered(): bool
    {
        return $this->isFilteredById()
            && $this->isFilteredByCategory()
            && $this->isFilteredByTags();
    }

    public function isFilteredById(): bool
    {
        if (empty($this->moduleId)) {
            // All modules
            return true;
        }

        if ($this->moduleId === $this->module->id) {
            // Force tags to "All" on filter by module ID
            $this->tags = [];
            return true;
        }

        return false;
    }

    public function isFilteredByCategory(): bool
    {
        if (empty($this->categoryId)) {
            // All categories
            return true;
        }

        if ($this->categoryId == -1) {
            return empty($this->module->categories);
        }

        return is_array($this->module->categories) && in_array($this->categoryId, $this->module->categories);
    }

    private function isFilteredByTags(): bool
    {
        if ($this->tags === []) {
            // Any tags
            return true;
        }

        $searchInstalled = in_array('installed', $this->tags);
        $searchNotInstalled = in_array('uninstalled', $this->tags);
        if ($searchInstalled && $searchNotInstalled && count($this->tags) === 2) {
            // No need to filter when only 2 tags "Installed" and "Not Installed" are selected
            return true;
        }
        if ($searchInstalled && !$searchNotInstalled && !$this->module->isInstalled()) {
            // Exclude all NOT Installed modules when requested only Installed modules
            return false;
        }
        if (!$searchInstalled && $searchNotInstalled && $this->module->isInstalled()) {
            // Exclude all Installed modules when requested only NOT Installed modules
            return false;
        }
        if (($searchInstalled || $searchNotInstalled) && count($this->tags) === 1) {
            // No need to next filter when only 1 tag "Installed" or "Not Installed" is selected
            return true;
        }

        foreach ($this->tags as $tag) {
            switch ($tag) {
                case 'professional':
                    if ($this->module->isProFeature()) {
                        return true;
                    }
                    break;
                case 'featured':
                    if ($this->module->featured) {
                        return true;
                    }
                    break;
                case 'official':
                    if (!$this->module->isThirdParty) {
                        return true;
                    }
                    break;
                case 'partner':
                    if ($this->module->isPartner) {
                        return true;
                    }
                    break;
                case 'new':
                    // TODO: Filter by new status
                    break;
            }
        }

        return false;
    }

}
