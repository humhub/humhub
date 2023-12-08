<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\models\GlobalId;
use yii\db\ActiveQueryInterface;
use yii\db\BaseActiveRecord;

/**
 * @property GlobalId globalIdRecord
 */
trait GlobalIdTrait
{
    public function __get($name)
    {
        $value = parent::__get($name);

        if ($name === 'globalIdRecord' && !$value instanceof GlobalId) {
            $value = new GlobalId(['polymorphicRelation' => $this]);
            $this->populateRelation('globalIdRecord', $value);
        }

        return $value;
    }

    public function getGlobalIdRecord(): ActiveQueryInterface
    {
        return $this->hasOne(GlobalId::class, ['gid' => 'gid']);
    }

    public function getGuid(): ?string
    {
        return $this->globalIdRecord->guid;
    }

    public function setGuid(?string $guid)
    {
        if ($this->hasAttribute('guid')) {
            $this->guid = $guid;
        }

        $globalId = $this->globalIdRecord;

        if ($globalId->guid === $guid) {
            return $this;
        }

        $this->onAfterEditSaveGlobalId();

        return $globalId->guid = $guid;
    }

    /**
     * @param Event|null $event
     *
     * @return void
     * @internal
     */
    public function onAfterEditSaveGlobalId(?Event $event = null)
    {
        if ($event === null) {
            $event = $this->getIsNewRecord() ? BaseActiveRecord::EVENT_AFTER_INSERT : BaseActiveRecord::EVENT_AFTER_UPDATE;

            $this->on(
                $event,
                [$this, 'onAfterEditSaveGlobalId'],
                (object)['globalId' => $this->globalIdRecord, 'eventName' => $event]
            );

            return;
        }

        /** @var GlobalId $globalId */
        $globalId = $event->data->globalId;
        $globalId->save();

        $this->off($event->data->eventName, [$this, 'onAfterEditSaveGlobalId']);
    }
}
