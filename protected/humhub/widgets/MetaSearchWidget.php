<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\modules\content\search\ContentSearchProvider;
use humhub\modules\space\search\SpaceSearchProvider;
use humhub\modules\user\search\UserSearchProvider;
use Yii;

/**
 * Meta Search Widget for TopMenuRightStack
 * @since 1.16
 */
class MetaSearchWidget extends JsWidget
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.search';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @var string[]|MetaSearchProviderInterface[] $searchProviders
     */
    protected array $providers = [
        ContentSearchProvider::class,
        UserSearchProvider::class,
        SpaceSearchProvider::class,
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('metaSearch', [
            'options' => $this->getOptions(),
            'providers' => $this->getSortedProviders(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getAttributes()
    {
        return ['class' => 'dropdown search-menu'];
    }

    /**
     * Add a search provider
     *
     * @param string|MetaSearchProviderInterface $searchProvider
     */
    public function addProvider($provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @return MetaSearchProviderInterface[]
     */
    private function getSortedProviders(): array
    {
        $providers = $this->providers;

        foreach ($providers as $p => $provider) {
            if (is_string($provider)) {
                $providers[$p] = Yii::createObject(['class' => $provider]);
            }
        }

        usort($providers, function (MetaSearchProviderInterface $a, MetaSearchProviderInterface $b) {
            if ($a->getSortOrder() == $b->getSortOrder()) {
                return 0;
            } elseif ($a->getSortOrder() < $b->getSortOrder()) {
                return -1;
            } else {
                return 1;
            }
        });

        return $providers;
    }
}
