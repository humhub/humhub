<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\models;

use Yii;
use yii\base\Model;
use yii\base\Exception;
use humhub\modules\activity\Module;
use humhub\modules\activity\components\MailSummary;
use humhub\modules\user\models\User;

/**
 * MailSummaryForm
 *
 * @since 1.2
 * @author Luke
 */
class MailSummaryForm extends Model
{

    /**
     * Space limit modes (include or exclude)
     */
    const LIMIT_MODE_EXCLUDE = 0;
    const LIMIT_MODE_INCLUDE = 1;

    /**
     * @var array of selected activities to include
     */
    public $activities = [];

    /**
     * @var int the mail summary interval
     */
    public $interval;

    /**
     * @var array the selected spaces
     */
    public $limitSpaces;

    /**
     * @var int the mode how to handle selected spaces (include or exclude)
     */
    public $limitSpacesMode = 0;

    /**
     * @var User the user when user settings should be loaded/saved
     */
    public $user = null;

    /**
     * @var boolean indicates that custom user settings were loaded
     */
    public $userSettingsLoaded = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['interval'], 'integer'],
            [['activities'], 'in', 'range' => array_keys($this->getActivitiesArray())],
            [['limitSpaces'], 'safe'],
            [['limitSpacesMode'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'interval' => Yii::t('ActivityModule.base', 'Interval'),
            'limitSpacesMode' => Yii::t('ActivityModule.base', 'Spaces'),
            'activities' => Yii::t('ActivityModule.base', 'Activities'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'interval' => Yii::t('ActivityModule.base', 'You will only receive an e-mail if there is something new.'),
        ];
    }

    /**
     * Returns available modes how to handle given spaces
     *
     * @return array the modes
     */
    public function getLimitSpaceModes()
    {
        return [
            MailSummaryForm::LIMIT_MODE_EXCLUDE => Yii::t('ActivityModule.base', 'Exclude spaces below from the mail summary'),
            MailSummaryForm::LIMIT_MODE_INCLUDE => Yii::t('ActivityModule.base', 'Only include spaces below to the mail summary'),
        ];
    }

    /**
     * Returns a list of available mail summary intervals
     *
     * @return array the intervals
     */
    public function getIntervals()
    {
        return [
            MailSummary::INTERVAL_NONE => Yii::t('ActivityModule.base', 'Never'),
            MailSummary::INTERVAL_HOURY => Yii::t('ActivityModule.base', 'Hourly'),
            MailSummary::INTERVAL_DAILY => Yii::t('ActivityModule.base', 'Daily'),
            MailSummary::INTERVAL_WEEKLY => Yii::t('ActivityModule.base', 'Weekly'),
        ];
    }

    /**
     * Returns an array of all possible activities for the checkboxLis
     *
     * @return array
     */
    public function getActivitiesArray()
    {
        $contents = [];

        foreach (Module::getConfigurableActivities() as $activity) {
            #$contents[$activity->className()] = Html::tag('strong', $activity->getTitle()) . "<br />" . $activity->getDescription()."<br />";
            $contents[$activity->className()] = $activity->getTitle() . ' - ' . $activity->getDescription();
        }

        return $contents;
    }

    /**
     * Loads the current values into this model
     *
     * If the 'user' attribute is set, the user settings are loaded if present.
     * Otherwise the system defaults will be loaded.
     *
     * @return boolean
     */
    public function loadCurrent()
    {
        // Only load user settings when user is given and the user has own settings
        if ($this->user !== null && Yii::$app->getModule('activity')->settings->user($this->user)->get('mailSummaryInterval') !== null) {
            $settingsManager = Yii::$app->getModule('activity')->settings->user($this->user);
            $this->userSettingsLoaded = true;
        } else {
            $settingsManager = Yii::$app->getModule('activity')->settings;
        }

        $this->interval = $settingsManager->get('mailSummaryInterval');
        $this->limitSpacesMode = $settingsManager->get('mailSummaryLimitSpacesMode');
        $this->limitSpaces = explode(',', $settingsManager->get('mailSummaryLimitSpaces'));

        // Since we store only disabled activities, we need to enable the difference
        $suppressedActivities = explode(',', $settingsManager->get('mailSummaryActivitySuppress'));
        $this->activities = array_diff(array_keys($this->getActivitiesArray()), $suppressedActivities);

        return true;
    }

    /**
     * Saves the current model values to the current user or globally.
     *
     * @return boolean success
     */
    public function save()
    {
        if ($this->user !== null) {
            $settingsManager = Yii::$app->getModule('activity')->settings->user($this->user);
            $this->userSettingsLoaded = true;
        } else {
            $settingsManager = Yii::$app->getModule('activity')->settings;
        }

        if (!is_array($this->activities)) {
            $this->activities = [];
        }
        if (!is_array($this->limitSpaces)) {
            $this->limitSpaces = [];
        }

        $settingsManager->set('mailSummaryInterval', $this->interval);
        $settingsManager->set('mailSummaryLimitSpaces', implode(",", $this->limitSpaces));
        $settingsManager->set('mailSummaryLimitSpacesMode', $this->limitSpacesMode);

        // We got a list of enabled activities, but we store only disabled activity class names
        $disabledActivities = array_diff(array_keys($this->getActivitiesArray()), $this->activities);
        $settingsManager->set('mailSummaryActivitySuppress', implode(',', $disabledActivities));

        return true;
    }

    /**
     * Resets all settings stored for the current user
     *
     * @throws Exception
     */
    public function resetUserSettings()
    {
        if ($this->user === null) {
            throw new Exception("Could not reset settings when no user is set!");
        }

        $settingsManager = Yii::$app->getModule('activity')->settings->user($this->user);
        $settingsManager->delete('mailSummaryInterval');
        $settingsManager->delete('mailSummaryLimitSpaces');
        $settingsManager->delete('mailSummaryLimitSpacesMode');
        $settingsManager->delete('mailSummaryActivitySuppress');
    }

}
