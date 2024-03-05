<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\interfaces\SearchRecordInterface;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\widgets\Image;

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
        $record = $this->content->getPolymorphicRelation();

        return $record instanceof Post
            ? $this->content->createdBy->displayName
            : $this->content->getContentDescription();
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        $record = $this->content->getPolymorphicRelation();
        $text = '';

        if (isset($record->description)) {
            $text = $record->description;
        } elseif (isset($record->message)) {
            $text = $record->message;
        } elseif (isset($record->page_content)) {
            $text = $record->page_content;
        } elseif (isset($record->text)) {
            $text = $record->text;
        } elseif (isset($record->article)) {
            $text = $record->article;
        }

        return $text === '' ? '' : RichText::output(strip_tags($text), ['record' => $record]);
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->content->getUrl();
    }
}
