<?php

namespace humhub\modules\file\widgets;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * UploadButtonWidget renders an upload button with integrated file input.
 *
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class UploadButton extends UploadInput
{
    /**
     * Additional button html options.
     * @var array
     */
    public $buttonOptions = [];

    /**
     * Show button tooltip on mousover.
     * @var bool
     */
    public $tooltip = true;

    /**
     * Tooltip position.
     * @var string
     */
    public $tooltipPosition = 'bottom';

    /**
     * Defines the button color class like btn-light, btn-primary
     * @var string
     */
    public $cssButtonClass = 'btn-light';

    /**
     * Render as link instead of a button
     * @var bool
     */
    public $asLink = false;

    /**
     * Either defines a label string or true to use the default label.
     * If set to false, no button label is printed.
     * @var bool
     */
    public $label = false;

    /**
     * Draws the Upload Button output.
     */
    public function run()
    {
        if ($this->label === true) {
            $this->label = '&nbsp;' . Yii::t('base', 'Upload');
        } elseif ($this->label === false) {
            $this->label = '';
        } else {
            $this->label = '&nbsp;' . $this->label;
        }

        $classPrefix = 'btn';
        if ($this->asLink) {
            $classPrefix = '';
            if ($this->cssButtonClass === 'btn-light') {
                $this->cssButtonClass = '';
            }
        }

        $classSuffix = 'fileinput-button ' . ($this->tooltip ? ' tt' : '');

        $defaultButtonOptions = [
            'class' => trim($classPrefix . ' ' . $this->cssButtonClass . ' ' . $classSuffix),
            'title' => ($this->tooltip === true) ? Yii::t('FileModule.base', 'Upload files') : $this->tooltip,
            'data' => [
                'placement' => $this->tooltipPosition,
                'action-click' => "file.upload",
                'action-target' => '#' . $this->getId(true),
            ],
        ];

        $options = ArrayHelper::merge($defaultButtonOptions, $this->buttonOptions);

        return $this->render('uploadButton', [
            'input' => parent::run(),
            'options' => $options,
            'label' => $this->label,
        ]);
    }
}
