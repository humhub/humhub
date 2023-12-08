<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\libs\DbDateValidator;
use humhub\modules\content\models\Content;
use yii\base\Component;

/**
 * This service is used to extend an implementing record for state features
 *
 * @since 1.16
 */
class StateService extends Component
{
    public const EVENT_INIT = 'init';

    public Content $content;

    protected array $states = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->allowState(Content::STATE_PUBLISHED);
        $this->allowState(Content::STATE_DRAFT);
        $this->allowState(Content::STATE_SCHEDULED);
        $this->allowState(Content::STATE_DELETED);

        $this->trigger(self::EVENT_INIT);
    }

    /**
     * Allow a state for the Content
     *
     * @param int $state
     */
    public function allowState(int $state)
    {
        if (!in_array($state, $this->states, true)) {
            $this->states[] = $state;
        }
    }

    /**
     * Exclude a state from the allowed list
     *
     * @param int $state
     */
    public function denyState(int $state)
    {
        $stateIndex = array_search($state, $this->states);
        if ($stateIndex !== false) {
            unset($this->states[$stateIndex]);
        }
    }

    /**
     * Check if the Content has the requested state
     *
     * @param int|string|null $state
     *
     * @return bool
     */
    public function is($state): bool
    {
        // Always convert to integer before comparing,
        // because right after save the content->state may be a string
        return (int)$this->content->state === (int)$state;
    }

    /**
     * Check if the requested state can be set to the Content
     *
     * @param int|string|null $state
     *
     * @return bool
     */
    public function canChange($state): bool
    {
        return in_array((int)$state, $this->states);
    }

    /**
     * Set new state
     *
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     */
    public function set($state, array $options = []): bool
    {
        $state = (int)$state;

        if (!$this->canChange($state)) {
            return false;
        }

        if ($state === Content::STATE_SCHEDULED) {
            if (empty($options['scheduled_at'])) {
                return false;
            }

            $this->content->scheduled_at = $options['scheduled_at'];
            (new DbDateValidator())->validateAttribute($this->content, 'scheduled_at');
            if ($this->content->hasErrors('scheduled_at')) {
                $this->content->scheduled_at = null;
                return false;
            }
        }

        $this->content->setAttribute('state', $state);
        return true;
    }

    /**
     * Set and save new state for the Content
     *
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     */
    public function update($state, array $options = []): bool
    {
        return $this->set($state, $options) && $this->content->save();
    }
}
