<?php

namespace humhub\components\bootstrap;

use humhub\components\Theme;
use humhub\modules\marketplace\components\OnlineModuleManager;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Event;
use humhub\libs\UUID;
use humhub\components\ModuleManager;

/**
 * SystemRevision
 *
 * This component manages a global "signature" of the current system state.
 * It is used to invalidate frontend caches and synchronize multiple web nodes.
 *
 * The signature changes automatically when:
 * 1. HumHub Core is updated (Version mismatch).
 * 2. Modules are enabled, disabled, or removed.
 * 3. Theme settings are saved.
 */
class SystemRevision extends Component implements BootstrapInterface
{
    private const SETTING_KEY = 'system.rev.signature';
    private const SETTING_KEY_SEP = '|';
    private ?string $_publicSignature = null;

    public function bootstrap($app): void
    {
        $this->registerEventListeners();
    }

    private function registerEventListeners(): void
    {
        Event::on(ModuleManager::class, ModuleManager::EVENT_AFTER_MODULE_ENABLE, [$this, 'touch']);
        Event::on(ModuleManager::class, ModuleManager::EVENT_AFTER_MODULE_DISABLE, [$this, 'touch']);
        Event::on(OnlineModuleManager::class, OnlineModuleManager::EVENT_AFTER_UPDATE, [$this, 'touch']);

        Event::on(Theme::class, Theme::EVENT_AFTER_THEME_ACTIVATE, [$this, 'touch']);
    }

    public function getPublicSignature(): string
    {
        if ($this->_publicSignature === null) {
            $stored = Yii::$app->settings->get(self::SETTING_KEY);

            $parts = explode(static::SETTING_KEY_SEP, (string)$stored, 2);
            if (!isset($parts[1]) || $parts[0] !== Yii::$app->version) {
                // Generate new signature
                $parts = $this->touch();
            }

            $this->_publicSignature = $parts[1];
        }

        return $this->_publicSignature;
    }

    public function touch(): array
    {
        $newSignature = [Yii::$app->version, UUID::v4()];

        Yii::$app->settings->set(self::SETTING_KEY, implode(static::SETTING_KEY_SEP, $newSignature));
        $this->_publicSignature = null;

        return $newSignature;
    }
}
