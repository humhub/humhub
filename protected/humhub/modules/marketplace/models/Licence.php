<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\models;

use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\space\models\Space;
use humhub\modules\marketplace\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\StaleObjectException;

class Licence extends Model
{
    /**
     * Licence types
     */
    const LICENCE_TYPE_CE = 'community';
    const LICENCE_TYPE_PRO = 'pro';
    const LICENCE_TYPE_EE = 'enterprise';

    /**
     * @var string the licence type
     */
    public $type;

    /**
     * @var string the licence key
     */
    public $licenceKey;

    /**
     * @var string name of the licensee
     */
    public $licencedTo;

    /**
     * @var int the number of maximum users
     */
    public $maxUsers;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->type = static::LICENCE_TYPE_CE;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['licenceKey', 'safe'],
            ['licenceKey', 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'licenceKey' => Yii::t('MarketplaceModule.base', 'Licence key'),
        ];
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function register()
    {
        $params = array_merge(['licenceKey' => $this->licenceKey], $this->getStats());
        $result = HumHubAPI::request('v1/pro/register', $params);

        if (empty($result) || !is_array($result) || !isset($result['status'])) {
            $this->addError('licenceKey', Yii::t('MarketplaceModule.base', 'Could not connect to licence server!'));
            return false;
        }

        if ($result['status'] === 'ok' && static::fetch()) {
            return true;
        }

        $this->addError('licenceKey', Yii::t('MarketplaceModule.base', 'Could not update licence. Error: ') . $result['message']);
        return false;
    }


    /**
     * Removes the licence from this installation
     *
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public static function remove()
    {
        $licenceKey = static::getModule()->settings->get('licenceKey');

        if (!empty($licenceKey)) {
            $params = array_merge(['licenceKey' => $licenceKey], static::getStats());
            $result = HumHubAPI::request('v1/pro/unregister', $params);
        }

        static::getModule()->settings->delete('licenceKey');
        static::getModule()->settings->delete('licencedTo');
        static::getModule()->settings->delete('maxUsers');
        static::getModule()->settings->delete('lastSave');
    }

    /**
     * @return Module
     */
    private static function getModule()
    {
        return Yii::$app->getModule('marketplace');
    }


    /**
     * @return array some basic stats
     */
    private static function getStats()
    {
        return [
            'tua' => User::find()->andWhere(['status' => User::STATUS_ENABLED])->count(),
            'tu' => User::find()->count(),
            'ts' => Space::find()->count(),
        ];
    }

    /**
     * Fetches the licence from the server
     *
     * @return bool
     */
    public static function fetch()
    {
        $result = HumHubAPI::request('v1/pro/get', static::getStats());

        if (empty($result) || !is_array($result) || !isset($result['status'])) {
            return false;
        }

        if ($result['status'] === 'ok') {
            static::getModule()->settings->set('licenceKey', $result['licence']['licenceKey']);
            static::getModule()->settings->set('licencedTo', $result['licence']['licencedTo']);
            static::getModule()->settings->set('maxUsers', $result['licence']['maxUsers']);
            static::getModule()->settings->set('lastFetch', time());

            return true;
        } elseif ($result['status'] === 'not-found') {
            try {
                Licence::remove();
            } catch (StaleObjectException $e) {
            } catch (\Throwable $e) {
            }
        }

        return false;
    }

}
