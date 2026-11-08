<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\components\Appearance;
use humhub\modules\content\Module as ContentModule;
use Yii;
use yii\base\Model;

/**
 * Administration → Appearance → Accessibility: the defaults of the users' accessibility options,
 * and whether the richtext editor asks for the alternative text of an uploaded image
 *
 * @since 1.20
 */
class AccessibilitySettingsForm extends Model
{
    public $contrast;
    public $motion;
    public $font;
    public $imageAltText;

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
        $this->imageAltText = Yii::$app->getModule('content')->getImageAltTextMode();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contrast', 'motion'], 'in', 'range' => [Appearance::SYSTEM, Appearance::OFF, Appearance::ON]],
            ['font', 'boolean'],
            ['imageAltText', 'in', 'range' => array_keys($this->getImageAltTextOptions())],
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
            'imageAltText' => Yii::t('AdminModule.settings', 'Alternative text for images'),
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
            'imageAltText' => Yii::t('AdminModule.settings', 'Whether the editor asks to describe an uploaded image for people who cannot see it. If required, the image is only inserted with a description.'),
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

    public function getImageAltTextOptions(): array
    {
        return [
            ContentModule::IMAGE_ALT_TEXT_OFF => Yii::t('AdminModule.settings', 'Off'),
            ContentModule::IMAGE_ALT_TEXT_OPTIONAL => Yii::t('AdminModule.settings', 'Optional'),
            ContentModule::IMAGE_ALT_TEXT_REQUIRED => Yii::t('AdminModule.settings', 'Required'),
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
        Yii::$app->getModule('content')->setImageAltTextMode($this->imageAltText);

        return true;
    }
}
