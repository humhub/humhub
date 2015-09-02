<?php
/**
 * CPhpAuthManager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPhpAuthManager represents an authorization manager that stores authorization information in terms of a PHP script file.
 *
 * The authorization data will be saved to and loaded from a file
 * specified by {@link authFile}, which defaults to 'protected/data/auth.php'.
 *
 * CPhpAuthManager is mainly suitable for authorization data that is not too big
 * (for example, the authorization data for a personal blog system).
 * Use {@link CDbAuthManager} for more complex authorization data.
 *
 * @property array $authItems The authorization items of the specific type.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.auth
 * @since 1.0
 */
class CPhpAuthManager extends CAuthManager
{
	/**
	 * @var string the path of the PHP script that contains the authorization data.
	 * If not set, it will be using 'protected/data/auth.php' as the data file.
	 * Make sure this file is writable by the Web server process if the authorization
	 * needs to be changed.
	 * @see loadFromFile
	 * @see saveToFile
	 */
	public $authFile;

	private $_items=array();			// itemName => item
	private $_children=array();			// itemName, childName => child
	private $_assignments=array();		// userId, itemName => assignment

	/**
	 * Initializes the application component.
	 * This method overrides parent implementation by loading the authorization data
	 * from PHP script.
	 */
	public function init()
	{
		parent::init();
		if($this->authFile===null)
			$this->authFile=Yii::getPathOfAlias('application.data.auth').'.php';
		$this->load();
	}

