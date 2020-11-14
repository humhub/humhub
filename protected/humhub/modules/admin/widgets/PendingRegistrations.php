<?php


namespace admin\widgets;


use humhub\modules\admin\models\PendingRegistrationSearch;
use humhub\modules\wiki\helpers\Url;
use humhub\widgets\JsWidget;
use Yii;
use yii\data\ActiveDataProvider;

class PendingRegistrations extends JsWidget
{
    public $jsWidget = 'admin.PendingRegistrations';

    /** @var ActiveDataProvider */
    public $dataProvider;

    /** @var PendingRegistrationSearch */
    public $searchModel;

    /**
     * The types of pending registrations
     * @var array
     */
    public $types;

    public $init = true;

    public function run()
    {
        return $this->render('PendingRegistrationsWidgetView',
            [
                'dataProvider' => $this->dataProvider,
                'searchModel' => $this->searchModel,
                'types' => $this->types,
                'options' => $this->getOptions(),
            ]);
    }

    public function getAttributes()
    {
        return [
            'class' => 'PendingRegistrations'
        ];
    }

    public function getData()
    {
        return [
            'url-delete-selected' => Url::to(['pending-registrations/delete-all-selected']),
            'url-delete-all' => Url::to(['pending-registrations/delete-all']),
            'note-delete-selected' => Yii::t('AdminModule.base','Delete selected rows'),
            'note-delete-all' => Yii::t('AdminModule.base','Delete all'),
        ];
    }
}
