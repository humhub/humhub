<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\libs\Sort;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use yii\web\HttpException;

/**
 * WallCreateContentFormContainer is the container widget to create "quick" create content forms above Stream/Wall.
 *
 * @author luke
 */
class WallCreateContentFormContainer extends Widget
{
    /**
     * @var ContentContainerActiveRecord this content will belong to
     */
    public $contentContainer;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!($this->contentContainer instanceof ContentContainerActiveRecord)) {
            throw new HttpException(500, 'No Content Container given!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('@humhub/modules/content/widgets/views/wallCreateContentFormContainer', [
            'contentContainer' => $this->contentContainer,
            'formClass' => $this->getTopSortedFormClass(),
        ]);
    }

    /**
     * Get top sorted Form class
     *
     * @return string|null
     */
    public function getTopSortedFormClass(): ?string
    {
        $forms = [];
        foreach ($this->contentContainer->moduleManager->getContentClasses() as $content) {

            $wallEntryWidget = WallStreamEntryWidget::getByContent($content);
            if (!$wallEntryWidget) {
                continue;
            }

            if (!$wallEntryWidget->hasCreateForm()) {
                continue;
            }

            $forms[] = [
                'class' => $wallEntryWidget->createFormClass,
                'sortOrder' => $wallEntryWidget->createFormSortOrder ?? '9999999-' . $content->getContentName(),
            ];
        }

        if (empty($forms)) {
            return null;
        }

        Sort::sort($forms);
        $topForm = array_shift($forms);

        return $topForm['class'];
    }

}
