<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\SearchProvider;
use humhub\components\Widget;
use Yii;

/**
 * Search Provider Widget
 * @since 1.16
 */
class SearchProviderWidget extends Widget
{
    /**
     * @var string|SearchProvider|null $searchProvider
     */
    public $searchProvider;

    public ?string $keyword = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initSearchProvider();
    }

    protected function initSearchProvider()
    {
        if ($this->searchProvider instanceof SearchProvider) {
            return;
        }

        if (is_string($this->searchProvider)) {
            $this->searchProvider = Yii::createObject([
                'class' => $this->searchProvider,
                'keyword' => $this->keyword
            ]);

            if ($this->searchProvider instanceof SearchProvider) {
                $this->searchProvider->search();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun() && $this->searchProvider instanceof SearchProvider;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('searchProvider', [
            'options' => $this->getOptions(),
            'searchProvider' => $this->searchProvider,
        ]);
    }

    protected function getOptions(): array
    {
        return [
            'class' => 'search-provider' . ($this->searchProvider->isSearched() ? ' provider-searched' : ''),
            'data-provider' => get_class($this->searchProvider)
        ];
    }
}
