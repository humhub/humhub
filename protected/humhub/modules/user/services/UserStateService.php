<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\components\StateService;
use humhub\events\EventWithTypedResult;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\interfaces\FilterableQueryInterface;
use humhub\interfaces\StatableInterface;
use humhub\libs\StateServiceApprovableTrait;
use humhub\libs\StateServiceEnablableTrait;
use humhub\libs\StateServiceSoftDeletableTrait;
use humhub\modules\content\models\Content;
use Yii;
use yii\db\ActiveRecord;

/**
 * This service is used to extend Content record for state features
 * @since 1.14
 */
class UserStateService extends StateService
{
    use StateServiceEnablableTrait;
    use StateServiceSoftDeletableTrait;
    use StateServiceApprovableTrait;

    /**
     * @var Content
     */
    public ActiveRecord $record;
    public string $field = 'status';

    public function initStates(): self
    {
        $this->allowState(StatableInterface::STATE_DISABLED);
        $this->allowState(StatableInterface::STATE_ENABLED);
        $this->allowState(StatableInterface::STATE_NEEDS_APPROVAL);
        $this->allowState(StatableInterface::STATE_DELETED);

        $this->defaultQueriedStates = [
            FilterableQueryInterface::FILTER_CONTEXT_DEFAULT => [StatableInterface::STATE_ENABLED,],
            ];

        return parent::initStates();
    }

    /**
     * @param array $config = ['withDeleted' => true]
     *
     * @return <int, string>[] state-indexed array of translated strings
     */
    public function getStateOptions(array $config = []): array
    {
        $withDeleted = filter_var($config['withDeleted'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($withDeleted === null) {
            throw new InvalidArgumentTypeException(__METHOD__, [1 => '$config["withDeleted"]'], ['bool', 'boolean string', 'int']);
        }

        $options = [
            StatableInterface::STATE_ENABLED => Yii::t('AdminModule.user', 'Enabled'),
            StatableInterface::STATE_DISABLED => Yii::t('AdminModule.user', 'Disabled'),
            StatableInterface::STATE_NEEDS_APPROVAL => Yii::t('AdminModule.user', 'Unapproved'),
        ];

        if ($withDeleted) {
            $options[StatableInterface::STATE_DELETED] = Yii::t('AdminModule.user', 'Deleted');
        }

        return EventWithTypedResult::create()
            ->setAllowedTypes(['array'])
            ->setValue($options)
            ->fire(self::EVENT_STATE_OPTIONS, $this)
            ->getValue();
    }
}
