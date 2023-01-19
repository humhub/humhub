<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use Yii;
use yii\helpers\Url;

/**
 * Class ExportButton
 */
class ExportButton extends Widget
{
    /** @var string|null */
    public $filter = null;

    /**
     * This method is invoked right before the widget is executed.
     *
     * The method will trigger the [[EVENT_BEFORE_RUN]] event. The return value of the method
     * will determine whether the widget should continue to run.
     *
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeRun()
     * {
     *     if (!parent::beforeRun()) {
     *         return false;
     *     }
     *
     *     // your custom code here
     *
     *     return true; // or false to not run the widget
     * }
     * ```
     *
     * @return bool whether the widget should continue to be executed.
     * @since 2.0.11
     */
    public function beforeRun()
    {
        if (!parent::beforeRun()) {
            return false;
        }

        if ($this->filter === null) {
            return false;
        }

        return true;
    }


    /**
     * Executes the widget.
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        $params = [
            'csv' => Url::toRoute([
                'export',
                'format' => 'csv',
                $this->filter => Yii::$app->request->get($this->filter),
            ]),
            'xlsx' => Url::toRoute([
                'export',
                'format' => 'xlsx',
                $this->filter => Yii::$app->request->get($this->filter),
            ]),
        ];

        echo $this->render('exportButton', $params);
    }
}
