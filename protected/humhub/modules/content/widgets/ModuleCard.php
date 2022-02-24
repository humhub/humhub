<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\admin\widgets\ModuleCard as AdminModuleCard;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * ModuleCard shows a card with module data of Content Container
 * 
 * @since 1.11
 * @author Luke
 */
class ModuleCard extends AdminModuleCard
{

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $card = $this->render($this->view, [
            'module' => $this->module,
            'contentContainer' => $this->contentContainer,
        ]);

        return str_replace('{card}', $card, $this->template);
    }

}
