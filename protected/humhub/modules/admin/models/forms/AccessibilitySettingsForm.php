<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\components\Appearance;
use Yii;
use yii\base\Model;

/**
 * Administration → Appearance → Accessibility: the defaults of the users' accessibility options
 *
 * @since 1.20
 */
class AccessibilitySettingsForm extends Model
{
    public $contrast;
    public $motion;
    public $font;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $appearance = Yii::$app->appearance;
        $this->contrast = $appearance->getDefault(Appearance::CONTRAST);
        $this->motion = $appearance->getDefault(Appearance::MOTION);
        $this->font = $appearance->getDefault(Appearance::FONT) === Appearance::ON;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contrast', 'motion'], 'in', 'range' => [Appearance::SYSTEM, Appearance::OFF, Appearance::ON]],
            ['font', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'contrast' => Yii::t('AdminModule.settings', 'High contrast'),
            'motion' => Yii::t('AdminModule.settings', 'Reduce motion'),
            'font' => Yii::t('AdminModule.settings', 'Readable font'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'contrast' => Yii::t('AdminModule.settings', 'Stronger text and border colours, underlined links and a clearly visible focus.'),
            'motion' => Yii::t('AdminModule.settings', 'Turns off animations and transitions.'),
            'font' => Yii::t('AdminModule.settings', 'Lexend, a font designed for easier reading, also with dyslexia.'),
        ];
    }

    public function getOptions(): array
    {
        return [
            Appearance::SYSTEM => Yii::t('AdminModule.settings', 'Use system setting'),
            Appearance::OFF => Yii::t('AdminModule.settings', 'Off'),
            Appearance::ON => Yii::t('AdminModule.settings', 'On'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $appearance = Yii::$app->appearance;
        $appearance->setDefault(Appearance::CONTRAST, $this->contrast);
        $appearance->setDefault(Appearance::MOTION, $this->motion);
        $appearance->setDefault(Appearance::FONT, $this->font ? Appearance::ON : Appearance::OFF);

        return true;
    }
}
