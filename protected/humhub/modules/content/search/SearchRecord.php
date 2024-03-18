<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\interfaces\SearchRecordInterface;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use Yii;

/**
 * Search Record for Content
 *
 * @author luke
 * @since 1.16
 */
class SearchRecord implements SearchRecordInterface
{
    public ?Content $content = null;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): string
    {
        $record = $this->content->getPolymorphicRelation();

        return $record instanceof Post
            ? Image::widget(['user' => $this->content->createdBy, 'width' => 36, 'link' => false, 'hideOnlineStatus' => true])
            : Icon::get($record->getIcon() ?? 'comment', ['fixedWidth' => true])->asString();
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return RichText::output($this->content->getContentDescription(), ['record' => $this->content->getModel()]);
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        $description = [];

        $author = $this->content->createdBy;
        if ($author instanceof User) {
            $description[] = $author->getDisplayName();
        }

        $container = $this->content->container;
        if ($container instanceof ContentContainerActiveRecord && !$container->is($author)) {
            $description[] = $container->getDisplayName();
        }

        if ($this->content->created_at !== null) {
            $description[] = Yii::$app->formatter->asDate($this->content->created_at, 'short');
        }

        return implode(' &middot; ', $description);
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->content->getUrl();
    }
}
