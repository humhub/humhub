<?php

/**
 * ProfileController is responsible for all user profiles.
 * Also the following functions are implemented here.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class ProfileController extends Controller
{

    public $subLayout = "_layout";
	
    public $privacySettings = array(
    	'displayFollowerInfo' => true,
    	'displayFollowingInfo' => true,
    	'displaySpaceInfo' => true,
    );
   
    
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors()
    {
        return array(
            'ProfileControllerBehavior' => array(
                'class' => 'application.modules_core.user.behaviors.ProfileControllerBehavior',
            ),
        );
    }

    /**
     *
     */
    public function actionIndex()
    {
    	$this->setPrivacySettings();
    	$this->render('index', array('privacySettings' => $this->privacySettings));
    }

    /**
     *
     */
    public function actionAbout()
    {
    	$this->setPrivacySettings();
    	$this->render('about', array('user' => $this->getUser(), 'privacySettings' => $this->privacySettings));
    }

    /**
     * Unfollows a User
     *
     */
    public function actionFollow()
    {
        $this->forcePostRequest();
        $this->getUser()->follow();
        $this->redirect($this->getUser()->getUrl());
    }

    /**
     * Unfollows a User
     */
    public function actionUnfollow()
    {
        $this->forcePostRequest();
        $this->getUser()->unfollow();
        $this->redirect($this->getUser()->getUrl());
    }

    
    /**
    * Determine User privacy settings
    */
    private function setPrivacySettings()
    {
    	$user = $this->getUser();
    	
    	// Display "Follower" Info
    	if (((HSetting::Get('defaultDisplayProfileFollowerInfo', 'privacy_default') == 'hide') && 
    			!(HSetting::Get('allowUserOverrideFollowerSetting', 'privacy_default'))) ||
    			((HSetting::Get('allowUserOverrideFollowerSetting', 'privacy_default')) &&
    			($user->getSetting("displayProfileFollowerInfo", 'privacy') == 'hide')))
    	{
    		$this->privacySettings['displayFollowerInfo'] = false;
    	}
    	
    	// Display "Following" Info
    	if (((HSetting::Get('defaultDisplayProfileFollowingInfo', 'privacy_default') == 'hide') &&
    			!(HSetting::Get('allowUserOverrideFollowingSetting', 'privacy_default'))) ||
    			((HSetting::Get('allowUserOverrideFollowingSetting', 'privacy_default')) &&
    					($user->getSetting("displayProfileFollowingInfo", 'privacy') == 'hide')))
    	{
    		$this->privacySettings['displayFollowingInfo'] = false;
    	}
    	
    	// Display "Space" Info
    	if (((HSetting::Get('defaultDisplayProfileSpaceInfo', 'privacy_default') == 'hide') &&
    			!(HSetting::Get('allowUserOverrideSpaceSetting', 'privacy_default'))) ||
    			((HSetting::Get('allowUserOverrideSpaceSetting', 'privacy_default')) &&
    					($user->getSetting("displayProfileSpaceInfo", 'privacy') == 'hide')))
    	{
    		$this->privacySettings['displaySpaceInfo'] = false;
    	}
    }
}

?>
