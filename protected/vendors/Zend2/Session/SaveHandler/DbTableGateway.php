<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\SaveHandler;

use Zend\Db\TableGateway\TableGateway;

/**
 * DB Table Gateway session save handler
 */
class DbTableGateway implements SaveHandlerInterface
{
    /**
     * Session Save Path
     *
     * @var string
     */
    protected $sessionSavePath;

    /**
     * Session Name
     *
     * @var string
     */
    protected $sessionName;

    /**
     * Lifetime
     * @var int
     */
    protected $lifetime;

    /**
     * Zend Db Table Gateway
     * @var TableGateway
     */
    protected $tableGateway;

    /**
     * DbTableGateway Options
     * @var DbTableGatewayOptions
     */
    protected $options;

    /**
     * Constructor
     *
     * @param TableGateway $tableGateway
     * @param DbTableGatewayOptions $options
     */
    public function __construct(TableGateway $tableGateway, DbTableGatewayOptions $options)
    {
        $this->tableGateway = $tableGateway;
        $this->options      = $options;
    }

    /**
     * Open Session
     *
     * @param  string $savePath
     * @param  string $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        $this->sessionSavePath = $savePath;
        $this->sessionName     = $name;
        $this->lifetime        = ini_get('session.gc_maxlifetime');

        return true;
    }

    /**
     * Close session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $rows = $this->tableGateway->select(array(
            $this->options->getIdColumn()   => $id,
            $this->options->getNameColumn() => $this->sessionName,
        ));

        if ($row = $rows->current()) {
            if ($row->{$this->options->getModifiedColumn()} +
                $row->{$this->options->getLifetimeColumn()} > time()) {
                return $row->{$this->options->getDataColumn()};
            }
            $this->destroy($id);
        }
        return '';
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        $data = array(
            $this->options->getModifiedColumn() => time(),
            $this->options->getDataColumn()     => (string) $data,
        );

        $rows = $this->tableGateway->select(array(
            $this->options->getIdColumn()   => $id,
            $this->options->getNameColumn() => $this->sessionName,
        ));

        if ($row = $rows->current()) {
            return (bool) $this->tableGateway->update($data, array(
                $this->options->getIdColumn()   => $id,
                $this->options->getNameColumn() => $this->sessionName,
            ));
        }
        $data[$this->options->getLifetimeColumn()] = $this->lifetime;
        $data[$this->options->getIdColumn()]       = $id;
        $data[$this->options->getNameColumn()]     = $this->sessionName;

        return (bool) $this->tableGateway->insert($data);
    }

    /**
     * Destroy session
     *
     * @param  string $id
     * @return bool
     */
    public function destroy($id)
    {
        return (bool) $this->tableGateway->delete(array(
            $this->options->getIdColumn()   => $id,
            $this->options->getNameColumn() => $this->sessionName,
        ));
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        $platform = $this->tableGateway->getAdapter()->getPlatform();
        return (bool) $this->tableGateway->delete(sprintf('%s + %s < %d',
            $platform->quoteIdentifier($this->options->getModifiedColumn()),
            $platform->quoteIdentifier($this->options->getLifetimeColumn()),
            time()
        ));
    }
}
