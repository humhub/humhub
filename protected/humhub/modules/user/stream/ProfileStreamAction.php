<?php


namespace humhub\modules\user\stream;

use humhub\modules\space\models\Space;
use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\user\models\User;
use humhub\modules\user\models\User as UserModel;
use humhub\modules\user\Module;
use humhub\modules\user\stream\filters\IncludeAllContributionsFilter;
use Yii;
use yii\base\InvalidConfigException;

/**
 * ProfileStream
 *
 * @package humhub\modules\user\components
 */
class ProfileStreamAction extends ContentContainerStream
{
    /**
     * @var IncludeAllContributionsFilter
     */
    public $includeAllContributionsFilter;

    public function initQuery()
    {
        $query = parent::initQuery();
        $this->includeAllContributionsFilter = $query->addFilterHandler(new IncludeAllContributionsFilter(['user' => $this->contentContainer]));
        return $query;
    }

    /**
     * @inheritdoc
     */
    protected function handleContentContainer()
    {
        if (!($this->contentContainer instanceof User)) {
            throw new InvalidConfigException('ContentContainer must be related to a User record.');
        }

        if($this->user && $this->includeAllContributionsFilter->isActive()) {
            $this->handlePinnedContent();
        } else {
            parent::handleContentContainer();
        }
    }
}
