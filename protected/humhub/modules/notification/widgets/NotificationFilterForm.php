<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\widgets;

use humhub\components\Widget;
use humhub\modules\notification\models\forms\FilterForm;
use Yii;

/**
 * Notification Filter Form
 *
 * @since 1.16
 */
class NotificationFilterForm extends Widget
{
    public ?FilterForm $filterForm = null;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('notificationFilterForm', [
            'filterForm' => $this->filterForm,
            'seenFilters' => $this->getSeenFilters(),
        ]);
    }

    private function getSeenFilters(): array
    {
        return [
            '' => [
                'title' => Yii::t('NotificationModule.base', 'All'),
                'icon' => 'bars',
                'active' => empty($this->filterForm->seenFilter),
            ],
            'unseen' => [
                'title' => Yii::t('NotificationModule.base', 'Unseen'),
                'icon' => 'eye-slash',
                'active' => $this->filterForm->seenFilter === 'unseen',
            ],
            'seen' => [
                'title' => Yii::t('NotificationModule.base', 'Seen'),
                'icon' => 'eye',
                'active' => $this->filterForm->seenFilter === 'seen',
            ],
        ];
    }
}
