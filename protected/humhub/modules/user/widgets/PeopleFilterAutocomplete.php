<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\ui\form\widgets\BaseAutocompleteInput;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use yii\helpers\Url;

/**
 * This BaseAutocompleteInput provides a PeopleFilterAutocomplete
 */
class PeopleFilterAutocomplete extends BaseAutocompleteInput
{
    /**
     * @inheritdoc
     */
    public $minInput = 1;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->url = Url::to(['/user/people/search-people-json']);
    }

    /**
     *
     * @param $field
     * @param null $keyword
     * @return mixed
     */
    public function getAutocompleteSuggestions($keyword = null)
    {
        $keyword = trim($keyword);

        if (empty($keyword)) {
            return [];
        }

        $query = User::find()->alias('u')
            ->joinWith(Profile::tableName() . ' p')
            ->where([
                'OR',
                ['LIKE', 'u.username', $keyword],
                ['LIKE', 'p.firstname', $keyword],
                ['LIKE', 'p.lastname', $keyword]
            ]);

        $items = [];

        foreach ($query->each() as $item) {
            $text = $item->profile->firstname . ' ' . $item->profile->lastname;

            $items[] = [
                'text' => $text,
                'html' => $this->render('@humhub/modules/user/widgets/views/peopleFilterAutocompleteItem', [
                    'fullname' => $text,
                    'username' => $item->username,
                    'image' => $item->getProfileImage()->getUrl()
                ])
            ];
        }

        return $items;
    }
}
