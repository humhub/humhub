<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\PeopleSettingsForm;
use humhub\modules\admin\permissions\ManageSettings;
use Yii;
use yii\web\HttpException;

/**
 * User People Configuration
 *
 * @since 1.9
 */
class UserPeopleController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->appendPageTitle(Yii::t('AdminModule.base', 'People'));
        $this->subLayout = '@admin/views/layouts/user';
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => [ManageSettings::class]]
        ];
    }

    /**
     * Configuration for People page
     *
     * @return string
     * @throws HttpException
     */
    public function actionIndex()
    {
        $form = new PeopleSettingsForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect(['/admin/user-people']);
        }

        return $this->render('index', [
            'model' => $form,
        ]);
    }
}
