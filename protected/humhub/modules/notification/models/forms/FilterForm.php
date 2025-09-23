<?php

namespace humhub\modules\notification\models\forms;

use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\models\Notification;
use Yii;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class FilterForm for filter notification list
 */
class FilterForm extends Model
{
    private const NO_CATEGORY_ID = 'others-no-category';

    /**
     * @var array|string|null Contains the current module filters
     */
    public array|string|null $categoryFilter = null;

    /**
     * @var string|null Contains the seen filter: 'all'|null, 'seen', 'unseen'
     */
    public ?string $seenFilter = null;

    /**
     * @var bool It is used only to select and unselect all filters on client side by JS
     */
    public bool $allFilter = true;

    /**
     * @var array|null Contains all available module filter
     */
    public ?array $categoryFilterSelection = null;

    private ?ActiveQuery $query = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categoryFilter'], 'safe'],
            [['seenFilter'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'allFilter' => Yii::t('NotificationModule.base', 'All'),
        ];
    }

    /**
     * Preselects all possible module filter
     * @inheritdoc
     */
    public function init()
    {
        $this->categoryFilter = $this->getDefaultFilters();
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);

        $this->allFilter = is_array($this->categoryFilter) && count($this->categoryFilter) === count($this->getCategoryFilterSelection());

        return $result;
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
     * Returns all Notifications classes of modules selected in the filter
     *
     * @return array
     */
    protected function getNotificationClasses(): array
    {
        $result = [];

        if (empty($this->categoryFilter)) {
            return $result;
        }

        foreach (Yii::$app->notification->getNotifications() as $notification) {
            $categoryId = $notification->getCategory() instanceof NotificationCategory
                ? $notification->getCategory()->id
                : self::NO_CATEGORY_ID;
            if (in_array($categoryId, $this->categoryFilter)) {
                $result[] = get_class($notification);
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
        if ($this->categoryFilterSelection === null) {
            $this->categoryFilterSelection = [];

            foreach (Yii::$app->notification->getNotificationCategories(Yii::$app->user->getIdentity()) as $category) {
                $this->categoryFilterSelection[$category->id] = $category->getTitle();
            }

            $this->categoryFilterSelection[self::NO_CATEGORY_ID] = Yii::t('NotificationModule.base', 'Others');
        }

        return $this->categoryFilterSelection;
    }

    /**
     * Checks if this filter is active (at least one filter selected)
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->categoryFilter !== null;
    }

    /**
     * Creates the filter query
     *
     * @return ActiveQuery
     */
    public function createQuery(): ActiveQuery
    {
        if ($this->query !== null) {
            return $this->query;
        }

        $this->query = Notification::findGrouped();
        if ($this->hasFilter()) {
            $notificationClasses = $this->getNotificationClasses();
            if (empty($notificationClasses)) {
                $this->query->andWhere(new Expression('FALSE'));
            } else {
                $this->query->andFilterWhere(['IN', 'notification.class', $notificationClasses]);
            }
        }
        if (!empty($this->seenFilter)) {
            $this->query->andFilterWhere(['notification.seen' => $this->seenFilter === 'seen' ? 1 : 0]);
        }

        return $this->query;
    }

    public function getPagination($pageSize): Pagination
    {
        $countQuery = clone $this->createQuery();
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => $pageSize,
        ]);
        $this->query->offset($pagination->offset)->limit($pagination->limit);

        // Don't display the help param in the pagination url
        $pagination->params['reload'] = null;

        // Append the not default filter selection to the pagination urls
        if ($this->categoryFilter !== $this->getDefaultFilters()) {
            $pagination->params['FilterForm']['categoryFilter'] = $this->categoryFilter;
        }
        if (!empty($this->seenFilter)) {
            $pagination->params['FilterForm']['seenFilter'] = $this->seenFilter;
        }

        return $pagination;
    }
}
