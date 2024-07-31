<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\widgets\JsWidget;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

/**
 * PendingRegistrations shows a grid view of all open/pending UserInvites
 *
 * @since 1.8
 * @package humhub\modules\admin\widgets
 */
class PendingRegistrations extends JsWidget
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'admin.PendingRegistrations';

    /**
     * @var ActiveDataProvider
     */
    public $dataProvider;

    /**
     * @var PendingRegistrationSearch
     */
    public $searchModel;

    /**
     * The types of pending registrations
     * @var array
     */
    public $types;

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @inheritDoc
     */
    public function run()
    {
        return $this->render(
            'pending-registrations',
            [
                'dataProvider' => $this->dataProvider,
                'searchModel' => $this->searchModel,
                'types' => $this->types,
                'options' => $this->getOptions(),
            ],
        );
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'PendingRegistrations',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return [
            'url-resend-selected' => Url::to(['pending-registrations/resend-all-selected']),
            'url-resend-all' => Url::to(['pending-registrations/resend-all']),
            'note-resend-selected' => Yii::t('AdminModule.base', 'Resend to selected rows'),
            'note-resend-all' => Yii::t('AdminModule.base', 'Resend to all'),
            'url-delete-selected' => Url::to(['pending-registrations/delete-all-selected']),
            'url-delete-all' => Url::to(['pending-registrations/delete-all']),
            'note-delete-selected' => Yii::t('AdminModule.base', 'Delete selected rows'),
            'note-delete-all' => Yii::t('AdminModule.base', 'Delete all'),
        ];
    }
}
