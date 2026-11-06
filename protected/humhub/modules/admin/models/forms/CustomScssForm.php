<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\helpers\ScssHelper;
use RuntimeException;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use Yii;
use yii\base\Model;

/**
 * Administration → Appearance → Custom SCSS
 *
 * @since 1.20
 */
class CustomScssForm extends Model
{
    public $themeCustomScss;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->themeCustomScss = Yii::$app->settings->get('themeCustomScss');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['themeCustomScss', 'string'],
            ['themeCustomScss', 'trim'],
            [
                'themeCustomScss',
                'filter',
                'filter' => fn($value) => preg_replace(['/<style>/', '/<style type="text\/css">/', '/<\/style>/'], '', (string) $value),
            ],
            ['themeCustomScss', 'validateThemeCustomScss'],
        ];
    }

    public function validateThemeCustomScss($attribute): void
    {
        // Test Sass Variables and Maps extractor
        try {
            [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($this->themeCustomScss);
        } catch (SassException|RuntimeException $e) {
            $this->addError(
                $attribute,
                Yii::t('AdminModule.settings', 'Cannot compile SCSS to CSS:') . ' ' . $e->getMessage(),
            );
            return;
        }

        // Simulate how it will be compiled when building the CSS
        $scssSource = $customVariables . PHP_EOL . $customMaps . PHP_EOL . $otherCustomScss;

        try {
            $compiler = new Compiler();

            // Block any @import or @use on direct files
            $compiler->addImportPath(function (): void {
                throw new RuntimeException("Import blocked: only external URLs are allowed");
            });

            $compiler->compileString($scssSource)->getCss();
        } catch (SassException|RuntimeException $e) {
            $this->addError(
                $attribute,
                Yii::t('AdminModule.settings', 'Cannot compile SCSS to CSS:') . ' ' . $e->getMessage(),
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'themeCustomScss' => Yii::t('AdminModule.settings', 'Custom SCSS'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'themeCustomScss' => Yii::t('AdminModule.settings', 'Use Sassy CSS syntax (SCSS)'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->settings->set('themeCustomScss', $this->themeCustomScss);

        return true;
    }
}
