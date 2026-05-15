<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\libs\ParameterEvent;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

/**
 * UserSourceCollection manages all configured UserSource instances.
 *
 * Registered as the 'userSourceCollection' application component.
 * Modules can add sources via the EVENT_BEFORE_USER_SOURCES_SET event.
 *
 * @since 1.19
 */
class UserSourceCollection extends Component
{
    /**
     * @event ParameterEvent raised before user sources are initialized.
     * Listeners may add entries to $event->parameters['userSources'].
     */
    public const EVENT_BEFORE_USER_SOURCES_SET = 'beforeUserSourcesSet';

    /**
     * @event Event raised after user sources have been initialized.
     */
    public const EVENT_AFTER_USER_SOURCES_SET = 'afterUserSourcesSet';

    /**
     * @var array raw source configs in format: 'sourceId' => [class => ..., ...]
     */
    private array $_sources = [];

    /**
     * Sets user sources from config. Called by Yii during component initialization.
     *
     * @param array $sources source configs keyed by source ID
     */
    public function setUserSources(array $sources): void
    {
        $event = new ParameterEvent(['userSources' => $sources]);
        $this->trigger(self::EVENT_BEFORE_USER_SOURCES_SET, $event);

        $this->_sources = array_merge(
            $this->getDefaultSources(),
            $event->parameters['userSources'],
            $this->_sources,
        );

        $this->trigger(self::EVENT_AFTER_USER_SOURCES_SET);
    }

    /**
     * Returns all user source instances.
     *
     * @return UserSourceInterface[]
     */
    public function getUserSources(): array
    {
        $sources = [];
        foreach ($this->_sources as $id => $_) {
            $sources[$id] = $this->getUserSource($id);
        }
        return $sources;
    }

    /**
     * Returns a user source instance by ID.
     *
     * @throws InvalidArgumentException if the source ID is unknown
     * @throws InvalidConfigException if the source cannot be instantiated
     */
    public function getUserSource(string $id): UserSourceInterface
    {
        if (!array_key_exists($id, $this->_sources)) {
            throw new InvalidArgumentException("Unknown user source '{$id}'.");
        }

        if (!($this->_sources[$id] instanceof UserSourceInterface)) {
            $this->_sources[$id] = $this->createUserSource($id, $this->_sources[$id]);
        }

        return $this->_sources[$id];
    }

    /**
     * Registers (or replaces) a user source at runtime.
     *
     * Accepts either an already-instantiated UserSourceInterface or a config array.
     * Modules use this from their bootstrap events (e.g. onAuthClientCollectionSet).
     */
    public function setUserSource(string $id, UserSourceInterface|array $source): void
    {
        $this->_sources[$id] = $source;
    }

    /**
     * Returns whether a user source with the given ID is configured.
     */
    public function hasUserSource(string $id): bool
    {
        return array_key_exists($id, $this->_sources);
    }

    /**
     * Finds the user source that claims responsibility for creating a user
     * from the given auth client.
     *
     * Dispatch:
     *  1. Non-local sources first — they ask {@see UserSourceInterface::claimsUserCreation()}
     *     which may inspect $attributes (e.g. LDAP verifies the user exists
     *     in the directory before claiming a foreign auth client).
     *  2. LocalUserSource is checked last, only as the explicit fallback.
     *
     * The attribute argument is optional — callers that only need to identify
     * the canonical source for an auth client ID (e.g. listing users) may omit
     * it, in which case the default {@see BaseUserSource::claimsUserCreation()}
     * behaves like the original ID-only match.
     */
    public function findUserSourceForAuthClient(string $authClientId, array $attributes = []): UserSourceInterface
    {
        foreach ($this->getUserSources() as $source) {
            if ($source->getId() === 'local') {
                continue;
            }
            if ($source->claimsUserCreation($authClientId, $attributes)) {
                return $source;
            }
        }

        $local = $this->getLocalUserSource();
        if ($local->claimsUserCreation($authClientId, $attributes)) {
            return $local;
        }
        return $local;
    }

    /**
     * Returns whether any registered UserSource explicitly lists the given
     * AuthClient ID in its allowedAuthClientIds. Used to detect "trusted"
     * providers that may auto-register users without the anonymousRegistration
     * setting being enabled.
     */
    public function isAuthClientClaimed(string $authClientId): bool
    {
        foreach ($this->getUserSources() as $source) {
            if (in_array($authClientId, $source->getAllowedAuthClientIds(), true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the LocalUserSource instance.
     */
    public function getLocalUserSource(): LocalUserSource
    {
        return $this->getUserSource('local');
    }

    /**
     * Instantiates a user source from its config array.
     */
    protected function createUserSource(string $id, array $config): UserSourceInterface
    {
        $config['id'] = $id;
        return Yii::createObject($config);
    }

    protected function getDefaultSources(): array
    {
        return [
            'local' => ['class' => LocalUserSource::class],
        ];
    }
}