	/**
	 * Performs access check for the specified user.
	 * @param string $itemName the name of the operation that need access check
	 * @param mixed $userId the user ID. This can be either an integer or a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * Since version 1.1.11 a param with name 'userId' is added to this array, which holds the value of <code>$userId</code>.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($itemName,$userId,$params=array())
	{
		if(!isset($this->_items[$itemName]))
			return false;
		$item=$this->_items[$itemName];
		Yii::trace('Checking permission "'.$item->getName().'"','system.web.auth.CPhpAuthManager');
		if(!isset($params['userId']))
		    $params['userId'] = $userId;
		if($this->executeBizRule($item->getBizRule(),$params,$item->getData()))
		{
			if(in_array($itemName,$this->defaultRoles))
				return true;
			if(isset($this->_assignments[$userId][$itemName]))
			{
				$assignment=$this->_assignments[$userId][$itemName];
				if($this->executeBizRule($assignment->getBizRule(),$params,$assignment->getData()))
					return true;
			}
			foreach($this->_children as $parentName=>$children)
			{
				if(isset($children[$itemName]) && $this->checkAccess($parentName,$userId,$params))
					return true;
			}
		}
		return false;
	}

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the item is added successfully
	 * @throws CException if either parent or child doesn't exist or if a loop has been detected.
	 */
	public function addItemChild($itemName,$childName)
	{
		if(!isset($this->_items[$childName],$this->_items[$itemName]))
			throw new CException(Yii::t('yii','Either "{parent}" or "{child}" does not exist.',array('{child}'=>$childName,'{name}'=>$itemName)));
		$child=$this->_items[$childName];
		$item=$this->_items[$itemName];
		$this->checkItemChildType($item->getType(),$child->getType());
		if($this->detectLoop($itemName,$childName))
			throw new CException(Yii::t('yii','Cannot add "{child}" as a child of "{parent}". A loop has been detected.',
				array('{child}'=>$childName,'{parent}'=>$itemName)));
		if(isset($this->_children[$itemName][$childName]))
			throw new CException(Yii::t('yii','The item "{parent}" already has a child "{child}".',
				array('{child}'=>$childName,'{parent}'=>$itemName)));
		$this->_children[$itemName][$childName]=$this->_items[$childName];
		return true;
	}

	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName,$childName)
	{
		if(isset($this->_children[$itemName][$childName]))
		{
			unset($this->_children[$itemName][$childName]);
			return true;
		}
		else
			return false;
	}

	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName,$childName)
	{
		return isset($this->_children[$itemName][$childName]);
	}

	/**
	 * Returns the children of the specified item.
	 * @param mixed $names the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return array all child items of the parent
	 */
	public function getItemChildren($names)
	{
		if(is_string($names))
			return isset($this->_children[$names]) ? $this->_children[$names] : array();

		$children=array();
		foreach($names as $name)
		{
			if(isset($this->_children[$name]))
				$children=array_merge($children,$this->_children[$name]);
		}
		return $children;
	}

	/**
	 * Assigns an authorization item to a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @param string $bizRule the business rule to be executed when {@link checkAccess} is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return CAuthAssignment the authorization assignment information.
	 * @throws CException if the item does not exist or if the item has already been assigned to the user
	 */
	public function assign($itemName,$userId,$bizRule=null,$data=null)
	{
		if(!isset($this->_items[$itemName]))
			throw new CException(Yii::t('yii','Unknown authorization item "{name}".',array('{name}'=>$itemName)));
		elseif(isset($this->_assignments[$userId][$itemName]))
			throw new CException(Yii::t('yii','Authorization item "{item}" has already been assigned to user "{user}".',
				array('{item}'=>$itemName,'{user}'=>$userId)));
		else
			return $this->_assignments[$userId][$itemName]=new CAuthAssignment($this,$itemName,$userId,$bizRule,$data);
	}

	/**
	 * Revokes an authorization assignment from a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether removal is successful
	 */
	public function revoke($itemName,$userId)
	{
		if(isset($this->_assignments[$userId][$itemName]))
		{
			unset($this->_assignments[$userId][$itemName]);
			return true;
		}
		else
			return false;
	}

	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($itemName,$userId)
	{
		return isset($this->_assignments[$userId][$itemName]);
	}

	/**
	 * Returns the item assignment information.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return CAuthAssignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	public function getAuthAssignment($itemName,$userId)
	{
		return isset($this->_assignments[$userId][$itemName])?$this->_assignments[$userId][$itemName]:null;
	}

	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return array the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAuthAssignments($userId)
	{
		return isset($this->_assignments[$userId])?$this->_assignments[$userId]:array();
	}

	/**
	 * Returns the authorization items of the specific type and user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @return array the authorization items of the specific type.
	 */
	public function getAuthItems($type=null,$userId=null)
	{
		if($type===null && $userId===null)
			return $this->_items;
		$items=array();
		if($userId===null)
		{
			foreach($this->_items as $name=>$item)
			{
				if($item->getType()==$type)
					$items[$name]=$item;
			}
		}
		elseif(isset($this->_assignments[$userId]))
		{
			foreach($this->_assignments[$userId] as $assignment)
			{
				$name=$assignment->getItemName();
				if(isset($this->_items[$name]) && ($type===null || $this->_items[$name]->getType()==$type))
					$items[$name]=$this->_items[$name];
			}
		}
		return $items;
	}

	/**
	 * Creates an authorization item.
	 * An authorization item represents an action permission (e.g. creating a post).
	 * It has three types: operation, task and role.
	 * Authorization items form a hierarchy. Higher level items inheirt permissions representing
	 * by lower level items.
	 * @param string $name the item name. This must be a unique identifier.
	 * @param integer $type the item type (0: operation, 1: task, 2: role).
	 * @param string $description description of the item
	 * @param string $bizRule business rule associated with the item. This is a piece of
	 * PHP code that will be executed when {@link checkAccess} is called for the item.
	 * @param mixed $data additional data associated with the item.
	 * @return CAuthItem the authorization item
	 * @throws CException if an item with the same name already exists
	 */
	public function createAuthItem($name,$type,$description='',$bizRule=null,$data=null)
	{
		if(isset($this->_items[$name]))
			throw new CException(Yii::t('yii','Unable to add an item whose name is the same as an existing item.'));
		return $this->_items[$name]=new CAuthItem($this,$name,$type,$description,$bizRule,$data);
	}

	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeAuthItem($name)
	{
		if(isset($this->_items[$name]))
		{
			foreach($this->_children as &$children)
				unset($children[$name]);
			foreach($this->_assignments as &$assignments)
				unset($assignments[$name]);
			unset($this->_items[$name]);
			return true;
		}
		else
			return false;
	}

	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return CAuthItem the authorization item. Null if the item cannot be found.
	 */
	public function getAuthItem($name)
	{
		return isset($this->_items[$name])?$this->_items[$name]:null;
	}

	/**
	 * Saves an authorization item to persistent storage.
	 * @param CAuthItem $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	public function saveAuthItem($item,$oldName=null)
	{
		if($oldName!==null && ($newName=$item->getName())!==$oldName) // name changed
		{
			if(isset($this->_items[$newName]))
				throw new CException(Yii::t('yii','Unable to change the item name. The name "{name}" is already used by another item.',array('{name}'=>$newName)));
			if(isset($this->_items[$oldName]) && $this->_items[$oldName]===$item)
			{
				unset($this->_items[$oldName]);
				$this->_items[$newName]=$item;
				if(isset($this->_children[$oldName]))
				{
					$this->_children[$newName]=$this->_children[$oldName];
					unset($this->_children[$oldName]);
				}
				foreach($this->_children as &$children)
				{
					if(isset($children[$oldName]))
					{
						$children[$newName]=$children[$oldName];
						unset($children[$oldName]);
					}
				}
				foreach($this->_assignments as &$assignments)
				{
					if(isset($assignments[$oldName]))
					{
						$assignments[$newName]=$assignments[$oldName];
						unset($assignments[$oldName]);
					}
				}
			}
		}
	}

	/**
	 * Saves the changes to an authorization assignment.
	 * @param CAuthAssignment $assignment the assignment that has been changed.
	 */
	public function saveAuthAssignment($assignment)
	{
	}

	/**
	 * Saves authorization data into persistent storage.
	 * If any change is made to the authorization data, please make
	 * sure you call this method to save the changed data into persistent storage.
	 */
	public function save()
	{
		$items=array();
		foreach($this->_items as $name=>$item)
		{
			$items[$name]=array(
				'type'=>$item->getType(),
				'description'=>$item->getDescription(),
				'bizRule'=>$item->getBizRule(),
				'data'=>$item->getData(),
			);
			if(isset($this->_children[$name]))
			{
				foreach($this->_children[$name] as $child)
					$items[$name]['children'][]=$child->getName();
			}
		}

		foreach($this->_assignments as $userId=>$assignments)
		{
			foreach($assignments as $name=>$assignment)
			{
				if(isset($items[$name]))
				{
					$items[$name]['assignments'][$userId]=array(
						'bizRule'=>$assignment->getBizRule(),
						'data'=>$assignment->getData(),
					);
				}
			}
		}

		$this->saveToFile($items,$this->authFile);
	}

	/**
	 * Loads authorization data.
	 */
	public function load()
	{
		$this->clearAll();

		$items=$this->loadFromFile($this->authFile);

		foreach($items as $name=>$item)
			$this->_items[$name]=new CAuthItem($this,$name,$item['type'],$item['description'],$item['bizRule'],$item['data']);

		foreach($items as $name=>$item)
		{
			if(isset($item['children']))
			{
				foreach($item['children'] as $childName)
				{
					if(isset($this->_items[$childName]))
						$this->_children[$name][$childName]=$this->_items[$childName];
				}
			}
			if(isset($item['assignments']))
			{
				foreach($item['assignments'] as $userId=>$assignment)
				{
					$this->_assignments[$userId][$name]=new CAuthAssignment($this,$name,$userId,$assignment['bizRule'],$assignment['data']);
				}
			}
		}
	}

	/**
	 * Removes all authorization data.
	 */
	public function clearAll()
	{
		$this->clearAuthAssignments();
		$this->_children=array();
		$this->_items=array();
	}

	/**
	 * Removes all authorization assignments.
	 */
	public function clearAuthAssignments()
	{
		$this->_assignments=array();
	}

	/**
	 * Checks whether there is a loop in the authorization item hierarchy.
	 * @param string $itemName parent item name
	 * @param string $childName the name of the child item that is to be added to the hierarchy
	 * @return boolean whether a loop exists
	 */
	protected function detectLoop($itemName,$childName)
	{
		if($childName===$itemName)
			return true;
		if(!isset($this->_children[$childName], $this->_items[$itemName]))
			return false;

		foreach($this->_children[$childName] as $child)
		{
			if($this->detectLoop($itemName,$child->getName()))
				return true;
		}
		return false;
	}

	/**
	 * Loads the authorization data from a PHP script file.
	 * @param string $file the file path.
	 * @return array the authorization data
	 * @see saveToFile
	 */
	protected function loadFromFile($file)
	{
		if(is_file($file))
			return require($file);
		else
			return array();
	}

	/**
	 * Saves the authorization data to a PHP script file.
	 * @param array $data the authorization data
	 * @param string $file the file path.
	 * @see loadFromFile
	 */
	protected function saveToFile($data,$file)
	{
		file_put_contents($file,"<?php\nreturn ".var_export($data,true).";\n");
	}
}
