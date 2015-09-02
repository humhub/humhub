<?php
/**
 * CPradoViewRenderer class file.
 *
 * @author Steve Heyns http://customgothic.com/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPradoViewRenderer implements a view renderer that allows users to use a template syntax similar to PRADO templates.
 *
 * To use CPradoViewRenderer, configure it as an application component named "viewRenderer" in the application configuration:
 * <pre>
 * array(
 *     'components'=>array(
 *         ......
 *         'viewRenderer'=>array(
 *             'class'=>'CPradoViewRenderer',
 *         ),
 *     ),
 * )
 * </pre>
 *
 * CPradoViewRenderer allows you to write view files with the following syntax:
 * <pre>
 * // PHP tags:
 * <%= expression %>
 * // <?php echo expression ?>
 * <% statement %>
 * // <?php statement ?></li>
 *
 * // component tags:
 * <com:WigetClass name1="value1" name2='value2' name3={value3} >
 * // <?php $this->beginWidget('WigetClass',
 * // array('name1'=>"value1", 'name2'=>'value2', 'name3'=>value3)); ?>
 * </com:WigetClass >
 * // <?php $this->endWidget('WigetClass'); ?>
 * <com:WigetClass name1="value1" name2='value2' name3={value3} />
 * // <?php $this->widget('WigetClass',
 * // array('name1'=>"value1", 'name2'=>'value2', 'name3'=>value3)); ?>
 *
 * // cache tags:
 * <cache:fragmentID name1="value1" name2='value2' name3={value3} >
 * // <?php if($this->beginCache('fragmentID',
 * // array('name1'=>"value1", 'name2'=>'value2', 'name3'=>value3))): ?>
 * </cache:fragmentID >
 * // <?php $this->endCache('fragmentID'); endif; ?>
 *
 * // clip tags:
 * <clip:clipID >
 * // <?php $this->beginClip('clipID'); ?>
 * </clip:clipID >
 * // <?php $this->endClip('clipID'); ?>
 *
 * // comment tags:
 * <!--- comments --->
 * // the whole tag will be stripped off
 * </pre>
 *
 * @author Steve Heyns http://customgothic.com/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.renderers
 * @since 1.0
 */
class CPradoViewRenderer extends CViewRenderer
{
	private $_input;
	private $_output;
	private $_sourceFile;

