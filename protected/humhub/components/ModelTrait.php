<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

trait ModelTrait
{
    /**
     * @param array|null  $safeConfig Configuration used during instantiation. If index 0 exists, it will be used as
     *                                constructor parameters, with the rest of the config as their last element
     * @param array       $data       Data added to the instance using the load() method.
     * @param string|null $formName   Form name to  be used to extract the subset from $data in the load() method
     * @param bool|string $scenario   Validate the model with the given scenario. Use True for default scenario.
     *                                Validation is skipped on False|Null|empty
     *
     *
     * @return Model
     * @throws InvalidConfigException
     * @see BaseActiveRecord::load()
     */
    public static function safelyCreateAndLoad(?array $safeConfig = [], array $data = [], $formName = null, $scenario = false): Model
    {

        $safeConfig ??= [];

        if (($args = ArrayHelper::remove($safeConfig, 0, [])) instanceof Model) {
            $model = $args;
            $type  = get_class($model);
        } else {
            $args[] = $safeConfig;
            $model  = Yii::createObject(static::class, $safeConfig);
            $type   = get_class($model);

            if (!$model instanceof Model) {
                throw new InvalidConfigException("Error creating a model from type $type.");
            }
        }

        if (!empty($scenario) && $scenario !== true) {
            $model->setScenario($scenario);
        }

        switch (reset($data)) {
            case 'get':
                $data = Yii::$app->request->get();
            break;
            case 'post':
                $data = Yii::$app->request->post();
            break;
        }

        if (empty($data)) {
            return $model;
        }

        if (!$model->load($data, $formName)) {
            try {
                $data = json_encode($data, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $data = serialize($data);
            }

            throw new InvalidConfigException("Unable to load data for $type: $data");
        }

        if (!empty($scenario) && !$model->validate()) {
            throw new InvalidConfigException(
                "Validation of $type failed with the following error: "
                . implode('; ', $model->getErrorSummary(true))
            );
        }

        return $model;
    }

    /**
     * @param array        $criteria            Search criteria
     * @param array|string $data                Data to be loaded. Use 'get' and 'post' to get data from respective
     *                                          request parameters
     * @param null         $formName
     * @param null         $scenario
     *
     * @return Model
     * @throws InvalidConfigException
     */
    public static function safelyFindAndLoad(
        array $criteria = [],
        array $data = [],
        $formName = null,
        $scenario = null
    ): Model {

        $model = static::findOne($criteria);

        if ($model === null) {
            $model = Yii::createObject(static::class);
            $model->load($criteria);
        }

        return static::safelyCreateAndLoad([$model], $data, $formName, $scenario);
    }
}
