<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use yii\helpers\Url;
use humhub\modules\content\permissions\CreatePublicContent;

/**
 * Visibility link for Wall Entries can be used to switch form public to private and vice versa.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 1.2
 */
class VisibilityLink extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $contentRecord;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = $this->contentRecord->content;
        $contentContainer = $content->container;
        
        if(!$content->canEdit()) {
            return;
        } else if($content->isPrivate() && !$contentContainer->permissionManager->can(new CreatePublicContent())) {
            return;
        }
        
        return $this->render('visibilityLink', [ 
                'content' => $content,
                'toggleLink' => Url::to(['/content/content/toggle-visibility', 'id' => $content->id])
        ]);
    }
}