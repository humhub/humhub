<?php
/**
 * CChainedLogFilter class file
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CChainedLogFilter allows you to attach multiple log filters to a log route (See {@link CLogRoute::$filter} for details).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @package system.logging
 * @since 1.1.13
 */
class CChainedLogFilter extends CComponent implements ILogFilter
{
	/**
	 * @var array list of filters to apply to the logs.
	 * The value of each array element will be passed to {@link Yii::createComponent} to create
	 * a log filter object. As a result, this can be either a string representing the
	 * filter class name or an array representing the filter configuration.
	 * In general, the log filter classes should implement {@link ILogFilter} interface.
	 * Filters will be applied in the order they are defined.
	 */
	public $filters=array();

	/**
	 * Filters the given log messages by applying all filters configured by {@link filters}.
	 * @param array $logs the log messages
	 */
	public function filter(&$logs)
	{
		foreach($this->filters as $filter)
			Yii::createComponent($filter)->filter($logs);
	}
}