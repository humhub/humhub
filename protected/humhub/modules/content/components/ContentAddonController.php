<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\Controller;
use humhub\helpers\DataTypeHelper;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * ContentAddonController is a base controller for ContentAddons.
 *
 * It automatically loads the target content or content addon record based
 * on given parameters contentModel or contentId.
 *
 * Also an access check is performed.
 *
 * @author luke
 * @version 0.11
 */
class ContentAddonController extends Controller
{
    /**
     * Content this addon belongs to
     *
     * @var ContentAddonActiveRecord|ContentActiveRecord
     */
    public $parentContent;

    /**
     * ContentAddon this addon may belongs to
     * ContentAddons may also belongs to ContentAddons e.g. Like -> Comment
     *
     * @var ContentAddonActiveRecord
     */
    public $parentContentAddon;

    /**
     * @var ContentAddonActiveRecord
     */
    public $contentAddon;

    /**
     * Class name of content model class
     *
     * @var string
     */
    public $contentModel;

    /**
     * Primary key of content model record
     *
     * @var int
     */
    public $contentId;

    /**
     * Automatically loads the by content or content addon given by parameter.
     * className & id
     *
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        $modelClass = Yii::$app->request->get('contentModel');
        $pk = (int)Yii::$app->request->get('contentId');

        // Fixme
        if ($modelClass == '') {
            $modelClass = Yii::$app->request->post('contentModel');
            $pk = (int)Yii::$app->request->post('contentId');
        }


        if ($modelClass == "" || $pk == "") {
            throw new HttpException(500, 'Model & ID parameter required!');
        }

        /** @var ContentAddonActiveRecord|ContentActiveRecord $modelClass */
        $modelClass = DataTypeHelper::matchClassType(
            $modelClass,
            [ContentAddonActiveRecord::class, ContentActiveRecord::class],
            true,
        );
        $target = $modelClass::findOne(['id' => $pk]);

        if ($target === null) {
            throw new HttpException(404, 'Could not find underlying content or content addon record!');
        }

        if ($target instanceof ContentAddonActiveRecord) {
            $this->parentContentAddon = $target;
            $this->parentContent = $target->getSource();
        } else {
            $this->parentContent = $target;
        }

        if (!$this->parentContent->content->canView()) {
            throw new HttpException(403, 'Access denied!');
        }

        $this->contentModel = PolymorphicRelation::getObjectModel($target);
        $this->contentId = $target->getPrimaryKey();

        return parent::beforeAction($action);
    }

    /**
     * Loads Content Addon
     * We also validate that the content addon corresponds to the loaded content.
     *
     * @param string $className
     * @param int $pk
     */
    public function loadContentAddon($className, $pk)
    {
        /** @var ContentAddonActiveRecord|null $className */
        $className = DataTypeHelper::matchClassType($className, ContentAddonActiveRecord::class);
        if ($className === null) {
            throw new Exception("Given className is not a content addon model!");
        }

        $target = $className::findOne(['id' => $pk]);

        if ($target === null) {
            throw new HttpException(500, 'Could not find content addon record!');
        }

        if ($target->object_model !== PolymorphicRelation::getObjectModel($this->parentContent) && $target->object_id !== $this->parentContent->getPrimaryKey()) {
            throw new HttpException(500, 'Content addon not belongs to given content record!');
        }

        $this->contentAddon = $target;
    }
}
