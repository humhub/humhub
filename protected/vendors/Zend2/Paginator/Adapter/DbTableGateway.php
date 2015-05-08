<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator\Adapter;

use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;

class DbTableGateway extends DbSelect
{
    /**
     * Constructs instance.
     *
     * @param TableGateway                $tableGateway
     * @param Where|\Closure|string|array $where
     * @param null                        $order
     */
    public function __construct(TableGateway $tableGateway, $where = null, $order = null)
    {
        $select = $tableGateway->getSql()->select();
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }

        $dbAdapter          = $tableGateway->getAdapter();
        $resultSetPrototype = $tableGateway->getResultSetPrototype();

        parent::__construct($select, $dbAdapter, $resultSetPrototype);
    }
}
