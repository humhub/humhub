<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * Extended AuthClient collection with event support
 *
 * @author luke
 * @since 1.1
 */
class Collection extends Component
{

    /**
     * @event Event an event raised after the clients are set.
     */
    const EVENT_AFTER_CLIENTS_SET = 'client_set';

    /**
     * @var array list of Auth clients with their configuration in format: 'clientId' => [...]
     */
    private $_clients = [];

    /**
     * @param array $clients list of auth clients
     */
    public function setClients(array $clients)
    {
        $this->_clients = array_merge($this->getDefaultClients(), $clients);
        $this->trigger(self::EVENT_AFTER_CLIENTS_SET);
    }

    /**
     * @return ClientInterface[] list of auth clients.
     */
    public function getClients($load = true)
    {
        $clients = [];
        foreach ($this->_clients as $id => $client) {
            $clients[$id] = $this->getClient($id, $load);
        }

        return $clients;
    }

    /**
     * @param string $id service id.
     * @return ClientInterface auth client instance.
     * @throws InvalidParamException on non existing client request.
     */
    public function getClient($id, $load = true)
    {
        if (!array_key_exists($id, $this->_clients)) {
            throw new InvalidParamException("Unknown auth client '{$id}'.");
        }
        if (!is_object($this->_clients[$id]) && $load) {
            $this->_clients[$id] = $this->createClient($id, $this->_clients[$id]);
        }

        return $this->_clients[$id];
    }

    /**
     * Checks if client exists in the hub.
     * @param string $id client id.
     * @return boolean whether client exist.
     */
    public function hasClient($id)
    {
        return array_key_exists($id, $this->_clients);
    }

    /**
     * Sets a client by id and config
     *
     * @param string $id auth client id.
     * @param array $config auth client instance configuration.
     */
    public function setClient($id, $config)
    {
        $this->_clients[$id] = $config;
    }

    /**
     * Removes client by id
     *
     * @param string $id client id.
     */
    public function removeClient($id)
    {
        unset($this->_clients[$id]);
    }

    /**
     * Creates auth client instance from its array configuration.
     * @param string $id auth client id.
     * @param array $config auth client instance configuration.
     * @return ClientInterface auth client instance.
     */
    protected function createClient($id, $config)
    {
        $config['id'] = $id;

        return Yii::createObject($config);
    }

    /**
     * Returns the configuration of default auth clients
     *
     * @return array the default auth clients
     */
    protected function getDefaultClients()
    {
        $clients = [];

        $clients['password'] = [
            'class' => 'humhub\modules\user\authclient\Password'
        ];

        if (Yii::$app->getModule('user')->settings->get('auth.ldap.enabled')) {
            $clients['ldap'] = [
                'class' => 'humhub\modules\user\authclient\ZendLdapClient'
            ];
        }

        return $clients;
    }

}
