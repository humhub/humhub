<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * OEmbed settings form
 *
 * @package humhub.modules_core.admin.forms
 * @since 1.11
 */
class OEmbedSettingsForm extends Model
{

    /**
     * @var bool
     */
    public $requestConfirmation;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->requestConfirmation = (bool) Yii::$app->settings->get('oembed.requestConfirmation', true);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['requestConfirmation', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'requestConfirmation' => Yii::t('AdminModule.settings', 'Embedded content requires the user\'s consent to be loaded'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->settings->set('oembed.requestConfirmation', $this->requestConfirmation);

        return true;
    }

}
