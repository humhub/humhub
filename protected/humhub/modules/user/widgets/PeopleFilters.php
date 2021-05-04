<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use Yii;

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
            'order' => Yii::$app->request->get('order', ''),
        ];

        return $this->render('peopleFilters', [
            'filters' => $filters,
        ]);
    }

}
