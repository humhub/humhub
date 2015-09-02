<?php
/**
 * CWsdlGenerator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWsdlGenerator generates the WSDL for a given service class.
 *
 * The WSDL generation is based on the doc comments found in the service class file.
 * In particular, it recognizes the '@soap' tag in the comment and extracts
 * API method and type definitions.
 *
 * In a service class, a remote invokable method must be a public method with a doc
 * comment block containing the '@soap' tag. In the doc comment, the type and name
 * of every input parameter and the type of the return value should be declared using
 * the standard phpdoc format.
 *
 * CWsdlGenerator recognizes the following primitive types (case-sensitive) in
 * the parameter and return type declarations:
 * <ul>
 * <li>str/string: maps to xsd:string;</li>
 * <li>int/integer: maps to xsd:int;</li>
 * <li>float/double: maps to xsd:float;</li>
 * <li>bool/boolean: maps to xsd:boolean;</li>
 * <li>date: maps to xsd:date;</li>
 * <li>time: maps to xsd:time;</li>
 * <li>datetime: maps to xsd:dateTime;</li>
 * <li>array: maps to xsd:string;</li>
 * <li>object: maps to xsd:struct;</li>
 * <li>mixed: maps to xsd:anyType.</li>
 * </ul>
 *
 * If a type is not a primitive type, it is considered as a class type, and
 * CWsdlGenerator will look for its property declarations. Only public properties
 * are considered, and they each must be associated with a doc comment block containg
 * the '@soap' tag. The doc comment block should declare the type of the property.
 *
 * CWsdlGenerator recognizes the array type with the following format:
 * <pre>
 * typeName[]: maps to tns:typeNameArray
 * </pre>
 *
 * The following is an example declaring a remote invokable method:
 * <pre>
 * / **
 *   * A foo method.
 *   * @param string name of something
 *   * @param string value of something
 *   * @return string[] some array
 *   * @soap
 *   * /
 * public function foo($name,$value) {...}
 * </pre>
 *
 * And the following is an example declaring a class with remote accessible properties:
 * <pre>
 * class Foo {
 *     / **
 *       * @var string name of foo {nillable=1, minOccurs=0, maxOccurs=2}
 *       * @soap
 *       * /
 *     public $name;
 *     / **
 *       * @var Member[] members of foo
 *       * @soap
 *       * /
 *     public $members;
 * }
 * </pre>
 * In the above, the 'members' property is an array of 'Member' objects. Since 'Member' is not
 * a primitive type, CWsdlGenerator will look further to find the definition of 'Member'.
 *
 * Optionally, extra attributes (nillable, minOccurs, maxOccurs) can be defined for each
 * property by enclosing definitions into curly brackets and separated by comma like so:
 *
 * {[attribute1 = value1][, attribute2 = value2], ...}
 *
 * where the attribute can be one of following:
 * <ul>
 * <li>nillable = [0|1|true|false]</li>
 * <li>minOccurs = n; where n>=0</li>
 * <li>maxOccurs = n; where [n>=0|unbounded]</li>
 * </ul>
 *
 * Additionally, each complex data type can have assigned a soap indicator flag declaring special usage for such a data type.
 * A soap indicator must be declared in the doc comment block with the '@soap-indicator' tag.
 * Following soap indicators are currently supported:
 * <ul>
 * <li>all - (default) allows any sorting order of child nodes</li>
 * <li>sequence - all child nodes in WSDL XML file will be expected in predefined order</li>
 * <li>choice - supplied can be either of the child elements</li>
 * </ul>
 * The Group indicators can be also injected via custom soap definitions as XML node into WSDL structure.
 *
 * In the following example, class Foo will create a XML node <xsd:Foo><xsd:sequence> ... </xsd:sequence></xsd:Foo> with children attributes expected in pre-defined order.
 * <pre>
 * / *
 *   * @soap-indicator sequence
 *   * /
 * class Foo {
 *     ...
 * }
 * </pre>
 * For more on soap indicators, see See {@link http://www.w3schools.com/schema/schema_complex_indicators.asp}.
 *
 * Since the variability of WSDL definitions is virtually unlimited, a special doc comment tag '@soap-wsdl' can be used in order to inject any custom XML string into generated WSDL file.
 * If such a block of the code is found in class's comment block, then it will be used instead of parsing and generating standard attributes within the class.
 * This gives virtually unlimited flexibility in defining data structures of any complexity.
 * Following is an example of defining custom piece of WSDL XML node:
 * <pre>
 * / *
 *   * @soap-wsdl <xsd:sequence>
 *   * @soap-wsdl 	<xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="name" type="xsd:string"/>
 *   * @soap-wsdl 	<xsd:choice minOccurs="1" maxOccurs="1" nillable="false">
 *   * @soap-wsdl 		<xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="age" type="xsd:integer"/>
 *   * @soap-wsdl 		<xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="date_of_birth" type="xsd:date"/>
 *   * @soap-wsdl 	</xsd:choice>
 *   * @soap-wsdl </xsd:sequence>
 *   * /
 * class User {
 *     / **
 *       * @var string User name {minOccurs=1, maxOccurs=1}
 *       * @soap
 *       * /
 *     public $name;
 *     / **
 *       * @var integer User age {nillable=0, minOccurs=1, maxOccurs=1}
 *       * @example 35
 *       * @soap
 *       * /
 *     public $age;
 *     / **
 *       * @var date User's birthday {nillable=0, minOccurs=1, maxOccurs=1}
 *       * @example 1980-05-27
 *       * @soap
 *       * /
 *     public $date_of_birth;
 * }
 * </pre>
 * In the example above, WSDL generator would inject under XML node <xsd:User> the code block defined by @soap-wsdl lines.
 *
 * By inserting into SOAP URL link the parameter "?makedoc", WSDL generator will output human-friendly overview of all complex data types rather than XML WSDL file.
 * Each complex type is described in a separate HTML table and recognizes also the '@example' PHPDoc tag. See {@link buildHtmlDocs()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.services
 * @since 1.0
 */
