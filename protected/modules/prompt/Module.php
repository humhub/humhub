<?php

namespace app\humhub\modules\prompt;

use Yii;
use yii\helpers\Url;
use humhub\components\Module as BaseModule;

class Module extends BaseModule
{
    /**
    * @inheritdoc
    */
    public function getConfigUrl()
    {
        return Url::to(['/prompt/admin']);
    }

    /**
    * @inheritdoc
    */
    public function disable()
    {
        // Cleanup all module data, don't remove the parent::disable()!!!
        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        if (Yii::$app instanceof \humhub\components\console\Application) {
            // Prevents the Yii HelpCommand from crawling all web controllers and possibly throwing errors at REST endpoints if the REST module is not available.
            $this->controllerNamespace = 'app\humhub\modules\prompt\commands';
        }
    }
}
