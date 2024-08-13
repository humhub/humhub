<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\models;

use humhub\modules\ui\form\interfaces\TabbedFormModel;
use yii\base\Model;

class TestTabbedFormModel extends Model implements TabbedFormModel
{
    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $email;

    /**
     * @var int
     */
    public $countryId;

    /**
     * @var int
     */
    public $stateId;

    /**
     * @var int
     */
    public $cityId;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'firstname' => 'First name',
            'lastname' => 'Last name',
            'email' => 'E-mail address',
            'countryId' => 'Country',
            'stateId' => 'State',
            'cityId' => 'City',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firstname', 'lastname', 'email'], 'string'],
            [['countryId', 'stateId', 'cityId'], 'integer'],
            [['email', 'countryId'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTabs(): array
    {
        return [
            [
                'label' => 'First tab',
                'view' => 'tab-first',
                'fields' => ['firstname', 'lastname', 'email'],
            ],
            [
                'label' => 'Second tab',
                'view' => 'tab-second',
                'fields' => ['countryId', 'stateId', 'cityId'],
            ]
        ];
    }
}