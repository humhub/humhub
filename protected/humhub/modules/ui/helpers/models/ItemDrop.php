<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: davidborn
 */

namespace humhub\modules\ui\helpers\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\TableSchema;
use yii\db\Transaction;
use yii\db\Expression;

/**
 * Class ItemDrop
 * @since 1.4
 */
abstract class ItemDrop extends Model
{
    /**
     * @var ActiveRecord the model to resort
     */
    private $model;

    /**
     * @var string ActiveRecord model class
     */
    public $modelClass;

    /**
     * @var integer
     */
    public $modelId;

    /**
     * @var integer new model index
     */
    public $index;

    /**
     * @var string form submit name
     */
    public $formName = 'ItemDrop';

    /**
     * @var integer|null the id of the target used for dragging items between lists
     */
    public $targetId;

    /**
     * @var string|null a targetId field of the model e.g. list_id
     */
    public $targetIdField;

    /**
     * @var ActiveQuery can be set instead of overwritting [getSortItemsQuery()]
     * @see getSortItemsQuery()
     */
    public $sortQuery;

    /**
     * @var string the sort order field of the model
     */
    public $sortOrderField = 'sort_order';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'index', 'targetId'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return $this->formName;
    }

    /**
     * Handles the resorting of the list
     * @return bool
     */
    public function save()
    {
        try {
            $this->moveItemIndex();
            return true;
        } catch (\Throwable $e) {
            Yii::error($e);
        }

        return false;
    }

    /**
     * Moves the given
     * @param $id
     * @param $newIndex
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    protected function run()
    {

        /** @var $transaction Transaction */
        $transaction = $this->beginTransaction();

        try {
            $model = $this->getModel();
            $tableName = $this->getTableName();

            // Load all items to sort and exclude the model we want to resort
            $itemsToSort = $this->getSortItemsQuery()->andWhere(['!=', $tableName.'.id', $this->id])->all();

            $newIndex = $this->validateIndex($this->index, $itemsToSort);

            if($this->getSortOrder($model) === $newIndex) {
                return true;
            }

            $this->updateTarget();

            // Add our model to the new index
            array_splice($itemsToSort, $newIndex, 0, [$model]);

            foreach ($itemsToSort as $index => $item) {
                $this->updateSortOrder($item, $index);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return Transaction
     */
    protected function beginTransaction()
    {
        return call_user_func($this->modelClass.'::getDb')->beginTransaction();
    }

    /**
     * Makes sure we use a valid sort index.
     *
     * @param $newIndex
     * @param $itemsToSort
     * @return int
     */
    protected function validateIndex($newIndex, $itemsToSort)
    {
        if ($newIndex < 0) {
            return 0;
        } else if ($newIndex >= count($itemsToSort) + 1) {
           return count($itemsToSort) - 1;
        }

        return $newIndex;
    }

    /**
     * Returns the sort order (index) value of an model (by default a sort_order field).
     *
     * This may be overwritten if the model uses another sort order field.
     *
     * @param $model
     * @return mixed
     */
    protected function getSortOrder(ActiveRecord $model)
    {
        return $model->${$this->sortOrderField};
    }

    /**
     * Updates the sort_order (index) of the given model instance (by default a sort_order field).
     *
     * This may be overwritten if the model uses another sort order field.
     *
     * @param $model
     * @return mixed
     */
    protected function updateSortOrder(ActiveRecord $model, $sortOrder)
    {
        return $model->updateAttributes([$this->sortOrderField => $sortOrder]);
    }

    /**
     * @return string returns the table name of the model
     */
    protected function getTableName()
    {
        /* @var $schema TableSchema */
        $schema = call_user_func($this->modelClass.'::getTableSchema');
        return $schema->fullName;
    }

    /**
     * Loads and caches the model instance to resort.
     *
     * @param $id mixed
     * @return ActiveRecord
     */
    protected function getModel()
    {
        if(!$this->model) {
            $this->model = $this->loadModel();
        }

        return $this->model;
    }

    /**
     * Loads the model instance to resort
     * @param $id
     * @return ActiveRecord
     */
    protected function loadModel()
    {
        return call_user_func($this->modelClass.'::findOne', ['id' => $this->id]);
    }

    /**
     * Returns a query responsible for loading all items to resort.
     * This this can be for example all models within a list:
     *
     * ```
     * return MyItem::find()->where(['listId' => $this->targetId]);
     * ```
     *
     * @return ActiveQuery
     */
    protected function getSortItemsQuery() {
        if($this->sortQuery) {
            return $this->sortQuery;
        }

        $query = call_user_func($this->modelClass.'::find');
        if($this->targetIdField) {
            $query->where([$this->targetIdField => $this->targetId]);
        }

        return $query;
    }

    /**
     * Responsible for updating the target on the model e.g:
     */
    protected function updateTarget() {
        if($this->targetIdField) {
            $targetId = $this->targetId ? $this->targetId : new Expression('NULL');
            $this->getModel()->updateAttributes([$this->targetIdField => $targetId]);
        }
    }
}
