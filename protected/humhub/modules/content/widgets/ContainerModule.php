<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;

/**
 * ModuleCard shows a card with module data of Content Container
 *
 * @since 1.15
 * @author Luke
 */
class ContainerModule extends Widget
{
    public ContentContainerModule $module;
    public ContentContainerActiveRecord $contentContainer;

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
