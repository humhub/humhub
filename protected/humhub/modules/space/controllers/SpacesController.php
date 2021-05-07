<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\space\components\SpacesQuery;
use Yii;

/**
 * SpacesController displays users directory
 *
 * @since 1.9
 */
class SpacesController extends Controller
{

    /**
     * @inheritdoc
     */
    public $subLayout = '@space/views/spaces/_layout';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('SpaceModule.base', 'Spaces'),
        ]);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ];
    }

    /**
     * Action to display spaces page
     */
    public function actionIndex()
    {
        $spacesQuery = new SpacesQuery();

        return $this->render('index', [
            'spaces' => $spacesQuery,
        ]);
    }

}