<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use Yii;

/**
 * SpacesFilters displays the filters on the directory spaces page
 *
 * @since 1.9
 * @author Luke
 */
class SpacesFilters extends Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('spacesFilters');
    }

    public static function getDefaultValue(string $filter): string
    {
        switch ($filter) {
            case 'sort':
                return 'name';
        }

        return '';
    }

    public static function getValue(string $filter)
    {
        $defaultValue = self::getDefaultValue($filter);

        return Yii::$app->request->get($filter, $defaultValue);
    }

    public static function getSortingOptions(): array
    {
        return [
            'name' => Yii::t('SpaceModule.base', 'Name'),
            'newer' => Yii::t('SpaceModule.base', 'Newer spaces'),
            'older' => Yii::t('SpaceModule.base', 'Older spaces'),
        ];
    }

    public static function getConnectionOptions(): array
    {
        return [
            '' => Yii::t('SpaceModule.base', 'All'),
            'member' => Yii::t('SpaceModule.base', 'Member'),
            'follow' => Yii::t('SpaceModule.base', 'Follow'),
        ];
    }

}
