<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\interfaces\SearchProviderInterface;
use Yii;

/**
 * SearchProvider Widget
 * @since 1.16
 */
class SearchProvider extends Widget
{
    /**
     * @var string|SearchProviderInterface|null $searchProvider
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
        if ($this->searchProvider instanceof SearchProviderInterface) {
            return;
        }

        if (is_string($this->searchProvider)) {
            $this->searchProvider = Yii::createObject([
                'class' => $this->searchProvider,
                'keyword' => $this->keyword
            ]);

            if ($this->searchProvider instanceof SearchProviderInterface) {
                $this->searchProvider->search();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun() && $this->searchProvider instanceof SearchProviderInterface;
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
            'class' => 'dropdown-search-provider' . ($this->searchProvider->isSearched() ? ' provider-searched' : ''),
            'data-provider' => get_class($this->searchProvider)
        ];
    }
}
