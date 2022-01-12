<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
use humhub\components\OnlineModule;
use humhub\components\Widget;

/**
 * ModuleCard shows a card with module data
 * 
 * @since 1.11
 * @author Luke
 */
class ModuleCard extends Widget
{

    /**
     * @var Module|array
     */
    public $module;

    /**
     * @var string HTML wrapper around card
     */
    public $template;

    /**
     * @var string
     */
    public $view;

    public function init()
    {
        parent::init();

        if (empty($this->template)) {
            $this->template = '<div class="card card-module col-lg-3 col-md-4 col-sm-6 col-xs-12">{card}</div>';
        }

        if (empty($this->view)) {
            $this->view = 'moduleCard';
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $onlineModule = new OnlineModule(['module' => $this->module]);

        $card = $this->render($this->view, [
            'module' => $this->module,
            'isFeaturedModule' => $onlineModule->isFeatured,
        ]);

        return str_replace('{card}', $card, $this->template);
    }

}
