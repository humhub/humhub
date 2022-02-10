<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\content\models\ContentContainerTag;
use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\BaseAutocompleteInput;
use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * This BaseAutocompleteInput provides a ProfileSettingsAutocomplete
 */
class ProfileSettingsAutocomplete extends BaseAutocompleteInput
{
    /**
     * @var string
     */
    public $itemTextKey;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->url = Url::to(['/user/account/search-settings-json', 'field' => $this->itemTextKey]);
    }

    /**
     *
     * @param $field
     * @param null $keyword
     * @return mixed
     */
    public function getAutocompleteSuggestions($field, $keyword = null)
    {
        $keyword = trim($keyword);

        if (empty($keyword)) {
            return [];
        }

        $query = Profile::find()
            ->where(['LIKE', $field, $keyword])
            ->limit(100);

        $items = [];

        foreach ($query->each() as $item) {
            $items[] = ['text' => $item->$field];
        }

        return $items;
    }
}
