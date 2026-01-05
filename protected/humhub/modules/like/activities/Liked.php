<?php

namespace humhub\modules\like\activities;

use humhub\modules\like\models\Like;
use Yii;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

/**
 * @property Like $source
 */
class Liked extends BaseActivity implements ConfigurableActivityInterface
{
    public $moduleId = 'like';
    public $viewName = 'liked';

    public function getViewParams($params = [])
    {
        $params['preview'] = $this->getContentInfo($this->source->getContentOwnerObject());
        return parent::getViewParams($params);
    }

    public function getTitle()
    {
        return Yii::t('LikeModule.activities', 'Likes');
    }

    public function getDescription()
    {
        return Yii::t('LikeModule.activities', 'Whenever someone likes something (e.g. a post or comment).');
    }
}
