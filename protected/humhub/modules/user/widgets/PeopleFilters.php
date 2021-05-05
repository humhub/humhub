<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\modules\admin\models\forms\PeopleSettingsForm;
use Yii;
use yii\base\BaseObject;

/**
 * PeopleFilters displays the filters on the directory people page
 *
 * @since 1.9
 * @author Luke
 */
class PeopleFilters extends Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        $filters = [
            'keyword' => Yii::$app->request->get('keyword', ''),
            'order' => self::getOrder(),
        ];

        return $this->render('peopleFilters', [
            'filters' => $filters,
        ]);
    }

    public static function getOrder(): string
    {
        return Yii::$app->request->get('order', PeopleCard::config('defaultSorting'));
    }

}
