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
        $title = preg_replace('/[\r\n\s]+/', ' ', strip_tags($title));
        return $this->cutStringToKeyword($title);
    }

    /**
     * Cut string to a word before first word contained the searched keyword
     *
     * @param string $string
     * @param int $maxWordNumberBeforeKeyword
     * @return string
     */
    private function cutStringToKeyword(string $string, int $maxWordNumberBeforeKeyword = 1): string
    {
        $index = stripos($string, $this->keyword);

        if ($index === false || $index < 40) {
            // Don't cut if the keyword is almost at the beginning
            return $string;
        }

        $wordNumber = 0;
        do {
            $index--;
            $subString = substr($string, $index);
            if ($subString[0] === ' ') {
                $wordNumber++;
            }
        } while ($index > 0 && $wordNumber <= $maxWordNumberBeforeKeyword);

        return ($index > 0 ? '...' : '') . trim($subString);
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
