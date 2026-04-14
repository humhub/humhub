<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\models\Licence;
use humhub\modules\marketplace\Module;
use Yii;

class AboutVersion extends Widget
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        $licence = $module->getLicence();

        $this->view->registerCss('.hh-about-logo { width: 96px }'
            . '@media (max-width: 576px) { .hh-about-logo { width: 64px } }');

        if ($licence->type === Licence::LICENCE_TYPE_PRO) {
            $view = isset(Yii::$app->params['hosting']) ? 'about_version_pro_cloud' : 'about_version_pro';
            return $this->render($view, ['licence' => $licence]);
        }

        return $this->render('about_version');
    }

}
