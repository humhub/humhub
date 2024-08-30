<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\interfaces\MetaSearchResultInterface;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * Search Record for Content
 *
 * @author luke
 * @since 1.16
 */
class SearchRecord implements MetaSearchResultInterface
{
    public ?Content $content = null;
    public ?string $keyword = null;

    public function __construct(Content $content, ?string $keyword = null)
    {
        $this->content = $content;
        $this->keyword = $keyword;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): string
    {
        return Icon::get($this->content->getPolymorphicRelation()->getIcon() ?? 'comment', ['fixedWidth' => true])->asString();
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        $title = RichTextToHtmlConverter::process($this->content->getContentDescription());
        $title = str_replace(["\r", "\n"], ' ', strip_tags($title));

        // Cut text to first word contained the searched keyword
        $keywordIndex = strpos($title, $this->keyword);
        if ($keywordIndex) {
            do {
                $keywordIndex--;
                $titlePart = substr($title, $keywordIndex);
            } while ($keywordIndex && $titlePart[0] !== ' ');
            $title = '...' . trim($titlePart);
        }

        return $title;
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
        return Url::to(['/content/perma', 'id' => $this->content->id, 'highlight' => $this->keyword]);
    }
}
