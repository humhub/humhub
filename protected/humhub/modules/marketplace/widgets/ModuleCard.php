<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\models\Module;

/**
 * ModuleCard shows a card with module data
 *
 * @since 1.11
 * @author Luke
 */
class ModuleCard extends Widget
{
    public Module $module;

    /**
     * @var string HTML wrapper around card
     */
    public $template;

    public string $view;

    public function init()
    {
        parent::init();

        if (empty($this->template)) {
            $this->template = '<div class="card card-module col-lg-3 col-md-4 col-sm-6 col-xs-6" data-module="{moduleId}">{card}</div>';
        }

        if (empty($this->view)) {
            $this->view = $this->module->isInstalled()
                ? 'module-installed-card'
                : 'module-uninstalled-card';
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $card = $this->render($this->view, [
            'module' => $this->module,
        ]);

        return str_replace(['{card}', '{moduleId}'], [$card, $this->module->id], $this->template);
    }

}
