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

        if ($licence->type === Licence::LICENCE_TYPE_PRO) {
            if (isset(Yii::$app->params['hosting'])) {
                return $this->render('about_version_pro_cloud', ['licence' => $licence]);
            } else {
                return $this->render('about_version_pro', ['licence' => $licence]);
            }
        } elseif ($licence->type === Licence::LICENCE_TYPE_EE) {
            return $this->render('about_version_ee');
        } else {
            return $this->render('about_version');
        }
    }

}