class CWsdlGenerator extends CComponent
{
	/**
	 * @var string the namespace to be used in the generated WSDL.
	 * If not set, it defaults to the name of the class that WSDL is generated upon.
	 */
	public $namespace;
	/**
	 * @var string the name of the generated WSDL.
	 * If not set, it defaults to "urn:{$className}wsdl".
	 */
	public $serviceName;

	protected static $typeMap=array(
		'string'=>'xsd:string',
		'str'=>'xsd:string',
		'int'=>'xsd:int',
		'integer'=>'xsd:integer',
		'float'=>'xsd:float',
		'double'=>'xsd:float',
		'bool'=>'xsd:boolean',
		'boolean'=>'xsd:boolean',
		'date'=>'xsd:date',
		'time'=>'xsd:time',
		'datetime'=>'xsd:dateTime',
		'array'=>'soap-enc:Array',
		'object'=>'xsd:struct',
		'mixed'=>'xsd:anyType',
	);

	/**
	* @var array List of recognized SOAP operations that will become remotely available.
	* All methods with declared @soap parameter will be included here in the format operation1 => description1, operation2 => description2, ..
	*/
	protected $operations;

	/**
	* @var array List of complex types used by operations.
	* If an SOAP operation defines complex input or output type, all objects are included here containing all sub-parameters.
	* For instance, if an SOAP operation "createUser" requires complex input object "User", then the object "User" will be included here with declared subparameters such as "firstname", "lastname", etc..
	*/
	protected $types;

	/**
	* @var array Map of request and response types for all operations.
	*/
	protected $messages;

	/**
	 * Generates the WSDL for the given class.
	 * @param string $className class name
	 * @param string $serviceUrl Web service URL
	 * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
	 * @return string the generated WSDL
	 */
	public function generateWsdl($className, $serviceUrl, $encoding='UTF-8')
	{
		$this->operations=array();
		$this->types=array();
		$this->messages=array();
		if($this->serviceName===null)
			$this->serviceName=$className;
		if($this->namespace===null)
			$this->namespace='urn:'.str_replace('\\','/',$className).'wsdl';

		$reflection=new ReflectionClass($className);
		foreach($reflection->getMethods() as $method)
		{
			if($method->isPublic())
				$this->processMethod($method);
		}

		$wsdl=$this->buildDOM($serviceUrl,$encoding)->saveXML();

		if(isset($_GET['makedoc']))
			$this->buildHtmlDocs();

		return $wsdl;
	}

