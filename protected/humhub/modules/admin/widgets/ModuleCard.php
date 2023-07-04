<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
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
            $this->template = '<div class="module-row row">{card}</div>';
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
        $card = $this->render($this->view, [
            'module' => $this->module,
        ]);

        return str_replace('{card}', $card, $this->template);
    }

}
