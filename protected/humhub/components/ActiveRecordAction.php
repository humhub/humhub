<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\interfaces\EditableInterface;
use humhub\libs\Helpers;
use humhub\libs\ObjectModel;
use humhub\modules\content\interfaces\ContentOwner;
use Throwable;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

class ActiveRecordAction extends Action
{
    public const EVENT_AFTER_LOAD_RECORD = 'after-load-record';
    public const EVENT_BEFORE_LOAD_RECORD = 'before-load-record';
    public const EVENT_NO_RECORD_FOUND = 'no-record-found';

    /**
     * The record to whom this files belongs to.
     * Optional, since "free" files can also be attached to a record later.
     *
     * @var BaseActiveRecord|\humhub\components\ActiveRecord|null the records
     */
    public ?BaseActiveRecord $record = null;

    /**
     * Determines whether or not teh record is attached automatically to the
     *
     * @var bool
     */
    public bool $autoAttach = true;

    public function init()
    {
        $this->on(self::EVENT_INIT_NO_CONFIG_DETECTION, [$this, 'loadRecord'], null, false);

        return parent::init();
    }


    /**
     * Loads the target record by request parameter if defined.
     * The default implementation only supports uploads to ContentActiveRecord or ContentAddonActiveRecords.
     *
     * @throws Throwable
     */
    protected function loadRecord(?Event $e = null)
    {
        $result = $this->filterValue(self::EVENT_BEFORE_LOAD_RECORD, null, [ActiveRecord::class => true, ObjectModel::class]);

        switch (true) {
            case $result instanceof ActiveRecord:
                $record = $result;
            break;

            case $result instanceof ObjectModel && $record = $result->getObject():
                break;

            default:
                $record = null;
                $pk = null;

                if ($model = $this->getGet('objectModel')) {
                    $pk = $this->getGet('objectId');
                } elseif ($model = $this->getPost('objectModel')) {
                    $pk = $this->getPost('objectId');
                }

                $result = $this->filterValue(self::EVENT_BEFORE_LOAD_RECORD, null, [ActiveRecord::class => true]);
                $this->trigger(
                    self::EVENT_BEFORE_LOAD_RECORD,
                    $event = new EventWithTypedResult([
                    'result' => (object)[
                        'object_model' => $model,
                        'object_id' => $pk
                    ]
                    ])
                );

                if ($event->value instanceof ActiveRecord) {
                    $record = $event->value;
                } else {
                    if (is_array($event->value)) {
                        $criteria = $event->value;
                    } else {
                        $pk = $event->value->object_id;
                        $model = $event->value->object_model;
                        $criteria = ['id' => $pk];
                    }

                    if ($pk && Helpers::CheckClassType($model, ActiveRecord::class)) {
                        $record = $model::findOne($criteria);
                    }
                }

                if (!$record instanceof ActiveRecord) {
                    $record = $this->filterValue(self::EVENT_NO_RECORD_FOUND, null, [ActiveRecord::class]);
                }
        }

        $record = $this->filterValue(self::EVENT_AFTER_LOAD_RECORD, $record, [ActiveRecord::class]);

        if ($record instanceof ContentOwner) {
            if (!$record->content->canEdit()) {
                $record = null;
            }
        } elseif ($record instanceof EditableInterface) {
            if (!$record->canEdit()) {
                $record = null;
            }
        }

        return $this->record = $record;
    }
}
