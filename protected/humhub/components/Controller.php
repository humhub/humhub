<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\components\access\ControllerAccess;
use humhub\components\behaviors\AccessControl;
use Yii;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Base Controller
 *
 * @inheritdoc
 * @author luke
 */
class Controller extends \yii\web\Controller
{

    /**
     * @event \yii\base\Event an event raised on init a controller.
     */
    const EVENT_INIT = 'init';

    /**
     * @var null|string the name of the sub layout to be applied to this controller's views.
     * This property mainly affects the behavior of [[render()]].
     */
    public $subLayout = null;

    /**
     * @var string title of the rendered page
     */
    public $pageTitle;

    /**
     * @var array page titles
     */
    public $actionTitlesMap = [];

    /**
     * @var boolean append page title
     */
    public $prependActionTitles = true;

    /**
     * @var string defines the ControllerAccess class for this controller responsible for managing access rules
     * @see self::getAccess()
     */
    protected $access = ControllerAccess::class;

    /**
     * Returns access rules for the standard access control behavior.
     *
     * @see AccessControl
     * @return array the access permissions
     */
    protected function getAccessRules()
    {
        return [];
    }

    /**
     * @return null|ControllerAccess returns an ControllerAccess instance
     */
    public function getAccess()
    {
        if(!$this->access) {
            return null;
        }

        return Yii::createObject($this->access);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_INIT);
    }

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
                'rules' => $this->getAccessRules()
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function renderAjaxContent($content)
    {
        return $this->getView()->renderAjaxContent($content, $this);
    }

    /**
     * Renders a static string by applying the layouts (sublayout + layout.
     *
     * @param string $content the static string being rendered
     * @return string the rendering result of the layout with the given static string as the `$content` variable.
     * If the layout is disabled, the string will be returned back.
     *
     * @since 1.2
     */
    public function renderContent($content)
    {
        // Apply Sublayout if provided
        if ($this->subLayout !== null) {
            $content = $this->getView()->render($this->subLayout . '.php', ['content' => $content], $this);
        }

        // Return Pjax Snippet
        if (Yii::$app->request->isPjax) {
            return $this->renderAjaxContent($content);
        }


        $layoutFile = $this->findLayoutFile($this->getView());
        if ($layoutFile !== false) {
            return $this->getView()->renderFile($layoutFile, ['content' => Html::tag('div', $content, ['id' => 'layout-content'])], $this);
        } else {
            return $content;
        }
    }

    /**
     * Throws HttpException in case the request is not an post request, otherwise returns true.
     *
     * @throws \yii\web\HttpException
     * @return boolean returns true in case the current request is a POST
     */
    public function forcePostRequest()
    {
        if (Yii::$app->request->method != 'POST') {
            throw new \yii\web\HttpException(405, Yii::t('ContentModule.controllers_ContentController', 'Invalid request method!'));
        }

        return true;
    }

    /**
     * Create Redirect for AJAX Requests which output goes into HTML content.
     * Is an alternative method to redirect, for ajax responses.
     */
    public function htmlRedirect($url = "")
    {
        return $this->renderPartial('@humhub/views/htmlRedirect.php', [
            'url' => Url::to($url)
        ]);
    }

    /**
     * @throws ForbiddenHttpException
     * @since 1.2
     */
    protected function forbidden()
    {
        throw new \yii\web\ForbiddenHttpException(Yii::t('error', 'You are not allowed to perform this action.'));
    }

    /**
     * Closes a modal
     */
    public function renderModalClose()
    {
        return $this->renderPartial('@humhub/views/modalClose.php', []);
    }

    /**
     *
     * @see \yii\web\Controller::beforeAction()
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (array_key_exists($this->action->id, $this->actionTitlesMap)) {
                if ($this->prependActionTitles) {
                    $this->prependPageTitle($this->actionTitlesMap[$this->action->id]);
                } else {
                    $this->appendPageTitle($this->actionTitlesMap[$this->action->id]);
                }
            }

            if (!empty($this->pageTitle)) {
                $this->getView()->pageTitle = $this->pageTitle;
            }

            if (!Yii::$app->request->isAjax || Yii::$app->request->isPjax) {
                $this->setJsViewStatus();
            }

            return true;
        }

        return false;
    }

    /**
     * Append a page title.
     *
     * @param string $title
     */
    public function appendPageTitle($title)
    {
        $this->pageTitle .= empty($this->pageTitle) ? $title : ' - ' . $title;
    }

    /**
     * Prepend a page title.
     *
     * @param string $title
     */
    public function prependPageTitle($title)
    {
        $this->pageTitle = empty($this->pageTitle) ? $title : $title . ' - ' . $this->pageTitle;
    }

    /**
     * Set the page title.
     *
     * @param string $title
     */
    public function setPageTitle($title)
    {
        $this->pageTitle = $title;
    }

    /**
     * Set a map that indicates what page title should be shown for the currently active action.
     * It will be appended to
     *
     * @param array $map
     *            [action_id => action_page_title]
     * @param boolean $prependActionTitles set to false if the action titles should rather be appended
     */
    public function setActionTitles($map = [], $prependActionTitles = true)
    {
        $this->actionTitlesMap = is_array($map) ? $map : [];
        $this->prependActionTitles = $prependActionTitles;
    }

    /**
     * @inheritdoc
     */
    public function redirect($url, $statusCode = 302)
    {
        if (Yii::$app->request->isPjax) {
            Yii::$app->response->statusCode = $statusCode;
            Yii::$app->response->headers->add('X-PJAX-REDIRECT-URL', Url::to($url));
            return;
        }

        return Yii::$app->getResponse()->redirect(Url::to($url), $statusCode);
    }

    /**
     * Sets some ui state as current controller/module and active topmenu.
     *
     * This is required for some modules in pjax mode.
     *
     * @since 1.2
     * @param type $url
     */
    public function setJsViewStatus()
    {
        $modluleId = (Yii::$app->controller->module) ? Yii::$app->controller->module->id : '';
        $this->view->registerJs('humhub.modules.ui.view.setState("' . $modluleId . '", "' . Yii::$app->controller->id . '", "' . Yii::$app->controller->action->id . '");', \yii\web\View::POS_BEGIN);

        if (Yii::$app->request->isPjax) {
            \humhub\widgets\TopMenu::setViewState();
        }
    }

}
