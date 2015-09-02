<?php
/**
 * CHttpCacheFilter class file.
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CHttpCacheFilter implements http caching. It works a lot like {@link COutputCache}
 * as a filter, except that content caching is being done on the client side.
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @package system.web.filters
 * @since 1.1.11
 */
class CHttpCacheFilter extends CFilter
{
	/**
	 * @var string|integer Timestamp for the last modification date.
	 * Must be either a string parsable by {@link http://php.net/strtotime strtotime()}
	 * or an integer representing a unix timestamp.
	 */
	public $lastModified;
	/**
	 * @var string|callback PHP Expression for the last modification date.
	 * If set, this takes precedence over {@link lastModified}.
	 *
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $lastModifiedExpression;
	/**
	 * @var mixed Seed for the ETag.
	 * Can be anything that passes through {@link http://php.net/serialize serialize()}.
	 */
	public $etagSeed;
	/**
	 * @var string|callback Expression for the ETag seed.
	 * If set, this takes precedence over {@link etagSeed}.
	 *
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $etagSeedExpression;
	/**
	 * @var string Http cache control headers. Set this to an empty string in order to keep this
	 * header from being sent entirely.
	 */
	public $cacheControl='max-age=3600, public';

	/**
	 * Performs the pre-action filtering.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * @return boolean whether the filtering process should continue and the action should be executed.
	 */
	public function preFilter($filterChain)
	{
		// Only cache GET and HEAD requests
		if(!in_array(Yii::app()->getRequest()->getRequestType(), array('GET', 'HEAD')))
			return true;

		$lastModified=$this->getLastModifiedValue();
		$etag=$this->getEtagValue();

		if($etag===false&&$lastModified===false)
			return true;

		if($etag)
			header('ETag: '.$etag);

		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])&&isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			if($this->checkLastModified($lastModified)&&$this->checkEtag($etag))
			{
				$this->send304Header();
				$this->sendCacheControlHeader();
				return false;
			}
		}
		elseif(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			if($this->checkLastModified($lastModified))
			{
				$this->send304Header();
				$this->sendCacheControlHeader();
				return false;
			}
		}
		elseif(isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			if($this->checkEtag($etag))
			{
				$this->send304Header();
				$this->sendCacheControlHeader();
				return false;
			}

		}

		if($lastModified)
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');

		$this->sendCacheControlHeader();
		return true;
	}

	/**
	 * Gets the last modified value from either {@link lastModifiedExpression} or {@link lastModified}
	 * and converts it into a unix timestamp if necessary
	 * @throws CException
	 * @return integer|boolean A unix timestamp or false if neither lastModified nor
	 * lastModifiedExpression have been set
	 */
	protected function getLastModifiedValue()
	{
		if($this->lastModifiedExpression)
		{
			$value=$this->evaluateExpression($this->lastModifiedExpression);
			if(is_numeric($value)&&$value==(int)$value)
				return $value;
			elseif(($lastModified=strtotime($value))===false)
				throw new CException(Yii::t('yii','Invalid expression for CHttpCacheFilter.lastModifiedExpression: The evaluation result "{value}" could not be understood by strtotime()',
					array('{value}'=>$value)));
			return $lastModified;
		}

		if($this->lastModified)
		{
			if(is_numeric($this->lastModified)&&$this->lastModified==(int)$this->lastModified)
				return $this->lastModified;
			elseif(($lastModified=strtotime($this->lastModified))===false)
				throw new CException(Yii::t('yii','CHttpCacheFilter.lastModified contained a value that could not be understood by strtotime()'));
			return $lastModified;
		}
		return false;
	}

	/**
	 *  Gets the ETag out of either {@link etagSeedExpression} or {@link etagSeed}
	 *  @return string|boolean Either a quoted string serving as ETag or false if neither etagSeed nor etagSeedExpression have been set
	 */
	protected function getEtagValue()
	{
		if($this->etagSeedExpression)
			return $this->generateEtag($this->evaluateExpression($this->etagSeedExpression));
		elseif($this->etagSeed)
			return $this->generateEtag($this->etagSeed);
		return false;
	}

	/**
	 * Check if the etag supplied by the client matches our generated one
	 * @param string $etag the supplied etag
	 * @return boolean true if the supplied etag matches $etag
	 */
	protected function checkEtag($etag)
	{
		return isset($_SERVER['HTTP_IF_NONE_MATCH'])&&$_SERVER['HTTP_IF_NONE_MATCH']==$etag;
	}

	/**
	 * Checks if the last modified date supplied by the client is still up to date
	 * @param integer $lastModified the last modified date
	 * @return boolean true if the last modified date sent by the client is newer or equal to $lastModified
	 */
	protected function checkLastModified($lastModified)
	{
		return isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])&&@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])>=$lastModified;
	}

	/**
	 * Sends the 304 HTTP status code to the client
	 */
	protected function send304Header()
	{
		header('HTTP/1.1 304 Not Modified');
	}

	/**
	 * Sends the cache control header to the client
	 * @see cacheControl
	 * @since 1.1.12
	 */
	protected function sendCacheControlHeader()
	{
		if(Yii::app()->session->isStarted)
		{
			session_cache_limiter('public');
			header('Pragma:',true);
		}
		header('Cache-Control: '.$this->cacheControl,true);
	}

	/**
	 * Generates a quoted string out of the seed
	 * @param mixed $seed Seed for the ETag
	 */
	protected function generateEtag($seed)
	{
		return '"'.base64_encode(sha1(serialize($seed),true)).'"';
	}
}
