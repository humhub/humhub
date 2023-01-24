<?php

namespace humhub\modules\notification\models\forms;

use humhub\modules\notification\models\Notification;
use Yii;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\ActiveQuery;

/**
 * Class FilterForm for filter notification list
 */
class FilterForm extends Model
{

    /**
     * Contains the current module filters
     * @var array
     */
    public $categoryFilter;

    /**
     * Contains all available module filter
     * @var array
     */
    public $categoryFilterSelection;

    /**
     * Contains all notifications by modulenames
     * @var array
     */
    public $notifications;

    /**
     * @var ActiveQuery|null
     */
    public $query;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categoryFilter'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'categoryFilter' => Yii::t('NotificationModule.base', 'Module Filter'),
        ];
    }

    /**
     * Preselects all possible module filter
     */
    public function init()
    {
        $this->categoryFilter = $this->getDefaultFilters();
    }

    /**
     * Default filter selection
     *
     * @return array
     */
    public function getDefaultFilters(): array
    {
        return array_keys($this->getCategoryFilterSelection());
    }

    /**
     * Returns all Notifications classes of modules not selected in the filter
     *
     * @return array
     */
    public function getExcludeClassFilter()
    {
        $result = [];

        foreach ($this->getNotifications() as $notification) {
            $categoryId = $notification->getCategory()->id;
            if (!in_array($categoryId, $this->categoryFilter)) {
                $result[] = $notification->className();
            }
        }
        return $result;
    }

    /**
     * Returns all available notification categories as checkbox list selection.
     * @return array
     */
    public function getCategoryFilterSelection(): array
    {
        if ($this->categoryFilterSelection == null) {
            $this->categoryFilterSelection = [];

            foreach (Yii::$app->notification->getNotificationCategories(Yii::$app->user->getIdentity()) as $category) {
                $this->categoryFilterSelection[$category->id] = $category->getTitle();
            }
        }
        return $this->categoryFilterSelection;
    }

    /**
     * Returns all available BaseNotification classes with a NotificationCategory.
     * @return array
     */
    public function getNotifications(): array
    {
        if ($this->notifications == null) {
            $this->notifications = array_filter(Yii::$app->notification->getNotifications(), function($notification) {
                return $notification->getCategory() != null;
            });
        }

        return $this->notifications;
    }

    /**
     * Checks if this filter is active (at least one filter selected)
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->categoryFilter != null;
    }

    /**
     * Creates the filter query
     *
     * @return ActiveQuery
     */
    public function createQuery(): ActiveQuery
    {
        if (isset($this->query)) {
            return $this->query;
        }

        $this->query = Notification::findGrouped();
        if ($this->hasFilter()) {
            $this->query->andFilterWhere(['not in', 'class', $this->getExcludeClassFilter()]);
        }

        return $this->query;
    }

    public function getPagination($pageSize): Pagination
    {
        $countQuery = clone $this->createQuery();
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => $pageSize
        ]);
        $this->query->offset($pagination->offset)->limit($pagination->limit);

        // Don't display thу help param in pagination url
        $pagination->params['reload'] = null;

        // Append the not default filter selection to the pagination urls
        if ($this->categoryFilter !== $this->getDefaultFilters()) {
            $pagination->params['FilterForm']['categoryFilter'] = $this->categoryFilter;
        }

        return $pagination;
    }

}
