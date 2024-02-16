<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\interfaces\SearchProviderInterface;
use humhub\modules\content\search\ContentSearchProvider;
use humhub\modules\space\search\SpaceSearchProvider;
use humhub\modules\user\search\UserSearchProvider;
use yii\helpers\Url;

/**
 * SearchMenu Widget for TopMenuRightStack
 * @since 1.16
 */
class SearchMenu extends JsWidget
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.search';

    /**
     * @var string[]|SearchProviderInterface[] $searchProviders
     */
    protected array $searchProviders = [
        ContentSearchProvider::class,
        UserSearchProvider::class,
        SpaceSearchProvider::class
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('searchMenu', [
            'options' => $this->getOptions(),
            'searchProviders' => $this->searchProviders
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
     * @param string|SearchProviderInterface $searchProvider
     */
    public function addProvider($searchProvider)
    {
        $this->searchProviders[] = $searchProvider;
    }
}
