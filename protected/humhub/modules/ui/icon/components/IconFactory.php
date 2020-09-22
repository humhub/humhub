<?php
namespace humhub\modules\ui\icon\components;

use Yii;
use yii\base\Component;

/**
 * IconFactory handles the registration and access of IconProviders.
 *
 * Modules may register additional IconProviders or overwrite the default IconProvider by means of [[registerProvider()]]
 * within the [[EVENT_AFTER_INIT]] event.
 *
 * If an IconProvider does only a subset of all icon names the [[IconProvider::render]] function should return null. In
 * this case the IconFactory will fall back to an [[fallbackProvider]].
 *
 * By default the FontAwesomeIconProvider is set as default provider.
 *
 * @see DevtoolsIconProvider
 * @since 1.4
 */
class IconFactory  extends Component
{

    /**
     * @event \yii\base\Event triggered after init, can be used to overwrite the [[defaultProvider]]
     */
    const EVENT_AFTER_INIT = 'afterInit';

    /**
     * @var IconProvider
     */
    private static $defaultProvider;

    /**
     * @var IconProvider
     */
    public static $fallbackProvider;

    /**
     * @var [] array  of IconProvider instances associated with a provider id
     */
    private static $provider = [];

    /**
     * @var IconFactory singleton instance
     */
    private static $instance;

    /**
     * @return IconFactory singleton instance
     * @throws \yii\base\InvalidConfigException
     */
    public static function getInstance()
    {
        if(!static::$instance) {
            static::$instance = Yii::createObject(['class' => static::class]);
        }

        return static::$instance;
    }

    /**
     * Adds a provider to this factory, if `$isDefault` is set to true the current default IconProvider
     * will be overwritten.
     *
     * @param IconProvider $instance
     */
    public static function registerProvider(IconProvider $instance, $isDefault = false)
    {
        static::$provider[$instance->getId()] = $instance;
        if($isDefault) {
            static::$defaultProvider = $instance;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $fontAwesomeIconProvider = new FontAwesomeIconProvider();
        static::registerProvider($fontAwesomeIconProvider, true);
        static::$fallbackProvider = $fontAwesomeIconProvider;
        $this->trigger(static::EVENT_AFTER_INIT);
    }

    /**
     * @param $icon
     * @param array $options
     * @see IconProvider::render()
     * @return mixed
     */
    public function render($icon, $options = [])
    {
        $result = $this->getProvider($icon->lib)->render($icon, $options);
        if(empty($result)) {
            $result = static::$fallbackProvider->render($icon, $options);
        }

        return $result;
    }

    /**
     * @param string|null $providerId icon provider id
     * @return IconProvider
     */
    public function getProvider($providerId = null)
    {
        if(empty($providerId)) {
            return static::$defaultProvider;
        }

        if(!isset(static::$provider[$providerId])) {
            Yii::warning(Yii::t('UiModule.icon', 'No icon provider registered for provider id {id}', ['id' => $providerId]));
            return static::$defaultProvider;
        }

        return static::$provider[$providerId];
    }

    /**
     *
     * @param $listDefinition
     * @param null $providerId
     * @see IconProvider::renderList()
     * @return mixed
     */
    public function renderList($listDefinition, $providerId = null)
    {
        $result = $this->getProvider($providerId)->renderList($listDefinition);
        if(empty($result)) {
            $result = static::$fallbackProvider->renderList($listDefinition);
        }

        return $result;
    }

    /**
     * Returns the supported icon names of the IconProvider
     *
     * @param null $providerId
     * @see IconProvider::getNames()
     * @return string[]
     */
    public function getNames($providerId = null)
    {
        return $this->getProvider($providerId)->getNames();
    }
}