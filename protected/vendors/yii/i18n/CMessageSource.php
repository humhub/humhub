<?php
/**
 * CMessageSource class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMessageSource is the base class for message translation repository classes.
 *
 * A message source is an application component that provides message internationalization (i18n).
 * It stores messages translated in different languages and provides
 * these translated versions when requested.
 *
 * A concrete class must implement {@link loadMessages} or override {@link translateMessage}.
 *
 * @property string $language The language that the source messages are written in.
 * Defaults to {@link CApplication::language application language}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */
abstract class CMessageSource extends CApplicationComponent
{
	/**
	 * @var boolean whether to force message translation when the source and target languages are the same.
	 * Defaults to false, meaning translation is only performed when source and target languages are different.
	 * @since 1.1.4
	 */
	public $forceTranslation=false;

	private $_language;
	private $_messages=array();

	/**
	 * Loads the message translation for the specified language and category.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the loaded messages
	 */
	abstract protected function loadMessages($category,$language);

	/**
	 * @return string the language that the source messages are written in.
	 * Defaults to {@link CApplication::language application language}.
	 */
	public function getLanguage()
	{
		return $this->_language===null ? Yii::app()->sourceLanguage : $this->_language;
	}

	/**
	 * @param string $language the language that the source messages are written in.
	 */
	public function setLanguage($language)
	{
		$this->_language=CLocale::getCanonicalID($language);
	}

	/**
	 * Translates a message to the specified language.
	 *
	 * Note, if the specified language is the same as
	 * the {@link getLanguage source message language}, messages will NOT be translated.
	 *
	 * If the message is not found in the translations, an {@link onMissingTranslation}
	 * event will be raised. Handlers can mark this message or do some
	 * default handling. The {@link CMissingTranslationEvent::message}
	 * property of the event parameter will be returned.
	 *
	 * @param string $category the message category
	 * @param string $message the message to be translated
	 * @param string $language the target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 * @return string the translated message (or the original message if translation is not needed)
	 */
	public function translate($category,$message,$language=null)
	{
		if($language===null)
			$language=Yii::app()->getLanguage();
		if($this->forceTranslation || $language!==$this->getLanguage())
			return $this->translateMessage($category,$message,$language);
		else
			return $message;
	}

	/**
	 * Translates the specified message.
	 * If the message is not found, an {@link onMissingTranslation}
	 * event will be raised.
	 * @param string $category the category that the message belongs to
	 * @param string $message the message to be translated
	 * @param string $language the target language
	 * @return string the translated message
	 */
	protected function translateMessage($category,$message,$language)
	{
		$key=$language.'.'.$category;
		if(!isset($this->_messages[$key]))
			$this->_messages[$key]=$this->loadMessages($category,$language);
		if(isset($this->_messages[$key][$message]) && $this->_messages[$key][$message]!=='')
			return $this->_messages[$key][$message];
		elseif($this->hasEventHandler('onMissingTranslation'))
		{
			$event=new CMissingTranslationEvent($this,$category,$message,$language);
			$this->onMissingTranslation($event);
			return $event->message;
		}
		else
			return $message;
	}

	/**
	 * Raised when a message cannot be translated.
	 * Handlers may log this message or do some default handling.
	 * The {@link CMissingTranslationEvent::message} property
	 * will be returned by {@link translateMessage}.
	 * @param CMissingTranslationEvent $event the event parameter
	 */
	public function onMissingTranslation($event)
	{
		$this->raiseEvent('onMissingTranslation',$event);
	}
}


/**
 * CMissingTranslationEvent represents the parameter for the {@link CMessageSource::onMissingTranslation onMissingTranslation} event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */
class CMissingTranslationEvent extends CEvent
{
	/**
	 * @var string the message to be translated
	 */
	public $message;
	/**
	 * @var string the category that the message belongs to
	 */
	public $category;
	/**
	 * @var string the ID of the language that the message is to be translated to
	 */
	public $language;

	/**
	 * Constructor.
	 * @param mixed $sender sender of this event
	 * @param string $category the category that the message belongs to
	 * @param string $message the message to be translated
	 * @param string $language the ID of the language that the message is to be translated to
	 */
	public function __construct($sender,$category,$message,$language)
	{
		parent::__construct($sender);
		$this->message=$message;
		$this->category=$category;
		$this->language=$language;
	}
}
