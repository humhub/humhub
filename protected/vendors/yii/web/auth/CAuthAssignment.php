<?php
/**
 * CAuthAssignment class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAuthAssignment represents an assignment of a role to a user.
 * It includes additional assignment information such as {@link bizRule} and {@link data}.
 * Do not create a CAuthAssignment instance using the 'new' operator.
 * Instead, call {@link IAuthManager::assign}.
 *
 * @property mixed $userId User ID (see {@link IWebUser::getId}).
 * @property string $itemName The authorization item name.
 * @property string $bizRule The business rule associated with this assignment.
 * @property mixed $data Additional data for this assignment.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.auth
 * @since 1.0
 */
class CAuthAssignment extends CComponent
{
	private $_auth;
	private $_itemName;
	private $_userId;
	private $_bizRule;
	private $_data;

	/**
	 * Constructor.
	 * @param IAuthManager $auth the authorization manager
	 * @param string $itemName authorization item name
	 * @param mixed $userId user ID (see {@link IWebUser::getId})
	 * @param string $bizRule the business rule associated with this assignment
	 * @param mixed $data additional data for this assignment
	 */
	public function __construct($auth,$itemName,$userId,$bizRule=null,$data=null)
	{
		$this->_auth=$auth;
		$this->_itemName=$itemName;
		$this->_userId=$userId;
		$this->_bizRule=$bizRule;
		$this->_data=$data;
	}

	/**
	 * @return mixed user ID (see {@link IWebUser::getId})
	 */
	public function getUserId()
	{
		return $this->_userId;
	}

	/**
	 * @return string the authorization item name
	 */
	public function getItemName()
	{
		return $this->_itemName;
	}

	/**
	 * @return string the business rule associated with this assignment
	 */
	public function getBizRule()
	{
		return $this->_bizRule;
	}

	/**
	 * @param string $value the business rule associated with this assignment
	 */
	public function setBizRule($value)
	{
		if($this->_bizRule!==$value)
		{
			$this->_bizRule=$value;
			$this->_auth->saveAuthAssignment($this);
		}
	}

	/**
	 * @return mixed additional data for this assignment
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @param mixed $value additional data for this assignment
	 */
	public function setData($value)
	{
		if($this->_data!==$value)
		{
			$this->_data=$value;
			$this->_auth->saveAuthAssignment($this);
		}
	}
}