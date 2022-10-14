<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\widgets;

use humhub\modules\content\widgets\WallCreateContentForm;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Url;

/**
 * This widget is used include the post form.
 * It normally should be placed above a steam.
 *
 * @since 0.5
 */
class Form extends WallCreateContentForm
{

    /**
     * @inheritdoc
     */
    public $submitUrl = '/post/post/post';

    /**
     * @var string
     */
    public $mentioningUrl = '/search/mentioning/space';

    /**
     * Get params for form rendering
     *
     * @param array $additionalParams
     * @return array
     */
    public function getRenderParams(array $additionalParams = []): array
    {
        $post = new Post($this->contentContainer);
        $canCreatePostInSpace = ($this->contentContainer instanceof Space && $post->content->canEdit());

        return array_merge([
            'post' => $post,
            'mentioningUrl' => $canCreatePostInSpace ? Url::to([$this->mentioningUrl, 'id' => $this->contentContainer->id]) : null,
            'submitUrl' => $this->submitUrl,
        ], $additionalParams);
    }

    /**
     * @inheritdoc
     */
    public function renderForm(): string
    {
        return $this->render('form', $this->getRenderParams());
    }

    /**
     * @inheritdoc
     */
    public function renderActiveForm(ActiveForm $form): string
    {
        return $this->render('form', $this->getRenderParams(['form' => $form]));
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!(new Post($this->contentContainer))->content->canEdit()) {
            return '';
        }

        return parent::run();
    }

}
