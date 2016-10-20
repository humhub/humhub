<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use Yii;
use yii\validators\Validator;
use URLify;
use humhub\modules\space\models\Space;

/**
 * UrlValidator for space urls
 *
 * @since 1.1
 * @author Luke
 */
class UrlValidator extends Validator
{

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (mb_strtolower($value) != URLify::filter($value, 45)) {
            $this->addError($model, $attribute, Yii::t('SpaceModule.manage', 'The url contains illegal characters!'));
        }
    }

    /**
     * Generate a unique space url
     * 
     * @param string $name
     * @return string a unique space url
     */
    public static function autogenerateUniqueSpaceUrl($name)
    {
        $maxUrlLength = 45;

        $url = URLify::filter($name, $maxUrlLength - 4);

        // Get a list of all similar space urls
        $existingSpaceUrls = [];
        foreach (Space::find()->where(['LIKE', 'url', $url . '%', false])->all() as $space) {
            $existingSpaceUrls[] = $space->url;
        }

        // Url is free
        if (!in_array($url, $existingSpaceUrls)) {
            return $url;
        }

        // Add number to taken url
        for ($i = 0, $existingSpaceUrlsCount = count($existingSpaceUrls); $i <= $existingSpaceUrlsCount; $i++) {
            $tryUrl = $url . ($i + 2);
            if (!in_array($tryUrl, $existingSpaceUrls)) {
                return $tryUrl;
            }
        }

        // Shouldn't never happen - failed
        return "";
    }

}
