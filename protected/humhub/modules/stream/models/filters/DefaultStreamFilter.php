<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use Yii;
use yii\db\Query;
use humhub\modules\content\models\Content;

class DefaultStreamFilter extends StreamQueryFilter
{
    /**
     * Default filters
     */
    const FILTER_FILES = "entry_files";
    const FILTER_ARCHIVED = "entry_archived";
    const FILTER_MINE = "entry_mine";
    const FILTER_INVOLVED = "entry_userinvolved";
    const FILTER_PRIVATE = "visibility_private";
    const FILTER_PUBLIC = "visibility_public";

    /**
     * Array of stream filters to apply to the query.
     * There are the following filter available:
     *
     *  - 'entry_files': Filters content with attached files
     *  - 'entry_mine': Filters only content created by the query $user
     *  - 'entry_userinvovled': Filter content the query $user is involved
     *  - 'visibility_private': Filter only private content
     *  - 'visibility_public': Filter only public content
     *
     * @var array
     */
    public $filters = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filters'], 'safe']
        ];
    }

    public function init()
    {
        $this->filters = $this->streamQuery->filters;
        parent::init();
        $this->filters = (is_string($this->filters)) ? [$this->filters] : $this->filters;
    }

    public function apply()
    {
        if ($this->isFilterActive(self::FILTER_FILES)) {
            $this->filterFile();
        }

        // Only apply archived filter when we should load more than one entry
        if (!$this->streamQuery->isSingleContentQuery() && !$this->isFilterActive(self::FILTER_ARCHIVED)) {
            $this->unFilterArchived();
        }

        // Show only mine items
        if ($this->isFilterActive(self::FILTER_MINE)) {
            $this->filterMine();
        }

        // Show only items where the current user is invovled
        if ($this->isFilterActive(self::FILTER_INVOLVED)) {
            $this->filterInvolved();
        }

        // Visibility filters
        if ($this->isFilterActive(self::FILTER_PRIVATE)) {
            $this->filterPrivate();
        } elseif ($this->isFilterActive(self::FILTER_PUBLIC)) {
            $this->filterPublic();
        }
    }

    public function isFilterActive($filter)
    {
        return in_array($filter, $this->filters);
    }

    protected function filterFile()
    {
        $fileSelector = (new Query())
            ->select(["id"])
            ->from('file')
            ->where('file.object_model=content.object_model AND file.object_id=content.object_id')
            ->limit(1);

        $fileSelectorSql = Yii::$app->db->getQueryBuilder()->build($fileSelector)[0];
        $this->query->andWhere('(' . $fileSelectorSql . ') IS NOT NULL');
        return $this;
    }

    protected function unFilterArchived()
    {
        $this->query->andWhere("(content.archived != 1 OR content.archived IS NULL)");
        return $this;
    }

    protected function filterMine()
    {
        if ($this->streamQuery->user) {
            $this->query->andWhere(['content.created_by' => $this->streamQuery->user->id]);
        }
        return $this;
    }

    protected function filterInvolved()
    {
        if ($this->streamQuery->user) {
            $this->query->leftJoin('user_follow', 'content.object_model=user_follow.object_model AND content.object_id=user_follow.object_id AND user_follow.user_id = :userId', ['userId' => $this->streamQuery->user->id]);
            $this->query->andWhere("user_follow.id IS NOT NULL");
        }
        return $this;
    }

    protected function filterPublic()
    {
        $this->query->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);
        return $this;
    }

    protected function filterPrivate()
    {
        $this->query->andWhere(['content.visibility' => Content::VISIBILITY_PRIVATE]);
        return $this;
    }
}
