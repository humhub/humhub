<?php
/**
 * CWebServiceAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebServiceAction implements an action that provides Web services.
 *
 * CWebServiceAction serves for two purposes. On the one hand, it displays
 * the WSDL content specifying the Web service APIs. On the other hand, it
 * invokes the requested Web service API. A GET parameter named <code>ws</code>
 * is used to differentiate these two aspects: the existence of the GET parameter
 * indicates performing the latter action.
 *
 * By default, CWebServiceAction will use the current controller as
 * the Web service provider. See {@link CWsdlGenerator} on how to declare
 * methods that can be remotely invoked.
 *
 * Note, PHP SOAP extension is required for this action.
 *
 * @property CWebService $service The Web service instance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.services
 * @since 1.0
 */
class CWebServiceAction extends CAction
{
	/**
	 * @var mixed the Web service provider object or class name.
	 * If specified as a class name, it can be a path alias.
	 * Defaults to null, meaning the current controller is used as the service provider.
	 * If the provider implements the interface {@link IWebServiceProvider},
	 * it will be able to intercept the remote method invocation and perform
	 * additional tasks (e.g. authentication, logging).
	 */
	public $provider;
	/**
	 * @var string the URL for the Web service. Defaults to null, meaning
	 * the URL for this action is used to provide Web services.
	 * In this case, a GET parameter named {@link serviceVar} will be used to
	 * deteremine whether the current request is for WSDL or Web service.
	 */
	public $serviceUrl;
	/**
	 * @var string the URL for WSDL. Defaults to null, meaning
	 * the URL for this action is used to serve WSDL document.
	 */
	public $wsdlUrl;
	/**
	 * @var string the name of the GET parameter that differentiates a WSDL request
	 * from a Web service request. If this GET parameter exists, the request is considered
	 * as a Web service request; otherwise, it is a WSDL request.  Defaults to 'ws'.
	 */
	public $serviceVar='ws';
	/**
	 * @var array a list of PHP classes that are declared as complex types in WSDL.
	 * This should be an array with WSDL types as keys and names of PHP classes as values.
	 * A PHP class can also be specified as a path alias.
	 * @see http://www.php.net/manual/en/soapclient.soapclient.php
	 */
	public $classMap;
	/**
	 * @var array the initial property values for the {@link CWebService} object.
	 * The array keys are property names of {@link CWebService} and the array values
	 * are the corresponding property initial values.
	 */
	public $serviceOptions=array();

	private $_service;


	/**
	 * Runs the action.
	 * If the GET parameter {@link serviceVar} exists, the action handle the remote method invocation.
	 * If not, the action will serve WSDL content;
	 */
	public function run()
	{
		$hostInfo=Yii::app()->getRequest()->getHostInfo();
		$controller=$this->getController();
		if(($serviceUrl=$this->serviceUrl)===null)
			$serviceUrl=$hostInfo.$controller->createUrl($this->getId(),array($this->serviceVar=>1));
		if(($wsdlUrl=$this->wsdlUrl)===null)
			$wsdlUrl=$hostInfo.$controller->createUrl($this->getId());
		if(($provider=$this->provider)===null)
			$provider=$controller;

		$this->_service=$this->createWebService($provider,$wsdlUrl,$serviceUrl);

		if(is_array($this->classMap))
			$this->_service->classMap=$this->classMap;

		foreach($this->serviceOptions as $name=>$value)
			$this->_service->$name=$value;

		if(isset($_GET[$this->serviceVar]))
			$this->_service->run();
		else
			$this->_service->renderWsdl();

		Yii::app()->end();
	}

	/**
	 * Returns the Web service instance currently being used.
	 * @return CWebService the Web service instance
	 */
	public function getService()
	{
		return $this->_service;
	}

	/**
	 * Creates a {@link CWebService} instance.
	 * You may override this method to customize the created instance.
	 * @param mixed $provider the web service provider class name or object
	 * @param string $wsdlUrl the URL for WSDL.
	 * @param string $serviceUrl the URL for the Web service.
	 * @return CWebService the Web service instance
	 */
	protected function createWebService($provider,$wsdlUrl,$serviceUrl)
	{
		return new CWebService($provider,$wsdlUrl,$serviceUrl);
	}
}