	/**
	 * @param ReflectionMethod $method method
	 */
	protected function processMethod($method)
	{
		$comment=$method->getDocComment();
		if(strpos($comment,'@soap')===false)
			return;
		$comment=strtr($comment,array("\r\n"=>"\n","\r"=>"\n")); // make line endings consistent: win -> unix, mac -> unix

		$methodName=$method->getName();
		$comment=preg_replace('/^\s*\**(\s*?$|\s*)/m','',$comment);
		$params=$method->getParameters();
		$message=array();
		$n=preg_match_all('/^@param\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches);
		if($n>count($params))
			$n=count($params);
		for($i=0;$i<$n;++$i)
			$message[$params[$i]->getName()]=array($this->processType($matches[1][$i]), trim($matches[3][$i])); // name => type, doc

		$this->messages[$methodName.'Request']=$message;

		if(preg_match('/^@return\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches))
			$return=array($this->processType($matches[1]),trim($matches[2])); // type, doc
		else
			$return=null;
		$this->messages[$methodName.'Response']=array('return'=>$return);

		if(preg_match('/^\/\*+\s*([^@]*?)\n@/s',$comment,$matches))
			$doc=trim($matches[1]);
		else
			$doc='';
		$this->operations[$methodName]=$doc;
	}

	/**
	 * @param string $type PHP variable type
	 */
	protected function processType($type)
	{
		if(isset(self::$typeMap[$type]))
			return self::$typeMap[$type];
		elseif(isset($this->types[$type]))
			return is_array($this->types[$type]) ? 'tns:'.$type : $this->types[$type];
		elseif(($pos=strpos($type,'[]'))!==false)
		{	// array of types
			$type=substr($type,0,$pos);
			$this->types[$type.'[]']='tns:'.$type.'Array';
			$this->processType($type);
			return $this->types[$type.'[]'];
		}
		else
		{	// process class / complex type
			$type=Yii::import($type,true);
			$class=new ReflectionClass($type);

			$comment=$class->getDocComment();
			$comment=strtr($comment,array("\r\n"=>"\n","\r"=>"\n")); // make line endings consistent: win -> unix, mac -> unix
			$comment=preg_replace('/^\s*\**(\s*?$|\s*)/m','',$comment);

			// extract soap indicator flag, if defined, e.g. @soap-indicator sequence
			// see http://www.w3schools.com/schema/schema_complex_indicators.asp
			if(preg_match('/^@soap-indicator\s+(\w+)\s*?(.*)$/im', $comment, $matches))
			{
				$indicator=$matches[1];
				$attributes=$this->getWsdlElementAttributes($matches[2]);
			}else{
				$indicator='all';
				$attributes=$this->getWsdlElementAttributes('');
			}

			$custom_wsdl=false;
			if(preg_match_all('/^@soap-wsdl\s+(\S.*)$/im',$comment,$matches)>0)
				$custom_wsdl=implode("\n", $matches[1]);

			$this->types[$type]=array(
				'indicator'=>$indicator,
				'nillable'=>$attributes['nillable'],
				'minOccurs'=>$attributes['minOccurs'],
				'maxOccurs'=>$attributes['maxOccurs'],
				'custom_wsdl'=>$custom_wsdl,
				'properties'=>array()
			);

			foreach($class->getProperties() as $property)
			{
				$comment=$property->getDocComment();
				if($property->isPublic() && strpos($comment,'@soap')!==false)
				{
					if(preg_match('/@var\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/mi',$comment,$matches))
					{
						$attributes=$this->getWsdlElementAttributes($matches[3]);

						if(preg_match('/{(.+)}/',$comment,$attr))
							$matches[3]=str_replace($attr[0],'',$matches[3]);

						// extract PHPDoc @example
						$example='';
						if(preg_match("/@example[:]?(.+)/mi",$comment,$match))
							$example=trim($match[1]);

						$this->types[$type]['properties'][$property->getName()]=array(
							$this->processType($matches[1]),
							trim($matches[3]),
							$attributes['nillable'],
							$attributes['minOccurs'],
							$attributes['maxOccurs'],
							$example
						); // name => type, doc, nillable, minOccurs, maxOccurs, example
					}
				}
			}
			return 'tns:'.$type;
		}
	}

	/**
	* Parse attributes nillable, minOccurs, maxOccurs
	* @param string $comment Extracted PHPDoc comment
	*/
	protected function getWsdlElementAttributes($comment) {
		$nillable=$minOccurs=$maxOccurs=null;
		if(preg_match('/{(.+)}/',$comment,$attr))
		{
			if(preg_match_all('/((\w+)\s*=\s*(\w+))/mi',$attr[1],$attr))
			{
				foreach($attr[2] as $id=>$prop)
				{
					$prop=strtolower($prop);
					$val=strtolower($attr[3][$id]);
					if($prop=='nillable'){
						if($val=='false' || $val=='true')
							$nillable=$val;
						else
							$nillable=$val ? 'true' : 'false';
					}elseif($prop=='minoccurs')
						$minOccurs=intval($val);
					elseif($prop=='maxoccurs')
						$maxOccurs=($val=='unbounded') ? 'unbounded' : intval($val);
				}
			}
		}
		return array(
			'nillable'=>$nillable,
			'minOccurs'=>$minOccurs,
			'maxOccurs'=>$maxOccurs
		);
	}

	/**
	* Import custom XML source node into WSDL document under specified target node
	* @param DOMDocument $dom XML WSDL document being generated
	* @param DOMElement $target XML node, to which will be appended $source node
	* @param DOMNode $source Source XML node to be imported
	*/
	protected function injectDom(DOMDocument $dom, DOMElement $target, DOMNode $source)
	{
		if ($source->nodeType!=XML_ELEMENT_NODE)
			return;

		$import=$dom->createElement($source->nodeName);

		foreach($source->attributes as $attr)
			$import->setAttribute($attr->name,$attr->value);

		foreach($source->childNodes as $child)
			$this->injectDom($dom,$import,$child);

		$target->appendChild($import);
	}

	/**
	 * @param string $serviceUrl Web service URL
	 * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
	 */
	protected function buildDOM($serviceUrl,$encoding)
	{
		$xml="<?xml version=\"1.0\" encoding=\"$encoding\"?>
<definitions name=\"{$this->serviceName}\" targetNamespace=\"{$this->namespace}\"
	 xmlns=\"http://schemas.xmlsoap.org/wsdl/\"
	 xmlns:tns=\"{$this->namespace}\"
	 xmlns:soap=\"http://schemas.xmlsoap.org/wsdl/soap/\"
	 xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
	 xmlns:wsdl=\"http://schemas.xmlsoap.org/wsdl/\"
	 xmlns:soap-enc=\"http://schemas.xmlsoap.org/soap/encoding/\"></definitions>";

		$dom=new DOMDocument();
		$dom->formatOutput=true;
		$dom->loadXml($xml);
		$this->addTypes($dom);

		$this->addMessages($dom);
		$this->addPortTypes($dom);
		$this->addBindings($dom);
		$this->addService($dom,$serviceUrl);

		return $dom;
	}

	/**
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	protected function addTypes($dom)
	{
		if($this->types===array())
			return;
		$types=$dom->createElement('wsdl:types');
		$schema=$dom->createElement('xsd:schema');
		$schema->setAttribute('targetNamespace',$this->namespace);
		foreach($this->types as $phpType=>$xmlType)
		{
			if(is_string($xmlType) && strrpos($xmlType,'Array')!==strlen($xmlType)-5)
				continue;  // simple type
			$complexType=$dom->createElement('xsd:complexType');
			if(is_string($xmlType))
			{
				if(($pos=strpos($xmlType,'tns:'))!==false)
					$complexType->setAttribute('name',substr($xmlType,4));
				else
					$complexType->setAttribute('name',$xmlType);
				$complexContent=$dom->createElement('xsd:complexContent');
				$restriction=$dom->createElement('xsd:restriction');
				$restriction->setAttribute('base','soap-enc:Array');
				$attribute=$dom->createElement('xsd:attribute');
				$attribute->setAttribute('ref','soap-enc:arrayType');
				$attribute->setAttribute('wsdl:arrayType',substr($xmlType,0,strlen($xmlType)-5).'[]');

				$arrayType=($dppos=strpos($xmlType,':')) !==false ? substr($xmlType,$dppos + 1) : $xmlType; // strip namespace, if any
				$arrayType=substr($arrayType,0,-5); // strip 'Array' from name
				$arrayType=(isset(self::$typeMap[$arrayType]) ? 'xsd:' : 'tns:') .$arrayType.'[]';
				$attribute->setAttribute('wsdl:arrayType',$arrayType);

				$restriction->appendChild($attribute);
				$complexContent->appendChild($restriction);
				$complexType->appendChild($complexContent);
			}
			elseif(is_array($xmlType))
			{
				$complexType->setAttribute('name',$phpType);
				if($xmlType['custom_wsdl']!==false)
				{
					$custom_dom=new DOMDocument();
					$custom_dom->loadXML('<root xmlns:xsd="http://www.w3.org/2001/XMLSchema">'.$xmlType['custom_wsdl'].'</root>');
					foreach($custom_dom->documentElement->childNodes as $el)
						$this->injectDom($dom,$complexType,$el);
				}else{
					$all=$dom->createElement('xsd:' . $xmlType['indicator']);

					if(!is_null($xmlType['minOccurs']))
						$all->setAttribute('minOccurs',$xmlType['minOccurs']);
					if(!is_null($xmlType['maxOccurs']))
						$all->setAttribute('maxOccurs',$xmlType['maxOccurs']);
					if(!is_null($xmlType['nillable']))
						$all->setAttribute('nillable',$xmlType['nillable']);

					foreach($xmlType['properties'] as $name=>$type)
					{
						$element=$dom->createElement('xsd:element');
						if(!is_null($type[3]))
							$element->setAttribute('minOccurs',$type[3]);
						if(!is_null($type[4]))
							$element->setAttribute('maxOccurs',$type[4]);
						if(!is_null($type[2]))
							$element->setAttribute('nillable',$type[2]);
						$element->setAttribute('name',$name);
						$element->setAttribute('type',$type[0]);
						$all->appendChild($element);
					}
					$complexType->appendChild($all);
				}
			}
			$schema->appendChild($complexType);
			$types->appendChild($schema);
		}
		$dom->documentElement->appendChild($types);
	}

	/**
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	protected function addMessages($dom)
	{
		foreach($this->messages as $name=>$message)
		{
			$element=$dom->createElement('wsdl:message');
			$element->setAttribute('name',$name);
			foreach($this->messages[$name] as $partName=>$part)
			{
				if(is_array($part))
				{
					$partElement=$dom->createElement('wsdl:part');
					$partElement->setAttribute('name',$partName);
					$partElement->setAttribute('type',$part[0]);
					$element->appendChild($partElement);
				}
			}
			$dom->documentElement->appendChild($element);
		}
	}

	/**
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	protected function addPortTypes($dom)
	{
		$portType=$dom->createElement('wsdl:portType');
		$portType->setAttribute('name',$this->serviceName.'PortType');
		$dom->documentElement->appendChild($portType);
		foreach($this->operations as $name=>$doc)
			$portType->appendChild($this->createPortElement($dom,$name,$doc));
	}

	/**
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 * @param string $name method name
	 * @param string $doc doc
	 */
	protected function createPortElement($dom,$name,$doc)
	{
		$operation=$dom->createElement('wsdl:operation');
		$operation->setAttribute('name',$name);

		$input=$dom->createElement('wsdl:input');
		$input->setAttribute('message', 'tns:'.$name.'Request');
		$output=$dom->createElement('wsdl:output');
		$output->setAttribute('message', 'tns:'.$name.'Response');

		$operation->appendChild($dom->createElement('wsdl:documentation',$doc));
		$operation->appendChild($input);
		$operation->appendChild($output);

		return $operation;
	}

	/**
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 */
	protected function addBindings($dom)
	{
		$binding=$dom->createElement('wsdl:binding');
		$binding->setAttribute('name',$this->serviceName.'Binding');
		$binding->setAttribute('type','tns:'.$this->serviceName.'PortType');

		$soapBinding=$dom->createElement('soap:binding');
		$soapBinding->setAttribute('style','rpc');
		$soapBinding->setAttribute('transport','http://schemas.xmlsoap.org/soap/http');
		$binding->appendChild($soapBinding);

		$dom->documentElement->appendChild($binding);

		foreach($this->operations as $name=>$doc)
			$binding->appendChild($this->createOperationElement($dom,$name));
	}

	/**
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 * @param string $name method name
	 */
	protected function createOperationElement($dom,$name)
	{
		$operation=$dom->createElement('wsdl:operation');
		$operation->setAttribute('name', $name);
		$soapOperation=$dom->createElement('soap:operation');
		$soapOperation->setAttribute('soapAction', $this->namespace.'#'.$name);
		$soapOperation->setAttribute('style','rpc');

		$input=$dom->createElement('wsdl:input');
		$output=$dom->createElement('wsdl:output');

		$soapBody=$dom->createElement('soap:body');
		$soapBody->setAttribute('use', 'encoded');
		$soapBody->setAttribute('namespace', $this->namespace);
		$soapBody->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
		$input->appendChild($soapBody);
		$output->appendChild(clone $soapBody);

		$operation->appendChild($soapOperation);
		$operation->appendChild($input);
		$operation->appendChild($output);

		return $operation;
	}

	/**
	 * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
	 * @param string $serviceUrl Web service URL
	 */
	protected function addService($dom,$serviceUrl)
	{
		$service=$dom->createElement('wsdl:service');
		$service->setAttribute('name', $this->serviceName.'Service');

		$port=$dom->createElement('wsdl:port');
		$port->setAttribute('name', $this->serviceName.'Port');
		$port->setAttribute('binding', 'tns:'.$this->serviceName.'Binding');

		$soapAddress=$dom->createElement('soap:address');
		$soapAddress->setAttribute('location',$serviceUrl);
		$port->appendChild($soapAddress);
		$service->appendChild($port);
		$dom->documentElement->appendChild($service);
	}

	/**
	* Generate human friendly HTML documentation for complex data types.
	* This method can be invoked either by inserting URL parameter "&makedoc" into URL link, e.g. "http://www.mydomain.com/soap/create?makedoc", or simply by calling from another script with argument $return=true.
	*
	* Each complex data type is described in a separate HTML table containing following columns:
	* <ul>
	* <li># - attribute ID</li>
	* <li>Attribute - attribute name, e.g. firstname</li>
	* <li>Type - attribute type, e.g. integer, date, tns:SoapPovCalculationResultArray</li>
	* <li>Nill - true|false - whether the attribute is nillable</li>
	* <li>Min - minimum number of occurrences</li>
	* <li>Max - maximum number of occurrences</li>
	* <li>Description - Detailed description of the attribute.</li>
	* <li>Example - Attribute example value if provided via PHPDoc property @example.</li>
	* <ul>
	*
	* @param bool $return If true, generated HTML output will be returned rather than directly sent to output buffer
	*/
	public function buildHtmlDocs($return=false)
	{
		$html='<html><head>';
		$html.='<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$html.='<style type="text/css">
table{border-collapse: collapse;background-color: #DDDDDD;}
tr{background-color: #FFFFFF;}
th{background-color: #EEEEEE;}
th, td{font-size: 12px;font-family: courier;padding: 3px;}
</style>';
		$html.='</head><body>';
		$html.='<h2>WSDL documentation for service '.$this->serviceName.'</h2>';
		$html.='<p>Generated on '.date('d.m.Y H:i:s').'</p>';
		$html.='<table border="0" cellspacing="1" cellpadding="1">';
		$html.='<tr><td>';

		if(!empty($this->types))
		{
			foreach($this->types as $object=>$options){
				if(!is_array($options) || empty($options) || !is_array($options['properties']) || empty($options['properties'])){
					continue;
				}
				$params=$options['properties'];
				$html.="\n\n<h3>Object: {$object}</h3>";
				$html.='<table border="1" cellspacing="1" cellpadding="1">';
				$html.='<tr><th>#</th><th>Attribute</th><th>Type</th><th>Nill</th><th>Min</th><th>Max</th><th>Description</th><th>Example</th></tr>';
				$c=0;
				foreach($params as $param=>$prop){
					++$c;
					$html.="\n<tr>"
								."\n\t<td>{$c}</td>"
								."\n\t<td>{$param}</td>"
								."\n\t<td>".(str_replace('xsd:','',$prop[0]))."</td>"
								."\n\t<td>".$prop[2]."</td>"
								."\n\t<td>".($prop[3]==null ? '&nbsp;' : $prop[3])."</td>"
								."\n\t<td>".($prop[4]==null ? '&nbsp;' : $prop[4])."</td>"
								."\n\t<td>{$prop[1]}</td>"
								."\n\t<td>".(trim($prop[5])=='' ? '&nbsp;' : $prop[5])."</td>"
							."\n</tr>";
				}
				$html.="\n</table><br/>";
			}
		}
		else
			$html.='No complex data type found!';

		$html.='</td></tr></table></body></html>';

		if($return)
			return $html;

		echo $html;
		Yii::app()->end(); // end the app to avoid conflict with text/xml header
	}
}
