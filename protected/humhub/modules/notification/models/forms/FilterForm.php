<?php

namespace humhub\modules\notification\models\forms;

use humhub\modules\notification\models\Notification;
use Yii;

class FilterForm extends \yii\base\Model
{

    /**
     * Contains the current module filters
     * @var type array
     */
    public $categoryFilter;

    /**
     * Contains all available module filter
     * @var type array
     */
    public $categoryFilterSelection;

    /**
     * Contains all notifications by modulenames
     * @var type
     */
    public $notifications;

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
        $this->categoryFilter = [];

        foreach ($this->getCategoryFilterSelection() as $moduleName => $title) {
            $this->categoryFilter [] = $moduleName;
        }
    }

    /**
     * Returns all Notifications classes of modules not selected in the filter
     *
     * @return type
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
     * @return type
     */
    public function getCategoryFilterSelection()
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
     * @return type
     */
    public function getNotifications()
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
     * @return type
     */
    public function hasFilter()
    {
        return $this->categoryFilter != null;
    }

    /**
     * Creates the filter query
     * @return \yii\db\ActiveQuery
     */
    public function createQuery()
    {
        $query = Notification::findGrouped();
        if($this->hasFilter()) {
            $query->andFilterWhere(['not in', 'class', $this->getExcludeClassFilter()]);
        }
        return $query;
    }

}
