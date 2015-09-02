<?php
/**
* YiiMail class file.
*
* @author Jonah Turnquist <poppitypop@gmail.com>
* @link https://code.google.com/p/yii-mail/
* @package Yii-Mail
*/

/**
* YiiMail is an application component used for sending email.
*
* You may configure it as below.  Check the public attributes and setter
* methods of this class for more options.
* <pre>
* return array(
* 	...
* 	'import => array(
* 		...
* 		'ext.mail.YiiMailMessage',
* 	),
* 	'components' => array(
* 		'mail' => array(
* 			'class' => 'ext.yii-mail.YiiMail',
* 			'transportType' => 'php',
* 			'viewPath' => 'application.views.mail',
* 			'logging' => true,
* 			'dryRun' => false
* 		),
* 		...
* 	)
* );
* </pre>
* 
* Example usage:
* <pre>
* $message = new YiiMailMessage;
* $message->setBody('Message content here with HTML', 'text/html');
* $message->subject = 'My Subject';
* $message->addTo('johnDoe@domain.com');
* $message->from = Yii::app()->params['adminEmail'];
* Yii::app()->mail->send($message);
* </pre>
*/
class YiiMail extends CApplicationComponent
{
	/**
	* @var bool whether to log messages using Yii::log().
	* Defaults to true.
	*/
	public $logging = true;
	
	/**
	* @var bool whether to disable actually sending mail.
	* Defaults to false.
	*/
	public $dryRun = false;
	
	/**
	* @var string the delivery type.  Can be either 'php' or 'smtp'.  When 
	* using 'php', PHP's {@link mail()} function will be used.
	* Defaults to 'php'.
	*/
	public $transportType = 'php';
	
	/**
	* @var string the path to the location where mail views are stored.
	* Defaults to 'application.views.mail'.
	*/
	public $viewPath = 'application.views.mail';
	
	/**
	* @var string options specific to the transport type being used.
	* To set options for STMP, set this attribute to an array where the keys 
	* are the option names and the values are their values.
	* Possible options for SMTP are:
	* <ul>
	* 	<li>host</li>
	* 	<li>username</li>
	* 	<li>password</li>
	* 	<li>port</li>
	* 	<li>encryption</li>
	* 	<li>timeout</li>
	* 	<li>extensionHandlers</li>
	* </ul>
	* See the SwiftMailer documentaion for the option meanings.
	*/
	public $transportOptions;
	
	/**
	* @var mixed Holds the SwiftMailer transport
	*/
	protected $transport;

	/**
	* @var mixed Holds the SwiftMailer mailer
	*/
	protected $mailer;

	private static $registeredScripts = false;

	/**
	* Calls the {@link registerScripts()} method.
	*/
	public function init() {
		$this->registerScripts();
		parent::init();	
	}
	
	/**
	* Send a {@link YiiMailMessage} as it would be sent in a mail client.
	* 
	* All recipients (with the exception of Bcc) will be able to see the other
	* recipients this message was sent to.
	* 
	* If you need to send to each recipient without disclosing details about the
	* other recipients see {@link batchSend()}.
	* 
	* Recipient/sender data will be retreived from the {@link YiiMailMessage} 
	* object.
	* 
	* The return value is the number of recipients who were accepted for
	* delivery.
	* 
	* @param YiiMailMessage $message
	* @param array &$failedRecipients, optional
	* @return int
	* @see batchSend()
	*/
	public function send(YiiMailMessage $message, &$failedRecipients = null) {
		if ($this->logging===true) self::log($message);
		if ($this->dryRun===true) return count($message->to);
		else return $this->getMailer()->send($message->message, $failedRecipients);
	}

	/**
	* Send the given {@link YiiMailMessage} to all recipients individually.
	* 
	* This differs from {@link send()} in the way headers are presented to the 
	* recipient.  The only recipient in the "To:" field will be the individual 
	* recipient it was sent to.
	* 
	* If an iterator is provided, recipients will be read from the iterator 
	* one-by-one, otherwise recipient data will be retreived from the 
	* {@link YiiMailMessage} object.
	* 
	* Sender information is always read from the {@link YiiMailMessage} object.
	* 
	* The return value is the number of recipients who were accepted for 
	* delivery.
	* 
	* @param YiiMailMessage $message
	* @param array &$failedRecipients, optional
	* @param Swift_Mailer_RecipientIterator $it, optional
	* @return int
	* @see send()
	*/
	public function batchSend(YiiMailMessage $message, &$failedRecipients = null, Swift_Mailer_RecipientIterator $it = null) {
		if ($this->logging===true) self::log($message);
		if ($this->dryRun===true) return count($message->to);
		else return $this->getMailer()->batchSend($message->message, $failedRecipients, $it);
	}
	
	/**
	* Sends a message in an extremly simple but less extensive way.
	* 
	* @param mixed from address, string or array of the form $address => $name
	* @param mixed to address, string or array of the form $address => $name
	* @param string subject
	* @param string body
	*/
	public function sendSimple($from, $to, $subject, $body) {
		$message = new YiiMailMessage;
		$message->setSubject($subject)
			->setFrom($from)
			->setTo($to)
			->setBody($body, 'text/html');
		
		if ($this->logging===true) self::log($message);
		if ($this->dryRun===true) return count($message->to);
		else return $this->getMailer()->send($message);
	}

	/**
	* Logs a YiiMailMessage in a (hopefully) readable way using Yii::log.
	* @return string log message
	*/
	public static function log(YiiMailMessage $message) {
		$msg = 'Sending email to '.implode(', ', array_keys($message->to))."\n".
			implode('', $message->headers->getAll())."\n".
			$message->body
		;
		Yii::log($msg, CLogger::LEVEL_INFO, 'ext.yii-mail.YiiMail'); // TODO: attempt to determine alias/category at runtime
		return $msg;
	}

	/**
	* Gets the SwiftMailer transport class instance, initializing it if it has 
	* not been created yet
	* @return mixed {@link Swift_MailTransport} or {@link Swift_SmtpTransport}
	*/
	public function getTransport() {
		if ($this->transport===null) {
			switch ($this->transportType) {
				case 'php':
					$this->transport = Swift_MailTransport::newInstance();
					if ($this->transportOptions !== null)
						$this->transport->setExtraParams($this->transportOptions);
					break;
				case 'smtp':
					$this->transport = Swift_SmtpTransport::newInstance();
					foreach ($this->transportOptions as $option => $value)
						$this->transport->{'set'.ucfirst($option)}($value); // sets option with the setter method
					break;
			}
		}
		
		return $this->transport;
	}
	
	/**
	* Gets the SwiftMailer {@link Swift_Mailer} class instance
	* @return Swift_Mailer
	*/
	public function getMailer() {
		if ($this->mailer===null)
			$this->mailer = Swift_Mailer::newInstance($this->getTransport());
			
		return $this->mailer;
	}
	
    /**
    * Registers swiftMailer autoloader and includes the required files
    */
    public function registerScripts() {
    	if (self::$registeredScripts) return;
    	self::$registeredScripts = true;
		require dirname(__FILE__).'/vendors/swiftMailer/classes/Swift.php';
		Yii::registerAutoloader(array('Swift','autoload'));
		require dirname(__FILE__).'/vendors/swiftMailer/swift_init.php';
	}
}