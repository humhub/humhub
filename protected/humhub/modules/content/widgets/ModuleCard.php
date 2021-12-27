<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\components\Module;
use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * ModuleCard shows a card with module data of Content Container
 * 
 * @since 1.11
 * @author Luke
 */
class ModuleCard extends Widget
{

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @var Module
     */
    public $module;

    /**
     * @var string HTML wrapper around card
     */
    public $template;

    public function init()
    {
        parent::init();

        if (empty($this->template)) {
            $this->template = '<div class="card card-module col-lg-3 col-md-4 col-sm-6 col-xs-12">{card}</div>';
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $card = $this->render('moduleCard', [
            'module' => $this->module,
            'contentContainer' => $this->contentContainer,
        ]);

        return str_replace('{card}', $card, $this->template);
    }

}
