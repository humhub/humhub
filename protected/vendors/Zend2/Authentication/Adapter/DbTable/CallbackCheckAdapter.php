<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication\Adapter\DbTable;

use Zend\Authentication\Result as AuthenticationResult;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql;
use Zend\Db\Sql\Predicate\Operator as SqlOp;

class CallbackCheckAdapter extends AbstractAdapter
{
    /**
     * $credentialValidationCallback - This overrides the Treatment usage to provide a callback
     * that allows for validation to happen in code
     *
     * @var callable
     */
    protected $credentialValidationCallback = null;

    /**
     * __construct() - Sets configuration options
     *
     * @param DbAdapter $zendDb
     * @param string    $tableName                    Optional
     * @param string    $identityColumn               Optional
     * @param string    $credentialColumn             Optional
     * @param callable  $credentialValidationCallback Optional
     */
    public function __construct(
        DbAdapter $zendDb,
        $tableName = null,
        $identityColumn = null,
        $credentialColumn = null,
        $credentialValidationCallback = null
    ) {
        parent::__construct($zendDb, $tableName, $identityColumn, $credentialColumn);

        if (null !== $credentialValidationCallback) {
            $this->setCredentialValidationCallback($credentialValidationCallback);
        } else {
            $this->setCredentialValidationCallback(function ($a, $b) {
                return $a === $b;
            });
        }
    }

    /**
     * setCredentialValidationCallback() - allows the developer to use a callback as a way of checking the
     * credential.
     *
     * @param callable $validationCallback
     * @return DbTable
     * @throws Exception\InvalidArgumentException
     */
    public function setCredentialValidationCallback($validationCallback)
    {
        if (!is_callable($validationCallback)) {
            throw new Exception\InvalidArgumentException('Invalid callback provided');
        }
        $this->credentialValidationCallback = $validationCallback;
        return $this;
    }

    /**
     * _authenticateCreateSelect() - This method creates a Zend\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Sql\Select
     */
    protected function authenticateCreateSelect()
    {
        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->tableName)
            ->columns(array(Sql\Select::SQL_STAR))
            ->where(new SqlOp($this->identityColumn, '=', $this->identity));

        return $dbSelect;
    }

    /**
     * _authenticateValidateResult() - This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @param  array $resultIdentity
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        try {
            $callbackResult = call_user_func($this->credentialValidationCallback, $resultIdentity[$this->credentialColumn], $this->credential);
        } catch (\Exception $e) {
            $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_UNCATEGORIZED;
            $this->authenticateResultInfo['messages'][] = $e->getMessage();
            return $this->authenticateCreateAuthResult();
        }
        if ($callbackResult !== true) {
            $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->authenticateCreateAuthResult();
        }

        $this->resultRow = $resultIdentity;

        $this->authenticateResultInfo['code']       = AuthenticationResult::SUCCESS;
        $this->authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->authenticateCreateAuthResult();
    }
}
