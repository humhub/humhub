<?php
/**
 * CGettextMoFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGettextMoFile represents an MO Gettext message file.
 *
 * This class is written by adapting Michael's Gettext_MO class in PEAR.
 * Please refer to the following license terms.
 *
 * Copyright (c) 2004-2005, Michael Wallner <mike@iworks.at>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n.gettext
 * @since 1.0
 */
class CGettextMoFile extends CGettextFile
{
	/**
	 * @var boolean whether to use Big Endian when reading and writing an integer.
	 */
	public $useBigEndian=false;

	/**
	 * Constructor.
	 * @param boolean $useBigEndian whether to use Big Endian when reading and writing an integer.
	 */
	public function __construct($useBigEndian=false)
	{
		$this->useBigEndian=$useBigEndian;
	}

	/**
	 * Loads messages from an MO file.
	 * @param string $file file path
	 * @param string $context message context
	 * @return array message translations (source message => translated message)
	 */
	public function load($file,$context)
	{
		if(!($fr=@fopen($file,'rb')))
			throw new CException(Yii::t('yii','Unable to read file "{file}".',
				array('{file}'=>$file)));

		if(!@flock($fr,LOCK_SH))
			throw new CException(Yii::t('yii','Unable to lock file "{file}" for reading.',
				array('{file}'=>$file)));

		$magic=current($array=unpack('c',$this->readByte($fr,4)));
		if($magic==-34)
			$this->useBigEndian=false;
		elseif($magic==-107)
			$this->useBigEndian=true;
		else
			throw new CException(Yii::t('yii','Invalid MO file: {file} (magic: {magic}).',
				array('{file}'=>$file,'{magic}'=>$magic)));

		if(($revision=$this->readInteger($fr))!=0)
			throw new CException(Yii::t('yii','Invalid MO file revision: {revision}.',
				array('{revision}'=>$revision)));

		$count=$this->readInteger($fr);
		$sourceOffset=$this->readInteger($fr);
		$targetOffset=$this->readInteger($fr);

		$sourceLengths=array();
		$sourceOffsets=array();
		fseek($fr,$sourceOffset);
		for($i=0;$i<$count;++$i)
		{
			$sourceLengths[]=$this->readInteger($fr);
			$sourceOffsets[]=$this->readInteger($fr);
		}

		$targetLengths=array();
		$targetOffsets=array();
		fseek($fr,$targetOffset);
		for($i=0;$i<$count;++$i)
		{
			$targetLengths[]=$this->readInteger($fr);
			$targetOffsets[]=$this->readInteger($fr);
		}

		$messages=array();
		for($i=0;$i<$count;++$i)
		{
			$id=$this->readString($fr,$sourceLengths[$i],$sourceOffsets[$i]);
			$pos = strpos($id,chr(4));

			if(($context && $pos!==false && substr($id,0,$pos)===$context) || (!$context && $pos===false))
			{
				if($pos !== false)
					$id=substr($id,$pos+1);

				$message=$this->readString($fr,$targetLengths[$i],$targetOffsets[$i]);
				$messages[$id]=$message;
			}
		}

		@flock($fr,LOCK_UN);
		@fclose($fr);

		return $messages;
	}

	/**
	 * Saves messages to an MO file.
	 * @param string $file file path
	 * @param array $messages message translations (message id => translated message).
	 * Note if the message has a context, the message id must be prefixed with
	 * the context with chr(4) as the separator.
	 */
	public function save($file,$messages)
	{
		if(!($fw=@fopen($file,'wb')))
			throw new CException(Yii::t('yii','Unable to write file "{file}".',
				array('{file}'=>$file)));

		if(!@flock($fw,LOCK_EX))
			throw new CException(Yii::t('yii','Unable to lock file "{file}" for writing.',
				array('{file}'=>$file)));

		// magic
		if($this->useBigEndian)
			$this->writeByte($fw,pack('c*', 0x95, 0x04, 0x12, 0xde));
		else
			$this->writeByte($fw,pack('c*', 0xde, 0x12, 0x04, 0x95));

		// revision
		$this->writeInteger($fw,0);

		// message count
		$n=count($messages);
		$this->writeInteger($fw,$n);

		// offset of source message table
		$offset=28;
		$this->writeInteger($fw,$offset);
		$offset+=($n*8);
		$this->writeInteger($fw,$offset);
		// hashtable size, omitted
		$this->writeInteger($fw,0);
		$offset+=($n*8);
		$this->writeInteger($fw,$offset);

		// length and offsets for source messagess
		foreach(array_keys($messages) as $id)
		{
			$len=strlen($id);
			$this->writeInteger($fw,$len);
			$this->writeInteger($fw,$offset);
			$offset+=$len+1;
		}

		// length and offsets for target messagess
		foreach($messages as $message)
		{
			$len=strlen($message);
			$this->writeInteger($fw,$len);
			$this->writeInteger($fw,$offset);
			$offset+=$len+1;
		}

		// source messages
		foreach(array_keys($messages) as $id)
			$this->writeString($fw,$id);

		// target messages
		foreach($messages as $message)
			$this->writeString($fw,$message);

		@flock($fw,LOCK_UN);
		@fclose($fw);
	}

	/**
	 * Reads one or several bytes.
	 * @param resource $fr file handle
	 * @param integer $n number of bytes to read
	 * @return string bytes
	 */
	protected function readByte($fr,$n=1)
	{
		if($n>0)
			return fread($fr,$n);
	}

	/**
	 * Writes bytes.
	 * @param resource $fw file handle
	 * @param string $data the data
	 * @return integer how many bytes are written
	 */
	protected function writeByte($fw,$data)
	{
		return fwrite($fw,$data);
	}

	/**
	 * Reads a 4-byte integer.
	 * @param resource $fr file handle
	 * @return integer the result
	 * @see useBigEndian
	 */
	protected function readInteger($fr)
	{
		return current($array=unpack($this->useBigEndian ? 'N' : 'V', $this->readByte($fr,4)));
	}

	/**
	 * Writes a 4-byte integer.
	 * @param resource $fw file handle
	 * @param integer $data the data
	 * @return integer how many bytes are written
	 */
	protected function writeInteger($fw,$data)
	{
		return $this->writeByte($fw,pack($this->useBigEndian ? 'N' : 'V', (int)$data));
	}

	/**
	 * Reads a string.
	 * @param resource $fr file handle
	 * @param integer $length string length
	 * @param integer $offset offset of the string in the file. If null, it reads from the current position.
	 * @return string the result
	 */
	protected function readString($fr,$length,$offset=null)
	{
		if($offset!==null)
			fseek($fr,$offset);
		return $this->readByte($fr,$length);
	}

	/**
	 * Writes a string.
	 * @param resource $fw file handle
	 * @param string $data the string
	 * @return integer how many bytes are written
	 */
	protected function writeString($fw,$data)
	{
		return $this->writeByte($fw,$data."\0");
	}
}
