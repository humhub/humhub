<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\libs\EmitFilterTrait;
use humhub\modules\file\libs\FileControllerInterface;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;

class Action extends \yii\base\Action
{
    use EmitFilterTrait;

    /**
     * @event \humhub\components\Event an event raised on init a controller.
     */
    public const EVENT_INIT = 'init';
    public const EVENT_INIT_FOR_CONFIG_DETECTION = 'init-config';
    public const EVENT_INIT_NO_CONFIG_DETECTION = 'init-no-config';

    /**
     * @var array
     */
    protected array $get;

    /**
     * @var array|object
     */
    protected $post;


    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function init()
    {
        $actionConfigDetection = $this->isControllerInConfigDetection();

        if (!$actionConfigDetection) {
            $this->setGet();
            $this->setPost();
        }

        $this->trigger(self::EVENT_INIT, new Event(['result' => $actionConfigDetection]));

        $this->trigger(
            $actionConfigDetection
                ? self::EVENT_INIT_FOR_CONFIG_DETECTION
                : self::EVENT_INIT_NO_CONFIG_DETECTION,
            new Event(['result' => $actionConfigDetection])
        );

        return $actionConfigDetection;
    }


    /**
     * @param null $name
     *
     * @return array|mixed
     */
    public function &getGet(
        $name = null,
        $defaultValue = null
    ) {
        if ($name === null) {
            return $this->get;
        }

        $value = ArrayHelper::remove($this->get, $name) ?? $defaultValue;

        return $value;
    }


    /**
     * @param string|null $name
     * @param mixed|null $defaultValue
     *
     * @return static
     */
    public function setGet(
        ?string $name = null,
        $defaultValue = null
    ): self {
        if ($name === null || $name === '') {
            $this->get = Yii::$app->request->get(null, (array)($defaultValue ?? []));
        } else {
            $this->get[$name] = Yii::$app->request->get($name, $defaultValue);
        }

        return $this;
    }


    /**
     * @param null $name
     *
     * @return array
     */
    public function &getGetPost(
        $name = null,
        $defaultValue = null
    ) {
        $value = $this->getGet($name) ?? $this->getPost($name) ?? $defaultValue;

        return $value;
    }


    /**
     * @return array
     */
    public function &getPost(
        $name = null,
        $defaultValue = null
    ) {
        if ($name === null) {
            return $this->post;
        }

        if (is_array($this->post)) {
            $value = ArrayHelper::remove($this->post, $name) ?? $defaultValue;
        } elseif (is_object($this->post)) {
            $value = $this->post->$name ?? $defaultValue;
            unset($this->post->$name);
        } else {
            $value = $this->post ?? $defaultValue;
        }

        return $value;
    }


    /**
     * @param string|null $name
     * @param mixed|null $defaultValue
     *
     * @return static
     */
    public function setPost(
        ?string $name = null,
        $defaultValue = null
    ): self {
        if ($name === null || $name === '') {
            $this->post = Yii::$app->request->post(null, (array)($defaultValue ?? []));
        } else {
            $this->get[$name] = Yii::$app->request->post($name, $defaultValue);
        }

        return $this;
    }


    /**
     * @param null $name
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function &getPostGet(
        $name = null,
        $defaultValue = null
    ) {
        $value = $this->getPost($name) ?? $this->getGet($name) ?? $defaultValue;

        return $value;
    }

    /**
     * @return bool
     */
    protected function isControllerInConfigDetection(): bool
    {
        return $this->controller instanceof FileControllerInterface && $this->controller->getActionConfigDetection();
    }
}
