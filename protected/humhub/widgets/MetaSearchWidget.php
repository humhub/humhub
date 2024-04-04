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
        SpaceSearchProvider::class
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('metaSearch', [
            'options' => $this->getOptions(),
            'providers' => $this->providers
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
}
