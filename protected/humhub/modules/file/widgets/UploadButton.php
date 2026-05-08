<?php

namespace humhub\modules\file\widgets;

use humhub\helpers\Html;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\Module;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * UploadButtonWidget renders an upload button with integrated file input.
 * When multiple handlers are available, a dropdown is automatically rendered.
 *
 * @package humhub.modules_core.file.widgets
 * @since 1.2
 */
class UploadButton extends UploadInput
{
    /**
     * Additional button html options.
     */
    public array $buttonOptions = [];

    /**
     * Show button tooltip on mousover.
     */
    public bool $tooltip = true;

    /**
     * Tooltip position.
     */
    public string $tooltipPosition = 'bottom';

    /**
     * CSS class for the btn-group wrapper div shown when a dropdown is rendered.
     * @since 1.19
     */
    public string $cssDropdownButtonClass = 'btn-group';

    /**
     * Defines the button color class like btn-light, btn-primary
     */
    public string $cssButtonClass = 'btn-light';

    /**
     * Render as link instead of a button
     */
    public bool $asLink = false;

    /**
     * Either defines a label string or true to use the default label.
     * If set to false, no button label is printed.
     */
    public bool $label = false;

    /**
     * Handlers to show in the dropdown.
     * If null, Module::defaultFileHandlers will be used.
     * If an empty array is passed, no dropdown will be rendered.
     * @since 1.19
     * @var BaseFileHandler[]|null
     */
    public ?array $handlers = null;

    /**
     * Size of the button or the dropdown menu
     * Allowed values: null or Bootstrap size: 'sm' or 'lg'
     *
     * @since 1.19
     */
    public ?string $size = null;

    /**
     * If true the dropdown-menu will be aligned to the right (dropdown-menu-end).
     * @since 1.19
     */
    public bool $dropdownMenuEnd = false;

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

        if ($this->size) {
            $this->cssDropdownButtonClass .= ' btn-group-' . $this->size;
            $this->cssButtonClass .= ' btn-' . $this->size;
        }

        $handlers = $this->getResolvedHandlers();
        $isDropdown = count($handlers) > 1;

        $defaultButtonOptions = [
            'class'
                => trim($classPrefix . ' ' . $this->cssButtonClass . ' fileinput-button')
                . ($isDropdown ? ' d-none' : ''), // We need it for JS click, but don't want to show it if we have a dropdown with multiple handlers
            'title' => ($this->tooltip === true) ? Yii::t('FileModule.base', 'Upload files') : $this->tooltip,
            'data' => [
                'placement' => $this->tooltipPosition,
                'action-click' => "file.upload",
                'action-target' => '#' . $this->getId(true),
                'data-bs-toggle' => $this->tooltip ? 'tooltip' : null,
            ],
        ];

        $defaultButton = $this->render('uploadButton', [
            'input' => parent::run(),
            'options' => ArrayHelper::merge($defaultButtonOptions, $this->buttonOptions),
            'label' => $this->label,
        ]);

        if ($isDropdown) {
            $output = Html::beginTag('div', ['class' => $this->cssDropdownButtonClass]);

            $output .= $defaultButton; // For JS click to open the upload dialog

            // Single merged dropdown-toggle button (no split; clicking the icon opens the dropdown)
            $toggleAttrs = [
                'type' => 'button',
                'class' => trim('btn ' . $this->cssButtonClass . ' dropdown-toggle'),
                'data-bs-toggle' => 'dropdown',
                'aria-haspopup' => 'true',
                'aria-expanded' => 'false',
            ];
            $buttonLabel = Icon::get('cloud-upload')
                . ($this->label !== '' ? ' ' . $this->label : '');
            $output .= Html::tag('button', $buttonLabel, $toggleAttrs);

            $menuCssClass = 'dropdown-menu' . ($this->dropdownMenuEnd ? ' dropdown-menu-end' : '');
            $output .= Html::beginTag('ul', ['class' => $menuCssClass]);
            foreach ($handlers as $handler) {
                $output .= Html::beginTag('li');
                $output .= $this->renderHandlerLink($handler->getLinkAttributes());
                $output .= Html::endTag('li');
            }
            $output .= Html::endTag('ul');

            // In case of hidden file input in the DOM, keep it so JS can locate this container via #id-file-upload
            // and so the file.Upload widget is available for file_handler/service.js
            $output .= parent::run();

            $output .= Html::endTag('div');

            return $output;
        }

        return $defaultButton;
    }

    /**
     * Resolves the list of handlers to use. Falls back to Module::defaultFileHandlers when $handlers is null.
     *
     * @since 1.19
     * @return BaseFileHandler[]
     */
    protected function getResolvedHandlers(): array
    {
        if ($this->handlers !== null) {
            return $this->handlers;
        }

        /** @var Module $fileModule */
        $fileModule = Yii::$app->getModule('file');
        $handlers = [];
        foreach ($fileModule->defaultFileHandlers as $handlerClass) {
            $handlers[] = new $handlerClass();
        }
        return $handlers;
    }

    /**
     * Renders a file handler dropdown item link.
     *
     * @since 1.19
     * @param array $options the HTML attributes from BaseFileHandler::getLinkAttributes()
     * @return string
     */
    protected function renderHandlerLink(array $options): string
    {
        $options['data-action-process'] = 'file-handler';
        Html::addCssClass($options, 'dropdown-item');

        $label = ArrayHelper::remove($options, 'label', 'Label');

        if (isset($options['url'])) {
            $url = ArrayHelper::remove($options, 'url', '#');
            $options['href'] = $url;
        }

        return Html::tag('a', $label, $options);
    }
}
