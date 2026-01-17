<?php

namespace humhub\modules\activity\components;

use humhub\modules\activity\models\Activity;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use Yii;
use yii\base\BaseObject;

abstract class BaseActivity extends BaseObject
{
    protected ContentContainer $contentContainer;

    protected User $user;

    protected string $createdAt;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($config);

        $this->contentContainer = $record->contentContainer;
        $this->user = $record->createdBy;
        $this->createdAt = $record->created_at;
    }

    abstract public function getAsText(): string;

    public function renderWeb(): string
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/web.php',
            array_merge(
                $this->getViewParams(),
                ['message' => $this->getAsText()],
            ),
        );
    }

    public function renderPlaintext(): string
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/mail_plaintext.php',
            array_merge(
                $this->getViewParams(),
                ['message' => $this->getAsText(), 'url' => $this->getUrl(true)],
            ),
        );
    }

    public function renderMail(): string
    {
        return Yii::$app->getView()->renderFile(
            '@activity/views/layouts/mail.php',
            array_merge(
                $this->getViewParams(),
                ['message' => $this->getAsText(), 'url' => $this->getUrl(true)],
            ),
        );
    }

    protected function getViewParams(): array
    {
        return [
            'url' => $this->getUrl(),
            'contentContainer' => $this->contentContainer,
            'createdAt' => $this->createdAt,
            'user' => $this->user,
        ];
    }

    public function getUrl(): ?string
    {
        return $this->contentContainer->polymorphicRelation->getUrl(true);
    }
}
