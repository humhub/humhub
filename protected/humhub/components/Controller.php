<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
namespace humhub\components;


use yii\helpers\Url;


/**
 * Description of Controller
 *
 * @author luke
 */
class Controller extends \yii\web\Controller
{

    public $subLayout;

    public $pageTitle;

    public $actionTitlesMap = [];
    
    public $prependActionTitles = true;

    public function renderAjaxContent($content)
    {
        return $this->getView()->renderAjaxContent($content, $this);
    }

    public function forcePostRequest()
    {
        if (\Yii::$app->request->method != 'POST') {
            print "Invalid method!";
            die();
        }
    }

    /**
     * Create Redirect for AJAX Requests which output goes into HTML content.
     * Is an alternative method to redirect, for ajax responses.
     */
    public function htmlRedirect($url = "")
    {
        return $this->renderPartial('@humhub/views/htmlRedirect.php', array(
            'url' => Url::to($url)
        ));
    }

    /**
     * Closes a modal
     */
    public function renderModalClose()
    {
        return $this->renderPartial('@humhub/views/modalClose.php', array());
    }

    /**
     *
     * @see \yii\web\Controller::beforeAction()
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (array_key_exists($this->action->id, $this->actionTitlesMap)) {
                if($this->prependActionTitles) {
                    $this->prependPageTitle($this->actionTitlesMap[$this->action->id]);
                } else {
                    $this->appendPageTitle($this->actionTitlesMap[$this->action->id]);
                }
            }
            if (! empty($this->pageTitle)) {
                $this->getView()->pageTitle = $this->pageTitle;
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
}
