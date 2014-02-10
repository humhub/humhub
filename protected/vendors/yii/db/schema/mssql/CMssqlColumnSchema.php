<?php
/**
 * CMssqlColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMssqlColumnSchema class describes the column meta data of a MSSQL table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @package system.db.schema.mssql
 */
class CMssqlColumnSchema extends CDbColumnSchema
{

     /**
     * Initializes the column with its DB type and default value.
     * This sets up the column's PHP type, size, precision, scale as well as default value.
     * @param string $dbType the column's DB type
     * @param mixed $defaultValue the default value
     */
     public function init($dbType, $defaultValue)
     {
        if ($defaultValue=='(NULL)')
        {
            $defaultValue=null;
        }
        parent::init($dbType, $defaultValue);
     }


	/**
	 * Extracts the PHP type from DB type.
	 * @param string $dbType DB type
	 */
	protected function extractType($dbType)
	{
		if(strpos($dbType,'float')!==false || strpos($dbType,'real')!==false)
			$this->type='double';
		elseif(strpos($dbType,'bigint')===false && (strpos($dbType,'int')!==false || strpos($dbType,'smallint')!==false || strpos($dbType,'tinyint')))
			$this->type='integer';
		elseif(strpos($dbType,'bit')!==false)
			$this->type='boolean';
		else
			$this->type='string';
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		if($this->dbType==='timestamp' )
			$this->defaultValue=null;
		else
			parent::extractDefault(str_replace(array('(',')',"'"), '', $defaultValue));
	}

	/**
	 * Extracts size, precision and scale information from column's DB type.
	 * We do nothing here, since sizes and precisions have been computed before.
	 * @param string $dbType the column's DB type
	 */
	protected function extractLimit($dbType)
	{
	}

	/**
	 * Converts the input value to the type that this column is of.
	 * @param mixed $value input value
	 * @return mixed converted value
	 */
	public function typecast($value)
	{
		if($this->type==='boolean')
			return $value ? 1 : 0;
		else
			return parent::typecast($value);
	}
}
