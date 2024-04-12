<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\interfaces\MetaSearchProviderInterface;
use Yii;

/**
 * Meta Search Provider Widget
 * @since 1.16
 */
class MetaSearchProviderWidget extends Widget
{
    /**
     * @var string|MetaSearchProviderInterface|null $provider
     */
    public $provider;

    public ?array $params = null;

    public ?string $keyword = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initProvider();
    }

    protected function initProvider()
    {
        if ($this->provider instanceof MetaSearchProviderInterface) {
            return;
        }

        if (is_string($this->provider)) {
            $this->provider = Yii::createObject([
                'class' => $this->provider,
                'params' => $this->params,
                'keyword' => $this->keyword,
            ]);

            if ($this->provider instanceof MetaSearchProviderInterface) {
                $this->provider->getService()->search();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun() && $this->provider instanceof MetaSearchProviderInterface;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('metaSearchProvider', [
            'options' => $this->getOptions(),
            'provider' => $this->provider,
        ]);
    }

    protected function getOptions(): array
    {
        $params = $this->provider->getParams();

        return [
            'class' => 'search-provider' . ($this->provider->getService()->isSearched() ? ' provider-searched' : ''),
            'data-provider' => get_class($this->provider),
            'data-provider-params' => empty($params) || !is_array($params) ? '' : json_encode($params),
            'data-hide-on-empty' => $this->provider->getIsHiddenWhenEmpty(),
        ];
    }
}
