<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\components\Module;
use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * ModuleCard shows a card with module data of Content Container
 * 
 * @since 1.15
 * @author Luke
 */
class ContainerModule extends Widget
{
    public Module $module;

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('container-module', [
            'module' => $this->module,
            'contentContainer' => $this->contentContainer,
        ]);
    }

}
