<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\form;

use humhub\components\icon\TablerIconProvider;
use humhub\widgets\Icon;
use kartik\select2\Select2;
use Yii;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;

/**
 * IconPicker form field widget
 *
 * Usage example:
 *
 * ```php
 *  <?= $activeForm->field($form, 'icon')->widget(IconPicker::class); ?>
 * ```
 *
 * The value is a Tabler icon name. A stored Font Awesome 4 name is preselected as the Tabler icon it
 * renders as and saved as such.
 *
 * @since 1.3
 */
class IconPicker extends Select2
{
    public $bsVersion = 5;

    /**
     * @var string optional icon provider id, defaults to the Tabler provider
     */
    public $lib;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->options['placeholder'] = Yii::t('base', 'Select icon');
        $this->theme = Select2::THEME_BOOTSTRAP;
        $this->pluginOptions = [
            'escapeMarkup' => new JsExpression('function (m) { return m; }'),
        ];
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->value = Icon::stripPrefix((string)$this->value) ?: null;

        if ($this->value !== null && $this->isTabler()) {
            $this->value = TablerIconProvider::resolveLegacyName($this->value);
        }

        return parent::run();
    }

    /**
     * @inheritdoc
     */
    public function renderInput()
    {
        $this->populateIconList();
        parent::renderInput();
    }

    /**
     * Populates the icon list.
     *
     * For the Tabler provider the names are handed to Select2 on the client, registered once per
     * page; only the current value is rendered as an option so it is preselected. Another provider
     * gets its icons rendered server-side, as before.
     */
    protected function populateIconList()
    {
        if (!$this->isTabler()) {
            foreach ($this->getIconNames() as $icon) {
                $this->data[$icon] = Icon::get(['name' => $icon, 'lib' => $this->lib]) . '&nbsp;&nbsp;' . $icon;
            }
            return;
        }

        $this->data = $this->value ? [$this->value => $this->value] : [];

        $this->getView()->registerJs(
            'window.humhubIconNames = ' . Json::encode(array_values($this->getIconNames())) . ';',
            View::POS_HEAD,
            'humhub-icon-names',
        );

        $template = new JsExpression('function (icon) { return icon.id ? \'<i class="ti ti-\' + icon.id + \'" aria-hidden="true"></i>&nbsp;&nbsp;\' + icon.text : icon.text; }');
        $this->pluginOptions['data'] = new JsExpression('window.humhubIconNames.map(function (name) { return {id: name, text: name}; })');
        $this->pluginOptions['templateResult'] = $template;
        $this->pluginOptions['templateSelection'] = $template;
    }

    /**
     * Returns a list of available icons
     *
     * @return array a list of icons
     */
    public function getIconNames()
    {
        return Icon::getNames($this->lib);
    }

    private function isTabler(): bool
    {
        return $this->lib === null || $this->lib === TablerIconProvider::ID;
    }
}
