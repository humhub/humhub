<?php
/**
 * CAuthManager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAuthManager is the base class for authorization manager classes.
 *
 * CAuthManager extends {@link CApplicationComponent} and implements some methods
 * that are common among authorization manager classes.
 *
 * CAuthManager together with its concrete child classes implement the Role-Based
 * Access Control (RBAC).
 *
 * The main idea is that permissions are organized as a hierarchy of
 * {@link CAuthItem authorization items}. Items on higer level inherit the permissions
 * represented by items on lower level. And roles are simply top-level authorization items
 * that may be assigned to individual users. A user is said to have a permission
 * to do something if the corresponding authorization item is inherited by one of his roles.
 *
 * Using authorization manager consists of two aspects. First, the authorization hierarchy
 * and assignments have to be established. CAuthManager and its child classes
 * provides APIs to accomplish this task. Developers may need to develop some GUI
 * so that it is more intuitive to end-users. Second, developers call {@link IAuthManager::checkAccess}
 * at appropriate places in the application code to check if the current user
 * has the needed permission for an operation.
 *
 * @property array $roles Roles (name=>CAuthItem).
 * @property array $tasks Tasks (name=>CAuthItem).
 * @property array $operations Operations (name=>CAuthItem).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.auth
 * @since 1.0
 */
abstract class CAuthManager extends CApplicationComponent implements IAuthManager
{
	/**
	 * @var boolean Enable error reporting for bizRules.
	 * @since 1.1.3
	 */
	public $showErrors = false;

	/**
	 * @var array list of role names that are assigned to all users implicitly.
	 * These roles do not need to be explicitly assigned to any user.
	 * When calling {@link checkAccess}, these roles will be checked first.
	 * For performance reason, you should minimize the number of such roles.
	 * A typical usage of such roles is to define an 'authenticated' role and associate
	 * it with a biz rule which checks if the current user is authenticated.
	 * And then declare 'authenticated' in this property so that it can be applied to
	 * every authenticated user.
	 */
	public $defaultRoles=array();

	/**
	 * Creates a role.
	 * This is a shortcut method to {@link IAuthManager::createAuthItem}.
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return CAuthItem the authorization item
	 */
	public function createRole($name,$description='',$bizRule=null,$data=null)
	{
		return $this->createAuthItem($name,CAuthItem::TYPE_ROLE,$description,$bizRule,$data);
	}

	/**
	 * Creates a task.
	 * This is a shortcut method to {@link IAuthManager::createAuthItem}.
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return CAuthItem the authorization item
	 */
	public function createTask($name,$description='',$bizRule=null,$data=null)
	{
		return $this->createAuthItem($name,CAuthItem::TYPE_TASK,$description,$bizRule,$data);
	}

	/**
	 * Creates an operation.
	 * This is a shortcut method to {@link IAuthManager::createAuthItem}.
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return CAuthItem the authorization item
	 */
	public function createOperation($name,$description='',$bizRule=null,$data=null)
	{
		return $this->createAuthItem($name,CAuthItem::TYPE_OPERATION,$description,$bizRule,$data);
	}

	/**
	 * Returns roles.
	 * This is a shortcut method to {@link IAuthManager::getAuthItems}.
	 * @param mixed $userId the user ID. If not null, only the roles directly assigned to the user
	 * will be returned. Otherwise, all roles will be returned.
	 * @return array roles (name=>CAuthItem)
	 */
	public function getRoles($userId=null)
	{
		return $this->getAuthItems(CAuthItem::TYPE_ROLE,$userId);
	}

	/**
	 * Returns tasks.
	 * This is a shortcut method to {@link IAuthManager::getAuthItems}.
	 * @param mixed $userId the user ID. If not null, only the tasks directly assigned to the user
	 * will be returned. Otherwise, all tasks will be returned.
	 * @return array tasks (name=>CAuthItem)
	 */
	public function getTasks($userId=null)
	{
		return $this->getAuthItems(CAuthItem::TYPE_TASK,$userId);
	}

	/**
	 * Returns operations.
	 * This is a shortcut method to {@link IAuthManager::getAuthItems}.
	 * @param mixed $userId the user ID. If not null, only the operations directly assigned to the user
	 * will be returned. Otherwise, all operations will be returned.
	 * @return array operations (name=>CAuthItem)
	 */
	public function getOperations($userId=null)
	{
		return $this->getAuthItems(CAuthItem::TYPE_OPERATION,$userId);
	}

	/**
	 * Executes the specified business rule.
	 * @param string $bizRule the business rule to be executed.
	 * @param array $params parameters passed to {@link IAuthManager::checkAccess}.
	 * @param mixed $data additional data associated with the authorization item or assignment.
	 * @return boolean whether the business rule returns true.
	 * If the business rule is empty, it will still return true.
	 */
	public function executeBizRule($bizRule,$params,$data)
	{
		return $bizRule==='' || $bizRule===null || ($this->showErrors ? eval($bizRule)!=0 : @eval($bizRule)!=0);
	}

	/**
	 * Checks the item types to make sure a child can be added to a parent.
	 * @param integer $parentType parent item type
	 * @param integer $childType child item type
	 * @throws CException if the item cannot be added as a child due to its incompatible type.
	 */
	protected function checkItemChildType($parentType,$childType)
	{
		static $types=array('operation','task','role');
		if($parentType < $childType)
			throw new CException(Yii::t('yii','Cannot add an item of type "{child}" to an item of type "{parent}".',
				array('{child}'=>$types[$childType], '{parent}'=>$types[$parentType])));
	}
}