	/**
	 * Parses the source view file and saves the results as another file.
	 * This method is required by the parent class.
	 * @param string $sourceFile the source view file path
	 * @param string $viewFile the resulting view file path
	 */
	protected function generateViewFile($sourceFile,$viewFile)
	{
		static $regexRules=array(
			'<%=?\s*(.*?)\s*%>',		// PHP statements or expressions
			'<\/?(com|cache|clip):([\w\.]+)\s*((?:\s*\w+\s*=\s*\'.*?(?<!\\\\)\'|\s*\w+\s*=\s*".*?(?<!\\\\)"|\s*\w+\s*=\s*\{.*?\})*)\s*\/?>', // component tags
			'<!---.*?--->',	// template comments
		);
		$this->_sourceFile=$sourceFile;
		$this->_input=file_get_contents($sourceFile);
		$n=preg_match_all('/'.implode('|',$regexRules).'/msS',$this->_input,$matches,PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
		$textStart=0;
        $this->_output="<?php /* source file: $sourceFile */ ?>\n";
		for($i=0;$i<$n;++$i)
		{
			$match=&$matches[$i];
			$str=$match[0][0];
			$matchStart=$match[0][1];
			$matchEnd=$matchStart+strlen($str)-1;

			if($matchStart>$textStart)
				$this->_output.=substr($this->_input,$textStart,$matchStart-$textStart);
			$textStart=$matchEnd+1;

			if(strpos($str,'<com:')===0)	// opening component tag
			{
				$type=$match[3][0];
				if($str[strlen($str)-2]!=='/')  // open tag
					$this->_output.=$this->processBeginWidget($type,$match[4][0],$match[2][1]);
				else
					$this->_output.=$this->processWidget($type,$match[4][0],$match[2][1]);
			}
			elseif(strpos($str,'</com:')===0)	// closing component tag
				$this->_output.=$this->processEndWidget($match[3][0],$match[2][1]);
			elseif(strpos($str,'<cache:')===0)	// opening cache tag
			{
				$id=$match[3][0];
				if($str[strlen($str)-2]!=='/')  // open tag
					$this->_output.=$this->processBeginCache($id,$match[4][0],$match[2][1]);
				else
					$this->_output.=$this->processCache($id,$match[4][0],$match[2][1]);
			}
			elseif(strpos($str,'</cache:')===0)	// closing cache tag
				$this->_output.=$this->processEndCache($match[3][0],$match[2][1]);
			elseif(strpos($str,'<clip:')===0)	// opening clip tag
			{
				$id=$match[3][0];
				if($str[strlen($str)-2]!=='/')  // open tag
					$this->_output.=$this->processBeginClip($id,$match[4][0],$match[2][1]);
				else
					$this->_output.=$this->processClip($id,$match[4][0],$match[2][1]);
			}
			elseif(strpos($str,'</clip:')===0)	// closing clip tag
				$this->_output.=$this->processEndClip($match[3][0],$match[2][1]);
			elseif(strpos($str,'<%=')===0)	// expression
				$this->_output.=$this->processExpression($match[1][0],$match[1][1]);
			elseif(strpos($str,'<%')===0)	// statement
				$this->_output.=$this->processStatement($match[1][0],$match[1][1]);
		}
		if($textStart<strlen($this->_input))
			$this->_output.=substr($this->_input,$textStart);

		file_put_contents($viewFile,$this->_output);
	}

	/*
	 * @param string $type type
	 * @param string $attributes attributes
	 * @param string $offset offset
	 */
	private function processWidget($type,$attributes,$offset)
	{
		$attrs=$this->processAttributes($attributes);
		if(empty($attrs))
			return $this->generatePhpCode("\$this->widget('$type');",$offset);
		else
			return $this->generatePhpCode("\$this->widget('$type', array($attrs));",$offset);
	}

	/*
	 * @param string $type type
	 * @param string $attributes attributes
	 * @param string $offset offset
	 */
	private function processBeginWidget($type,$attributes,$offset)
	{
		$attrs=$this->processAttributes($attributes);
		if(empty($attrs))
			return $this->generatePhpCode("\$this->beginWidget('$type');",$offset);
		else
			return $this->generatePhpCode("\$this->beginWidget('$type', array($attrs));",$offset);
	}

	/*
	 * @param string $type type
	 * @param string $offset offset
	 */
	private function processEndWidget($type,$offset)
	{
		return $this->generatePhpCode("\$this->endWidget('$type');",$offset);
	}

	/*
	 * @param string $id id
	 * @param string $attributes attributes
	 * @param string $offset offset
	 */
	private function processCache($id,$attributes,$offset)
	{
		return $this->processBeginCache($id,$attributes,$offset) . $this->processEndCache($id,$offset);
	}

	/*
	 * @param string $id id
	 * @param string $attributes attributes
	 * @param string $offset offset
	 */
	private function processBeginCache($id,$attributes,$offset)
	{
		$attrs=$this->processAttributes($attributes);
		if(empty($attrs))
			return $this->generatePhpCode("if(\$this->beginCache('$id')):",$offset);
		else
			return $this->generatePhpCode("if(\$this->beginCache('$id', array($attrs))):",$offset);
	}

	/*
	 * @param string $id id
	 * @param string $offset offset
	 */
	private function processEndCache($id,$offset)
	{
		return $this->generatePhpCode("\$this->endCache('$id'); endif;",$offset);
	}

	/*
	 * @param string $id id
	 * @param string $attributes attributes
	 * @param string $offset offset
	 */
	private function processClip($id,$attributes,$offset)
	{
		return $this->processBeginClip($id,$attributes,$offset) . $this->processEndClip($id,$offset);
	}

	/*
	 * @param string $id id
	 * @param string $attributes attributes
	 * @param string $offset offset
	 */
	private function processBeginClip($id,$attributes,$offset)
	{
		$attrs=$this->processAttributes($attributes);
		if(empty($attrs))
			return $this->generatePhpCode("\$this->beginClip('$id');",$offset);
		else
			return $this->generatePhpCode("\$this->beginClip('$id', array($attrs));",$offset);
	}

	/*
	 * @param string $id id
	 * @param string $offset offset
	 */
	private function processEndClip($id,$offset)
	{
		return $this->generatePhpCode("\$this->endClip('$id');",$offset);
	}

	/*
	 * @param string $expression expression
	 * @param string $offset offset
	 */
	private function processExpression($expression,$offset)
	{
		return $this->generatePhpCode('echo '.$expression,$offset);
	}

	/*
	 * @param string $statement statement
	 * @param string $offset offset
	 */
	private function processStatement($statement,$offset)
	{
		return $this->generatePhpCode($statement,$offset);
	}

	/*
	 * @param string $code code
	 * @param string $offset offset
	 */
	private function generatePhpCode($code,$offset)
	{
		$line=$this->getLineNumber($offset);
		$code=str_replace('__FILE__',var_export($this->_sourceFile,true),$code);
		return "<?php /* line $line */ $code ?>";
	}

	/*
	 * @param string $str str
	 */
	private function processAttributes($str)
	{
		static $pattern='/(\w+)\s*=\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)"|\{.*?\})/msS';
		$attributes=array();
		$n=preg_match_all($pattern,$str,$matches,PREG_SET_ORDER);
		for($i=0;$i<$n;++$i)
		{
			$match=&$matches[$i];
			$name=$match[1];
			$value=$match[2];
			if($value[0]==='{')
				$attributes[]="'$name'=>".str_replace('__FILE__',$this->_sourceFile,substr($value,1,-1));
			else
				$attributes[]="'$name'=>$value";
		}
		return implode(', ',$attributes);
	}

	/*
	 * @param string $offset offset
	 */
	private function getLineNumber($offset)
	{
		return count(explode("\n",substr($this->_input,0,$offset)));
	}
}
