<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\SaveHandler;

use Zend\Session\Exception;
use Zend\Stdlib\AbstractOptions;

/**
 * DbTableGateway Save Handler Options
 */
class DbTableGatewayOptions extends AbstractOptions
{
    /**
     * ID Column
     * @var string
     */
    protected $idColumn = 'id';

    /**
     * Name Column
     * @var string
     */
    protected $nameColumn = 'name';

    /**
     * Data Column
     * @var string
     */
    protected $dataColumn = 'data';

    /**
     * Lifetime Column
     * @var string
     */
    protected $lifetimeColumn = 'lifetime';

    /**
     * Modified Column
     * @var string
     */
    protected $modifiedColumn = 'modified';


    /**
     * Set Id Column
     *
     * @param string $idColumn
     * @return DbTableGatewayOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setIdColumn($idColumn)
    {
        $idColumn = (string) $idColumn;
        if (strlen($idColumn) === 0) {
            throw new Exception\InvalidArgumentException('$idColumn must be a non-empty string');
        }
        $this->idColumn = $idColumn;
        return $this;
    }

    /**
     * Get Id Column
     *
     * @return string
     */
    public function getIdColumn()
    {
        return $this->idColumn;
    }

    /**
     * Set Name Column
     *
     * @param string $nameColumn
     * @return DbTableGatewayOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setNameColumn($nameColumn)
    {
        $nameColumn = (string) $nameColumn;
        if (strlen($nameColumn) === 0) {
            throw new Exception\InvalidArgumentException('$nameColumn must be a non-empty string');
        }
        $this->nameColumn = $nameColumn;
        return $this;
    }

    /**
     * Get Name Column
     *
     * @return string
     */
    public function getNameColumn()
    {
        return $this->nameColumn;
    }

    /**
     * Set Data Column
     *
     * @param string $dataColumn
     * @return DbTableGatewayOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setDataColumn($dataColumn)
    {
        $dataColumn = (string) $dataColumn;
        if (strlen($dataColumn) === 0) {
            throw new Exception\InvalidArgumentException('$dataColumn must be a non-empty string');
        }
        $this->dataColumn = $dataColumn;
        return $this;
    }

    /**
     * Get Data Column
     *
     * @return string
     */
    public function getDataColumn()
    {
        return $this->dataColumn;
    }

    /**
     * Set Lifetime Column
     *
     * @param string $lifetimeColumn
     * @return DbTableGatewayOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setLifetimeColumn($lifetimeColumn)
    {
        $lifetimeColumn = (string) $lifetimeColumn;
        if (strlen($lifetimeColumn) === 0) {
            throw new Exception\InvalidArgumentException('$lifetimeColumn must be a non-empty string');
        }
        $this->lifetimeColumn = $lifetimeColumn;
        return $this;
    }

    /**
     * Get Lifetime Column
     *
     * @return string
     */
    public function getLifetimeColumn()
    {
        return $this->lifetimeColumn;
    }

    /**
     * Set Modified Column
     *
     * @param string $modifiedColumn
     * @return DbTableGatewayOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setModifiedColumn($modifiedColumn)
    {
        $modifiedColumn = (string) $modifiedColumn;
        if (strlen($modifiedColumn) === 0) {
            throw new Exception\InvalidArgumentException('$modifiedColumn must be a non-empty string');
        }
        $this->modifiedColumn = $modifiedColumn;
        return $this;
    }

    /**
     * Get Modified Column
     *
     * @return string
     */
    public function getModifiedColumn()
    {
        return $this->modifiedColumn;
    }
}
