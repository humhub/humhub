<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;

use humhub\modules\polls\models\Poll;
use humhub\modules\polls\models\PollAnswerUser;
use Yii;
use yii\db\Query;
use humhub\modules\content\models\Content;

class PollsStreamFilter extends DefaultStreamFilter
{
    /**
     * Polls filters
     */

    const FILTER_MINE = "filter_entry_mine";
    const FILTER_PRIVATE = "filter_visibility_private";
    const FILTER_PUBLIC = "filter_visibility_public";
    const FILTER_POLLS_NOT_ANSWERED = "filter_polls_notAnswered";

    /**
     * Array of stream filters to apply to the query.
     * There are the following filter available:
     *
     *  - 'entry_mine': Filters only content created by the query $user
     *  - 'visibility_private': Filter only private content
     *  - 'visibility_public': Filter only public content
     *  - 'filter_polls_notAnswered': Filter only not answered polls
     *
     */


    public function apply()
    {

        // Show only mine items
        if ($this->isFilterActive(self::FILTER_MINE)) {
            $this->filterMine();
        }

        // Visibility filters
        if ($this->isFilterActive(self::FILTER_PRIVATE)) {
            $this->filterPrivate();
        } elseif ($this->isFilterActive(self::FILTER_PUBLIC)) {
            $this->filterPublic();
        }

        if ($this->isFilterActive(self::FILTER_POLLS_NOT_ANSWERED)) {
            $this->filterPollsNotAnswered();
        }
    }

    protected function filterPollsNotAnswered()
    {

        $pollAnswerUserQuery = PollAnswerUser::find()->select('poll_id');
        $this->query->andWhere(['NOT IN', 'content.object_id', $pollAnswerUserQuery]);
        $this->query->andWhere(['=', 'content.object_model', Poll::class]);

        return $this;
    }
}
