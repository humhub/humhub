<?php
/**
 * CDbMessageSource class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbMessageSource represents a message source that stores translated messages in database.
 *
 * The database must contain the following two tables:
 * <pre>
 * CREATE TABLE SourceMessage
 * (
 *     id INTEGER PRIMARY KEY,
 *     category VARCHAR(32),
 *     message TEXT
 * );
 * CREATE TABLE Message
 * (
 *     id INTEGER,
 *     language VARCHAR(16),
 *     translation TEXT,
 *     PRIMARY KEY (id, language),
 *     CONSTRAINT FK_Message_SourceMessage FOREIGN KEY (id)
 *          REFERENCES SourceMessage (id) ON DELETE CASCADE ON UPDATE RESTRICT
 * );
 * </pre>
 * The 'SourceMessage' table stores the messages to be translated, and the 'Message' table
 * stores the translated messages. The name of these two tables can be customized by setting
 * {@link sourceMessageTable} and {@link translatedMessageTable}, respectively.
 *
 * When {@link cachingDuration} is set as a positive number, message translations will be cached.
 *
 * @property CDbConnection $dbConnection The DB connection used for the message source.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */
class CDbMessageSource extends CMessageSource
{
	const CACHE_KEY_PREFIX='Yii.CDbMessageSource.';
	/**
	 * @var string the ID of the database connection application component. Defaults to 'db'.
	 */
	public $connectionID='db';
	/**
	 * @var string the name of the source message table. Defaults to 'SourceMessage'.
	 */
	public $sourceMessageTable='SourceMessage';
	/**
	 * @var string the name of the translated message table. Defaults to 'Message'.
	 */
	public $translatedMessageTable='Message';
	/**
	 * @var integer the time in seconds that the messages can remain valid in cache.
	 * Defaults to 0, meaning the caching is disabled.
	 */
	public $cachingDuration=0;
	/**
	 * @var string the ID of the cache application component that is used to cache the messages.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching the messages.
	 */
	public $cacheID='cache';

	/**
	 * Loads the message translation for the specified language and category.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the loaded messages
	 */
	protected function loadMessages($category,$language)
	{
		if($this->cachingDuration>0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key=self::CACHE_KEY_PREFIX.'.messages.'.$category.'.'.$language;
			if(($data=$cache->get($key))!==false)
				return unserialize($data);
		}

		$messages=$this->loadMessagesFromDb($category,$language);

		if(isset($cache))
			$cache->set($key,serialize($messages),$this->cachingDuration);

		return $messages;
	}

	private $_db;

	/**
	 * Returns the DB connection used for the message source.
	 * @return CDbConnection the DB connection used for the message source.
	 * @since 1.1.5
	 */
	public function getDbConnection()
	{
		if($this->_db===null)
		{
			$this->_db=Yii::app()->getComponent($this->connectionID);
			if(!$this->_db instanceof CDbConnection)
				throw new CException(Yii::t('yii','CDbMessageSource.connectionID is invalid. Please make sure "{id}" refers to a valid database application component.',
					array('{id}'=>$this->connectionID)));
		}
		return $this->_db;
	}

	/**
	 * Loads the messages from database.
	 * You may override this method to customize the message storage in the database.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the messages loaded from database
	 * @since 1.1.5
	 */
	protected function loadMessagesFromDb($category,$language)
	{
		$sql=<<<EOD
SELECT t1.message AS message, t2.translation AS translation
FROM {$this->sourceMessageTable} t1, {$this->translatedMessageTable} t2
WHERE t1.id=t2.id AND t1.category=:category AND t2.language=:language
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		$command->bindValue(':category',$category);
		$command->bindValue(':language',$language);
		$messages=array();
		foreach($command->queryAll() as $row)
			$messages[$row['message']]=$row['translation'];

		return $messages;
	}
}