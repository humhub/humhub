<?php
/**
 * CGettextPoFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGettextPoFile represents a PO Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n.gettext
 * @since 1.0
 */
class CGettextPoFile extends CGettextFile
{
	/**
	 * Loads messages from a PO file.
	 * @param string $file file path
	 * @param string $context message context
	 * @return array message translations (source message => translated message)
	 */
	public function load($file,$context)
	{
		$pattern='/(msgctxt\s+"(.*?(?<!\\\\))")?'
			. '\s+msgid\s+"(.*?(?<!\\\\))"'
			. '\s+msgstr\s+"(.*?(?<!\\\\))"/';
		$content=file_get_contents($file);
        $n=preg_match_all($pattern,$content,$matches);
        $messages=array();
        for($i=0;$i<$n;++$i)
        {
        	if($matches[2][$i]===$context)
        	{
	        	$id=$this->decode($matches[3][$i]);
	        	$message=$this->decode($matches[4][$i]);
	        	$messages[$id]=$message;
	        }
        }
        return $messages;
	}

	/**
	 * Saves messages to a PO file.
	 * @param string $file file path
	 * @param array $messages message translations (message id => translated message).
	 * Note if the message has a context, the message id must be prefixed with
	 * the context with chr(4) as the separator.
	 */
	public function save($file,$messages)
	{
		$content='';
		foreach($messages as $id=>$message)
		{
			if(($pos=strpos($id,chr(4)))!==false)
			{
				$content.='msgctxt "'.substr($id,0,$pos)."\"\n";
				$id=substr($id,$pos+1);
			}
			$content.='msgid "'.$this->encode($id)."\"\n";
			$content.='msgstr "'.$this->encode($message)."\"\n\n";
		}
		file_put_contents($file,$content);
	}

	/**
	 * Encodes special characters in a message.
	 * @param string $string message to be encoded
	 * @return string the encoded message
	 */
	protected function encode($string)
	{
		return str_replace(array('"', "\n", "\t", "\r"),array('\\"', "\\n", '\\t', '\\r'),$string);
	}

	/**
	 * Decodes special characters in a message.
	 * @param string $string message to be decoded
	 * @return string the decoded message
	 */
	protected function decode($string)
	{
		return str_replace(array('\\"', "\\n", '\\t', '\\r'),array('"', "\n", "\t", "\r"),$string);
	}
}