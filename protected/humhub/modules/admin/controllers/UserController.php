<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\compat\HForm;
use humhub\components\export\ArrayColumn;
use humhub\components\export\DateTimeColumn;
use humhub\components\export\SpreadsheetExport;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\PasswordEditForm;
use humhub\modules\admin\models\forms\UserDeleteForm;
use humhub\modules\admin\models\forms\UserEditForm;
use humhub\modules\admin\models\UserSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\forms\Invite as InviteForm;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\services\LinkRegistrationService;
use Yii;
use yii\base\Exception;
use yii\db\Query;
use yii\web\HttpException;
use yii\web\Response;

/**
 * User management
 *
 * @since 0.5
 */
class UserController extends Controller
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

        $this->appendPageTitle(Yii::t('AdminModule.base', 'Users'));
        $this->subLayout = '@admin/views/layouts/user';
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => [ManageUsers::class, ManageGroups::class]],
            ['permissions' => [ManageSettings::class], 'actions' => ['index']],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->can([new ManageUsers(), new ManageGroups()])) {
            return $this->redirect(['list']);
        } elseif (Yii::$app->user->can(ManageSettings::class)) {
            return $this->redirect(['/admin/authentication']);
        } else {
            return $this->forbidden();
        }
    }

    /**
     * Returns a List of Users
     */
    public function actionList()
    {
        $searchModel = Yii::createObject(UserSearch::class);
        $searchModel->status = User::STATUS_ENABLED;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $showPendingRegistrations = Invite::find()->where(Invite::filterSource())->exists()
            && Yii::$app->user->can([ManageUsers::class, ManageGroups::class]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'showPendingRegistrations' => $showPendingRegistrations,
        ]);
    }

    /**
     * Edits a user
     * @return string
     * @throws HttpException
     */
    public function actionEdit()
    {
        $user = UserEditForm::findOne(['id' => Yii::$app->request->get('id')]);

        if ($user === null) {
            throw new HttpException(404, Yii::t('AdminModule.user', 'User not found!'));
        }

        $user->initGroupSelection();

        if ($user->canEditAdminFields()) {
            $user->scenario = User::SCENARIO_EDIT_ADMIN;
            $user->profile->scenario = Profile::SCENARIO_EDIT_ADMIN;
        }

        $profile = $user->profile;

        if ($user->canEditPassword()) {
            if (!($password = PasswordEditForm::findOne(['user_id' => $user->id]))) {
                $password = new PasswordEditForm();
                $password->user_id = $user->id;
            }
            $password->mustChangePassword = $user->mustChangePassword();
        }

        // Build Form Definition
        $definition = [];
        $definition['elements'] = [];
        // Add User Form
        $definition['elements']['User'] = [
            'type' => 'form',
            'title' => Yii::t('AdminModule.user', 'Account'),
            'elements' => [
                'id' => [
                    'type' => 'text',
                    'class' => 'form-control',
                    'readonly' => true,
                ],
                'username' => [
                    'type' => 'text',
                    'class' => 'form-control',
                    'maxlength' => 25,
                    'readonly' => !$user->getAuthClientUserService()->canChangeUsername(),
                ],
                'email' => [
                    'type' => 'text',
                    'class' => 'form-control',
                    'maxlength' => 100,
                    'readonly' => !$user->getAuthClientUserService()->canChangeEmail(),
                ],
                'groupSelection' => [
                    'id' => 'user_edit_groups',
                    'type' => 'multiselectdropdown',
                    'items' => UserEditForm::getGroupItems(),
                    'options' => [
                        'data-placeholder' => Yii::t('AdminModule.user', 'Select Groups'),
                        'data-placeholder-more' => Yii::t('AdminModule.user', 'Add Groups...'),
                        'data-tags' => 'false',
                    ],
                    'maxSelection' => 250,
                    'isVisible' => Yii::$app->user->can(new ManageGroups()),
                ],
            ],
        ];

        if ($user->canEditAdminFields()) {
            $definition['elements']['User']['elements']['status'] = [
                'type' => 'dropdownlist',
                'class' => 'form-control',
                'items' => User::getStatusOptions(false),
            ];

            $definition['elements']['User']['elements']['visibility'] = [
                'type' => 'dropdownlist',
                'class' => 'form-control',
                'items' => User::getVisibilityOptions(),
            ];
        }

        // Change Password Form
        if ($user->canEditPassword()) {
            $definition['elements']['Password'] = [
                'type' => 'form',
                'title' => Yii::t('AdminModule.user', 'Password'),
                'elements' => [
                    'newPassword' => [
                        'type' => 'password',
                        'class' => 'form-control',
                        'maxlength' => 45,
                    ],
                    'newPasswordConfirm' => [
                        'type' => 'password',
                        'class' => 'form-control',
                        'maxlength' => 45,
                    ],
                    'mustChangePassword' => [
                        'type' => 'checkbox',
                        'class' => 'form-control',
                        'label' => Yii::t('UserModule.base', 'Force password change upon next login'),
                    ],
                ],
            ];
        }

        // Add Profile Form
        $definition['elements']['Profile'] = array_merge(['type' => 'form'], $profile->getFormDefinition());

        // Get Form Definition
        $definition['buttons'] = [
            'save' => [
                'type' => 'submit',
                'label' => Yii::t('AdminModule.user', 'Save'),
                'class' => 'btn btn-primary',
            ],

        ];

        if ($user->canEditAdminFields() && !$user->isCurrentUser()) {
            $definition['buttons']['delete'] = [
                'type' => 'submit',
                'label' => Yii::t('AdminModule.user', 'Delete'),
                'class' => 'btn btn-danger',
            ];
        }

        $form = new HForm($definition);
        $form->models['User'] = $user;
        $form->models['Profile'] = $profile;
        if ($user->canEditPassword()) {
            $form->models['Password'] = $password;
        }

        if ($form->submitted('save') && $form->validate()) {
            if ($user->canEditPassword()) {
                if (!empty($password->newPassword)) {
                    $password->setPassword($password->newPassword);
                } elseif ($user->canEditAdminFields()) {
                    // Allow admin to save user without password
                    unset($form->models['Password']);
                }
                $user->setMustChangePassword($password->mustChangePassword);
            }
            if ($form->save()) {
                $this->view->saved();
                return $this->redirect(['/admin/user']);
            }
        }

        if ($form->submitted('delete')) {
            return $this->redirect(['delete', 'id' => $user->id]);
        }

        return $this->render('edit', [
            'hForm' => $form,
            'user' => $user,
        ]);
    }

    public function actionAdd()
    {
        $registration = new Registration(enableEmailField: true, enableMustChangePassword: true);
        $registration->enableUserApproval = false;

        if ($registration->submitted('save') && $registration->validate() && $registration->register()) {
            return $this->redirect(['edit', 'id' => $registration->getUser()->id]);
        }

        $invite = new InviteForm(['target' => LinkRegistrationService::TARGET_ADMIN]);

        return $this->render('add', [
            'hForm' => $registration,
            'canInviteByEmail' => $invite->canInviteByEmail(),
            'canInviteByLink' => $invite->canInviteByLink(),
        ]);
    }

    /**
     * Deletes a user permanently
     * @throws HttpException
     */
    public function actionDelete($id)
    {
        $user = User::findOne(['id' => $id]);

        $this->checkUserAccess($user);

        if ($user->isCurrentUser()) {
            throw new HttpException(400, Yii::t('AdminModule.user', 'You cannot delete yourself!'));
        }

        $model = new UserDeleteForm(['user' => $user]);
        if ($model->load(Yii::$app->request->post()) && $model->performDelete()) {
            $this->view->info(Yii::t('AdminModule.user', 'User deletion process queued.'));
            return $this->redirect(['list']);
        }
        return $this->render('delete', ['model' => $model]);
    }

    public function checkUserAccess(User $user = null)
    {
        if (!$user) {
            throw new HttpException(404, Yii::t('AdminModule.user', 'User not found!'));
        }

        if ($user->isSystemAdmin() && !Yii::$app->user->isAdmin()) {
            throw new HttpException(403);
        }
    }

    /**
     * Redirect to user profile
     *
     * @param int $id
     * @return \yii\base\Response the response
     * @throws HttpException
     */
    public function actionViewProfile($id)
    {
        $user = User::findOne(['id' => $id]);
        if ($user === null) {
            throw new HttpException(404);
        }

        return $this->redirect($user->getUrl());
    }

    public function actionEnable($id)
    {
        $this->forcePostRequest();

        $user = User::findOne(['id' => $id]);
        if ($user === null) {
            throw new HttpException(404);
        }

        $user->status = User::STATUS_ENABLED;
        $user->save();

        return $this->redirect(['list']);
    }

    public function actionDisable($id)
    {
        $this->forcePostRequest();

        $user = User::findOne(['id' => $id]);

        $this->checkUserAccess($user);

        $user->status = User::STATUS_DISABLED;
        $user->save();

        return $this->redirect(['list']);
    }

    /**
     * Redirect to user profile
     *
     * @param int $id
     * @return \yii\base\Response the response
     * @throws HttpException
     */
    public function actionImpersonate($id)
    {
        $this->forcePostRequest();

        $user = User::findOne(['id' => $id]);

        $this->checkUserAccess($user);

        if (!Yii::$app->user->impersonate($user)) {
            throw new HttpException(403);
        }

        return $this->goHome();
    }

    /**
     * Export user list as csv or xlsx
     * @param string $format supported format by phpspreadsheet
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function actionExport($format)
    {
        $searchModel = Yii::createObject(UserSearch::class);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $exporter = new SpreadsheetExport([
            'dataProvider' => $dataProvider,
            'columns' => $this->collectExportColumns(),
            'resultConfig' => [
                'fileBaseName' => 'humhub_user',
                'writerType' => $format,
            ],
        ]);

        return $exporter->export()->send();
    }

    /**
     * Return array with columns for data export
     * @return array
     */
    private function collectExportColumns()
    {
        $userColumns = [
            'id',
            'guid',
            'status',
            'username',
            'email',
            'auth_mode',
            [
                'class' => ArrayColumn::class,
                'attribute' => 'tags',
            ],
            'language',
            'time_zone',
            [
                'class' => DateTimeColumn::class,
                'attribute' => 'created_at',
            ],
            'created_by',
            [
                'class' => DateTimeColumn::class,
                'attribute' => 'updated_at',
            ],
            'updated_by',
            [
                'class' => DateTimeColumn::class,
                'attribute' => 'last_login',
            ],
            'authclient_id',
            'visibility',
        ];

        $profileColumns = (new Query())
            ->select(['CONCAT(\'profile.\', internal_name)'])
            ->from(ProfileField::tableName())
            ->orderBy(['profile_field_category_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->column();

        return array_merge($userColumns, $profileColumns);
    }
}
