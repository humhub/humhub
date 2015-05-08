# CHANGELOG

## 2.2.5 (2013-10-31)

- [4604: Zend\Json\Server\Server::addFunction instantiates new class even an object was given as callable](https://github.com/zendframework/zf2/issues/4604)
- [4874: Skip AnnotationScanner if class name information can't be found.](https://github.com/zendframework/zf2/pull/4874)
- [4918: &#91;suggest&#92; Ignore methods without parameters from aware interfaces](https://github.com/zendframework/zf2/pull/4918)
- [5013: ZF2-2454 HTTP 308 Resume Incomplete missing in Zend\Http\Response](https://github.com/zendframework/zf2/pull/5013)
- [5031: Fix input annotation handler in Zend/Form/Annotation/ElementAnnotationsListener](https://github.com/zendframework/zf2/pull/5031)
- [5035: updated Zend&#95;Validate&#95;Hostname translation message IDs and translations](https://github.com/zendframework/zf2/pull/5035)
- [5037: Slovenian translations updated](https://github.com/zendframework/zf2/pull/5037)
- [5040: Correct namespace name DockBlock to DocBlock](https://github.com/zendframework/zf2/pull/5040)
- [5044: Reflection ThrowsTag to handle types correctly](https://github.com/zendframework/zf2/pull/5044)
- [5050: #4996 broke File filters management](https://github.com/zendframework/zf2/pull/5050)
- [5053: add test case for Zend\Validator\IsInstanceOf to pass Traversable to constructor](https://github.com/zendframework/zf2/pull/5053)
- [5054: is bin/pluginmap&#95;generator.php broken ?](https://github.com/zendframework/zf2/pull/5054)
- [5065: &#91;Zend\Http\Client&#92; dupplicate header keys in prepareHeaders](https://github.com/zendframework/zf2/pull/5065)
- [5066: &#95;&#95;invoke parameter should be null by default](https://github.com/zendframework/zf2/pull/5066)
- [5068: using injected response object](https://github.com/zendframework/zf2/pull/5068)
- [5071: Increase readability, fix indentation](https://github.com/zendframework/zf2/pull/5071)
- [5078: hotfix/4508 and make Zend\Http\Header\SetCookie RFC conform](https://github.com/zendframework/zf2/pull/5078)
- [5083: &#91;Barcode&#92; removed some unused variables](https://github.com/zendframework/zf2/pull/5083)
- [5093: Extract and populate values for nested fieldsets in Collection elements](https://github.com/zendframework/zf2/pull/5093)
- [5100: &#91;ServiceManager&#92; Implemented circular alias reference detection](https://github.com/zendframework/zf2/pull/5100)
- [5111: Fix test suite when ext/intl isn't available](https://github.com/zendframework/zf2/pull/5111)
- [5121: Add inline comments](https://github.com/zendframework/zf2/pull/5121)
- [5140: Fix not allowed encoding of content-transfer-encoding and content-type headers in single part encoded mails](https://github.com/zendframework/zf2/pull/5140)
- [5146: Adds an alias for ModuleManager and removes the duplicate service defini...](https://github.com/zendframework/zf2/pull/5146)
- [5150: Fix Validator\PhoneNumber with E.123/E.164 international numbers.](https://github.com/zendframework/zf2/pull/5150)
- [5152: Issue #4669 - Class generator should return uses from file generator](https://github.com/zendframework/zf2/pull/5152)
- [5161: Fix calling View\Helper\BasePath from CLI results in fatal error.](https://github.com/zendframework/zf2/pull/5161)
- [5175: fix delegators to allow usage in plugin managers](https://github.com/zendframework/zf2/pull/5175)
- [5180: Ensure DiAbstractServiceFactory takes lowest possible priority](https://github.com/zendframework/zf2/pull/5180)
- [5183: Fix for CamelCase filter when string contains multiple uppercase letters and Unicode is off](https://github.com/zendframework/zf2/pull/5183)
- [5193: Fix returned NamespaceType for Parameters from Reflection](https://github.com/zendframework/zf2/pull/5193)
- [5196: Fix JsonRpc service name](https://github.com/zendframework/zf2/pull/5196)
- [5212: assertQueryContentContains searching through all nodes found](https://github.com/zendframework/zf2/pull/5212)
- [5216: added missing I18n\Validator\DateTime translations](https://github.com/zendframework/zf2/pull/5216)
- [5220: Bug fix for Zend\Form\Element\Collection::extract()](https://github.com/zendframework/zf2/pull/5220)
- [5223: Cannot use Zend\Stdlib\ResponseInterface as Response because the name is already in use in Zend\Stdlib\DispatchableInterface](https://github.com/zendframework/zf2/issues/5223)
- [5234: added zendframework/zend-session as suggest dependency at Zend\ProgressBar](https://github.com/zendframework/zf2/pull/5234)
- [5239: added zendframework/zend-cache as suggest dependency at Zend\Paginator](https://github.com/zendframework/zf2/pull/5239)
- [5240: fix Debug::getEscaper() never called at Debug::dump() when xdebug is loaded](https://github.com/zendframework/zf2/pull/5240)
- [5246: move zendframework/zend-escaper from require to suggest dependency at Zend\Debug](https://github.com/zendframework/zf2/pull/5246)
- [5250: explode should be made only by colon (:) and not colon+space (: )](https://github.com/zendframework/zf2/pull/5250)
- [5252: Improvements Zend\Form\View\Helper\FormElement](https://github.com/zendframework/zf2/pull/5252)
- [5254: Zend\Log\Writer\Db via config throws exception](https://github.com/zendframework/zf2/pull/5254)
- [5259: Modified PhpArray config writer to generate better readable array format.](https://github.com/zendframework/zf2/pull/5259)
- [5271: fixes #5270](https://github.com/zendframework/zf2/pull/5271)
- [5274: add regression testing for fieldset input filter](https://github.com/zendframework/zf2/pull/5274)
- [5279: Polish translation for Zend\Captcha](https://github.com/zendframework/zf2/pull/5279)
- [5280: Polish translation and fixes in Zend\Validate](https://github.com/zendframework/zf2/pull/5280)
- [5286: Hotfix/5118](https://github.com/zendframework/zf2/pull/5286)
- [5287: Add Not Like Predicate](https://github.com/zendframework/zf2/pull/5287)
- [5291: &#91;mail&#92; Fixes, criteria unification and optimization.](https://github.com/zendframework/zf2/pull/5291)
- [5293: Fix #5289 (abstract factories return type)](https://github.com/zendframework/zf2/pull/5293)
- [5295: Update DateFormat.php to fix deprecated method call: PHP &gt;= 5.5.0.](https://github.com/zendframework/zf2/pull/5295)
- [5301: &#91;http&#92; Adapt header field name validation to RFC definition](https://github.com/zendframework/zf2/pull/5301)
- [5302: &#91;http&#92; Parse headerline](https://github.com/zendframework/zf2/pull/5302)
- [5311: &#91;http&#92; Unify criteria for split name](https://github.com/zendframework/zf2/pull/5311)
- [5317: IbmDb2 Commitment Control](https://github.com/zendframework/zf2/pull/5317)
- [5318: &#91;#5013&#92; Remove custom code response tests](https://github.com/zendframework/zf2/pull/5318)
- [5319: Class not found instead of exception in RedisOptions](https://github.com/zendframework/zf2/pull/5319)
- [5325: fixed typo](https://github.com/zendframework/zf2/pull/5325)
- [5333: Zend\ServiceManager - CS fixes for master](https://github.com/zendframework/zf2/pull/5333)
- [5336: fix typo](https://github.com/zendframework/zf2/pull/5336)
- [5343: Remove date filtering on date elements](https://github.com/zendframework/zf2/pull/5343)
- [5350: fixed typos](https://github.com/zendframework/zf2/pull/5350)
- [5351: fixes #5310](https://github.com/zendframework/zf2/pull/5351)
- [5360: fixed typo](https://github.com/zendframework/zf2/pull/5360)
- [5368: Avoid SOAP constant error in PHPUnit](https://github.com/zendframework/zf2/pull/5368)
- [5369: Php unit windows](https://github.com/zendframework/zf2/pull/5369)
- [5370: fixed typos](https://github.com/zendframework/zf2/pull/5370)
- [5374: Potential security vulnerability ](https://github.com/zendframework/zf2/issues/5374)
- [5378: Exception as one of the possible exception for Soap\Server::registerFaultException](https://github.com/zendframework/zf2/pull/5378)
- [5379: fixes #4604](https://github.com/zendframework/zf2/pull/5379)
- [5382: #4954 Mongodb small changes](https://github.com/zendframework/zf2/pull/5382)

### SECURITY UPDATES

An issue with `Zend\Http\PhpEnvironment\RemoteAddress` was reported in
[#5374](https://github.com/zendframework/zf2/pull/5374). Essentially, the class
was not checking if `$_SERVER['REMOTE_ADDR']` was one of the trusted proxies
configured, and as a result, `getIpAddressFromProxy()` could return an untrusted
IP address. 

The class was updated to check if `$_SERVER['REMOTE_ADDR']` is in the list of
trusted proxies, and, if so, will return that value immediately before
consulting the values in the `X-Forwarded-For` header.

If you use the `RemoteAddr` `Zend\Session` validator, and are configuring
trusted proxies, we recommend updating to 2.2.5 or later immediately.

### Potential Breakage

- [#5343](https://github.com/zendframework/zf2/pull/5343) removed the
  DateTimeFormatter filter from DateTime form elements. This was done
  due to the fact that it led to unexpected behavior when non-date inputs were
  provided. However, since the DateTime element already incorporates a
  DateValidator that accepts a date format, validation can still work as
  expected.

## 2.2.4 (2013-08-26)

- [5008: deprecated feature in classmap generator](https://github.com/zendframework/zf2/issues/5008)
- [5015: Allow set Form::setPreferFormInputFilter via options](https://github.com/zendframework/zf2/issues/5015)
- [5028: Fix forms regression introduced in 2.2.3](https://github.com/zendframework/zf2/issues/5028)

## 2.2.3 (2013-08-21):

- [4851: allow usage of validator and filter plugin managers in input filter factory if form manager injected](https://github.com/zendframework/zf2/issues/4851)
- [4868: Tests for issue with unexpected injection.](https://github.com/zendframework/zf2/issues/4868)
- [4877: Validator\File tests throwing errors in custom PHP 5.3.10 distributions](https://github.com/zendframework/zf2/issues/4877)
- [4878: Form element title attribute test](https://github.com/zendframework/zf2/issues/4878)
- [4881: Update Validator translations](https://github.com/zendframework/zf2/issues/4881)
- [4883: Update Zend&#95;Validate.php](https://github.com/zendframework/zf2/issues/4883)
- [4893: Resolves warning raised when version is not matched.](https://github.com/zendframework/zf2/issues/4893)
- [4895: Small fix for ZendTest\Form\FormTest method name](https://github.com/zendframework/zf2/issues/4895)
- [4897: Support file stream](https://github.com/zendframework/zf2/issues/4897)
- [4905: Update Statement.php](https://github.com/zendframework/zf2/issues/4905)
- [4909: renamed test class according to psr-0](https://github.com/zendframework/zf2/issues/4909)
- [4915: Dependency suggest for MVC plugins](https://github.com/zendframework/zf2/issues/4915)
- [4919: Notices being triggered when hydrating classes with no properties with the reflection hydrator](https://github.com/zendframework/zf2/issues/4919)
- [4920: Redundant conditional](https://github.com/zendframework/zf2/issues/4920)
- [4922: remove unused $typeFormats property at Zend/Code/Generator/DocBlock/Tag.php](https://github.com/zendframework/zf2/issues/4922)
- [4925: HttpClient: adapter always reachable through getter if specified on contructor](https://github.com/zendframework/zf2/issues/4925)
- [4929: Add Zend\Uri as a suggest because it is required by the Uri &amp; Sitemap\Loc validator](https://github.com/zendframework/zf2/issues/4929)
- [4934: Mime\Message: createFromString: decode transfer encoding](https://github.com/zendframework/zf2/issues/4934)
- [4957: Undefined variable: class in Zend/ModuleManager/Listener/ServiceListener.php](https://github.com/zendframework/zf2/issues/4957)
- [4966: Fix issue #4952](https://github.com/zendframework/zf2/issues/4966)
- [4976: Applied trim and strtolower to Gravatar email per Gravatar docs: https://en.gravatar.com/site/implement/hash/](https://github.com/zendframework/zf2/issues/4976)
- [4978: added missing docblock for &quot;@link&quot;, &quot;@copyright&quot;, and &quot;@license&quot; and fix wrong namespace according PSR-0](https://github.com/zendframework/zf2/issues/4978)
- [4981: Revise docblocks in Zend\Session\ContainerAbstractServiceFactory](https://github.com/zendframework/zf2/issues/4981)
- [4988: &#91;Zend-Code&#92; Fix Code Generation for non namespace classes](https://github.com/zendframework/zf2/issues/4988)
- [4990: &#91;Zend-Code&#92; Make sure that a use is only added once in ClassGenerator](https://github.com/zendframework/zf2/issues/4990)
- [4996: BaseInputFilter-&gt;add deasn't work (Form Validation breaks since 2.2)](https://github.com/zendframework/zf2/issues/4996)

## 2.2.2 (2013-07-24):

- [4105: Method &quot;headLink&quot; does not exist](https://github.com/zendframework/zf2/issues/4105)
- [4555: Zend\Http\Response::getBody() tries to decode gzip that has already been decoded by cURL](https://github.com/zendframework/zf2/issues/4555)
- [4564: &#91;Navigation&#92; Allow non-string permissions](https://github.com/zendframework/zf2/issues/4564)
- [4567: &#91;InputFilter&#92;&#91;Hotfix&#92; Missing check for allowEmpty()](https://github.com/zendframework/zf2/issues/4567)
- [4612: Templatemap generator: keys of templatemap not correct?](https://github.com/zendframework/zf2/issues/4612)
- [4631: remove redundance @copyright and @license docblock  because of already written](https://github.com/zendframework/zf2/issues/4631)
- [4640: Split multiple implements into multiple lines](https://github.com/zendframework/zf2/issues/4640)
- [4643: Add use statements](https://github.com/zendframework/zf2/issues/4643)
- [4644: Make ValidatorPluginManager aware of PhoneNumber validator](https://github.com/zendframework/zf2/issues/4644)
- [4646: Docblock subject misspelling](https://github.com/zendframework/zf2/issues/4646)
- [4649: &#91;code&#92; Implement logic for include a file in FileReflection if this exists and is not already included](https://github.com/zendframework/zf2/issues/4649)
- [4650: Some doc block fixes](https://github.com/zendframework/zf2/issues/4650)
- [4652: router defaults not being set properly in console](https://github.com/zendframework/zf2/issues/4652)
- [4654: Make AbstractRestController rest methods non-abstract #4209](https://github.com/zendframework/zf2/issues/4654)
- [4665: Make ValidatorPluginManager aware of DateTime validator](https://github.com/zendframework/zf2/issues/4665)
- [4676: Fix file post redirect get redirection with ModuleRouteListener](https://github.com/zendframework/zf2/issues/4676)
- [4688: Add @todo docblock](https://github.com/zendframework/zf2/issues/4688)
- [4690: Zend\Mail\Protocol\Smtp does not reset protected $auth after disconnect](https://github.com/zendframework/zf2/issues/4690)
- [4692: added zendframework/zend-resources to the global composer.json](https://github.com/zendframework/zf2/issues/4692)
- [4696: &#91;WIP&#92; Enforcing composer version in travis builds](https://github.com/zendframework/zf2/issues/4696)
- [4699: Add use statements](https://github.com/zendframework/zf2/issues/4699)
- [4700: PHP 5.5 can't fail anymore](https://github.com/zendframework/zf2/issues/4700)
- [4702: DocBlock and CS fixes](https://github.com/zendframework/zf2/issues/4702)
- [4705: add zendframework/zend-json to Zend\ProgressBar\composer.json as suggest](https://github.com/zendframework/zf2/issues/4705)
- [4722: remove bloated LICENSE description at header for consistency ](https://github.com/zendframework/zf2/issues/4722)
- [4725: Add sorting to classmap generator](https://github.com/zendframework/zf2/issues/4725)
- [4729: Provide ability to configure ReCaptcha Service public and private keys via options](https://github.com/zendframework/zf2/issues/4729)
- [4734: Fix for #4727](https://github.com/zendframework/zf2/issues/4734)
- [4738: remove unnecessary space after function name](https://github.com/zendframework/zf2/issues/4738)
- [4741: Hotfix/4740](https://github.com/zendframework/zf2/issues/4741)
- [4743: Update PluginManager.php](https://github.com/zendframework/zf2/issues/4743)
- [4744: Remove ZendTest from Composer](https://github.com/zendframework/zf2/issues/4744)
- [4746: Bumping supported ProxyManager version](https://github.com/zendframework/zf2/issues/4746)
- [4754: Update SimpleStreamResponseSenderTest.php](https://github.com/zendframework/zf2/issues/4754)
- [4759: Added pluginmap&#95;generator + templatemap&#95;generator to BIN directory](https://github.com/zendframework/zf2/issues/4759)
- [4761: Remove exceptions from #4734](https://github.com/zendframework/zf2/issues/4761)
- [4762: &#91;Hotfix&#92; Fix conflicting use statement](https://github.com/zendframework/zf2/issues/4762)
- [4771: Form\View\Helper\FormRow label position gets overwritten by &#95;&#95;invoke()](https://github.com/zendframework/zf2/issues/4771)
- [4776: Zend\Http\Header\SetCookie Allow unsetting cookie attibutes by resetting to null](https://github.com/zendframework/zf2/issues/4776)
- [4777: Change file mode from 644 to 755 templatemap&#95;generator.php](https://github.com/zendframework/zf2/issues/4777)
- [4778: Zend\Validator depends on Zend\Filter](https://github.com/zendframework/zf2/issues/4778)
- [4783: Make methods setUp and tearDown protected](https://github.com/zendframework/zf2/issues/4783)
- [4787: Update Zend&#95;Validate.php](https://github.com/zendframework/zf2/issues/4787)
- [4788: set factory in CollectionInputFilter](https://github.com/zendframework/zf2/issues/4788)
- [4790: Add check to DI to see if we have a class to instantiate](https://github.com/zendframework/zf2/issues/4790)
- [4793: &#91;validator&#92; Validate quoted local part of email addresses](https://github.com/zendframework/zf2/issues/4793)
- [4798: Default mode variables HeadScript and InlineScript](https://github.com/zendframework/zf2/issues/4798)
- [4804: Possible Typo in  Zend / Cache / Storage / Adapter / RedisResourceManager](https://github.com/zendframework/zf2/issues/4804)
- [4805: Zend\I18n\View\Helper\CurrencyFormat | showDecimals parameter overrides the default value](https://github.com/zendframework/zf2/issues/4805)
- [4808: Unimplemented REST methods should set a 405 status](https://github.com/zendframework/zf2/issues/4808)
- [4818: Issue4817](https://github.com/zendframework/zf2/issues/4818)
- [4830: Correct spelling of function getMajorVersion](https://github.com/zendframework/zf2/issues/4830)
- [4835: Update templatemap&#95;generator.php](https://github.com/zendframework/zf2/issues/4835)
- [4838: Little fix in InputFilter/Factory](https://github.com/zendframework/zf2/issues/4838)
- [4847: Fix Version::getLatest docblock](https://github.com/zendframework/zf2/issues/4847)
- [4850: Allow form elements created via Annotations to have same default InputFilter as created via array specification](https://github.com/zendframework/zf2/issues/4850)
- [4854: Allow FormElementErrors view helper to translate messages](https://github.com/zendframework/zf2/issues/4854)
- [4856: Zend\Validator\File\MimeType warning with no params](https://github.com/zendframework/zf2/issues/4856)
- [4857: `fault` property must be an instance of \Zend\XmlRpc\Fault](https://github.com/zendframework/zf2/issues/4857)
- [4858: Removed @category, @package and @subpackage docblock tags in ZendTest\Config](https://github.com/zendframework/zf2/issues/4858)
- [4859: doc block changes in head view helpers](https://github.com/zendframework/zf2/issues/4859)
- [4866: update tests/ZendTest/Mvc/ApplicationTest.php](https://github.com/zendframework/zf2/issues/4866)
- [4870: Use MvcTranslator to inject view helpers](https://github.com/zendframework/zf2/issues/4870)

## 2.2.1 (2013-06-12):

- [3647: Problems in the way Zend\Paginator\Adapter\DbSelect count()s](https://github.com/zendframework/zf2/issues/3647)
- [3853: Log formatters shouldn't override referenced values](https://github.com/zendframework/zf2/issues/3853)
- [4421: fix docblocks : `Zend_` should be `Zend\\` and some typos](https://github.com/zendframework/zf2/issues/4421)
- [4452: Zend\Authentication\Result custom result codes not possible](https://github.com/zendframework/zf2/issues/4452)
- [4456: can't override Zend\Log\Logger::registerExceptionHandler](https://github.com/zendframework/zf2/issues/4456)
- [4457: Zend\Code\Scanner\ClassScanner don't parse constants with docblock](https://github.com/zendframework/zf2/issues/4457)
- [4458: Fix for PHP 5.5 unit tests (and XDebug &gt;= 2.2.0)](https://github.com/zendframework/zf2/issues/4458)
- [4465: Add ConstantScanner to Zend\Code\Scanner](https://github.com/zendframework/zf2/issues/4465)
- [4470: sync ZF1 svn r24807 - ZF-12128: File Upload validator should display file na...](https://github.com/zendframework/zf2/issues/4470)
- [4474: Suggest some dependencies in Zend\Mvc](https://github.com/zendframework/zf2/issues/4474)
- [4480: fixed Cache\StorageFactory::factory()](https://github.com/zendframework/zf2/issues/4480)
- [4494: Add build.xml to .gitattributes/export-ignore](https://github.com/zendframework/zf2/issues/4494)
- [4496: Class methods hydrator skips getters with optional parameters](https://github.com/zendframework/zf2/issues/4496)
- [4497: Fix name of LoggerAbstractServiceFactory test](https://github.com/zendframework/zf2/issues/4497)
- [4498: Update the method level comment to reflect change in signature](https://github.com/zendframework/zf2/issues/4498)
- [4499: Add service definition for DateTimeFormatter (related to #3632)](https://github.com/zendframework/zf2/issues/4499)
- [4503: Zend\Session\Storage\AbstractSessionArrayStorage::fromArray() can receive a string causing a fatal error on shutdown](https://github.com/zendframework/zf2/issues/4503)
- [4509: `DateTimeFormatter` Format DateTime values correctly](https://github.com/zendframework/zf2/issues/4509)
- [4516: CollectionInputFilter should respect the keys of collectionData](https://github.com/zendframework/zf2/issues/4516)
- [4518: Update PhpDoc comment](https://github.com/zendframework/zf2/issues/4518)
- [4522: Remove unknown invokables from FilterPluginManager](https://github.com/zendframework/zf2/issues/4522)
- [4524: Add zend-json as a required dependency](https://github.com/zendframework/zf2/issues/4524)
- [4526: Fill SharedEventManager events with identifiers](https://github.com/zendframework/zf2/issues/4526)
- [4528: Fix priority not handled in AggregateHydrator](https://github.com/zendframework/zf2/issues/4528)
- [4529: Allow Zend\Form\Element\Checkbox to return real value instead of always a boolean](https://github.com/zendframework/zf2/issues/4529)
- [4530: Fix for unmatched routes in navigation](https://github.com/zendframework/zf2/issues/4530)
- [4535: Update RoleInterface.php](https://github.com/zendframework/zf2/issues/4535)
- [4538: Zend\Crypt\Password\Bcrypt does not report inability to generate hash](https://github.com/zendframework/zf2/issues/4538)
- [4539: Update StrategyInterface.php](https://github.com/zendframework/zf2/issues/4539)
- [4542: Adds ability to specify a template for exceptions retrieved from Exception::getPrevious](https://github.com/zendframework/zf2/issues/4542)
- [4543: soapVersion key is not reachable](https://github.com/zendframework/zf2/issues/4543)
- [4546: View: correctly validate input in PartialLoop](https://github.com/zendframework/zf2/issues/4546)
- [4552: Wincache unexpected return value on internalGetItem](https://github.com/zendframework/zf2/issues/4552)
- [4553: Remove private variables from AbstractControllerTestCase.](https://github.com/zendframework/zf2/issues/4553)
- [4561: Fix the controller plugin PostRedirectGet wrong redirection (in MVC)](https://github.com/zendframework/zf2/issues/4561)
- [4562: Validator Messages Tests](https://github.com/zendframework/zf2/issues/4562)
- [4566: Fix generating array with unsorted keys](https://github.com/zendframework/zf2/issues/4566)
- [4568: Cast Parameters](https://github.com/zendframework/zf2/issues/4568)
- [4571: INI reader breaks when mbstring function overloading is in place](https://github.com/zendframework/zf2/issues/4571)
- [4572: Zend\Form Should throw exception if try to get() an element that does not exist](https://github.com/zendframework/zf2/issues/4572)
- [4576: Redis Cache Adapter Config - setLibOptions is broken](https://github.com/zendframework/zf2/issues/4576)
- [4577: Fix issue with Redis Cache adapter whereby setOption was being called before connecting to Redis server](https://github.com/zendframework/zf2/issues/4577)
- [4581: Hostname route ignore `HTTP_HOST` and give `SERVER_NAME` precedence](https://github.com/zendframework/zf2/issues/4581)
- [4582: Fix Nested form element wrapping (relative: #4383)](https://github.com/zendframework/zf2/issues/4582)
- [4588: set 0 as header value (issue #4583)](https://github.com/zendframework/zf2/issues/4588)
- [4590: Zend paginator dbselect count](https://github.com/zendframework/zf2/issues/4590)
- [4595: Missing invokable fo Redis Cache Storage, problem with setting password](https://github.com/zendframework/zf2/issues/4595)
- [4596: Missing french translations, and wrong class name](https://github.com/zendframework/zf2/issues/4596)
- [4597: Zend\Validate\Hostname doesn't handle IDN for .UA](https://github.com/zendframework/zf2/issues/4597)
- [4599: `InputFilter` Input merge should copy over the `continue_if_empty` flag](https://github.com/zendframework/zf2/issues/4599)
- [4602: Remove needless check](https://github.com/zendframework/zf2/issues/4602)
- [4603: Redis Storage won't behave correctly after libOptions were set](https://github.com/zendframework/zf2/issues/4603)
- [4605: Possibility to use camelCase for all soap client options](https://github.com/zendframework/zf2/issues/4605)
- [4608: Allow the `gc_probability` option to be set to zero.](https://github.com/zendframework/zf2/issues/4608)
- [4609: Logger: Error/Exception Handler: fixed 3853 &amp; 4456](https://github.com/zendframework/zf2/issues/4609)
- [4615: Fix #4579 `day_attributes` could not be passed in construct](https://github.com/zendframework/zf2/issues/4615)
- [4616: fixed 4614: infinite loop in Zend\Log\Formatter::normalize](https://github.com/zendframework/zf2/issues/4616)
- [4617: Zend\Code: Docblock generates empty line under @tags if docblock was read from existing code](https://github.com/zendframework/zf2/issues/4617)
- [4618: Missed method findRealpathInIncludePath() in Zend\Code\Reflection\FileReflection](https://github.com/zendframework/zf2/issues/4618)
- [4621: Update 'Missing captcha fields' translation](https://github.com/zendframework/zf2/issues/4621)
- [4622: Ensure router factory is used by SM factory](https://github.com/zendframework/zf2/issues/4622)
- [4624: Notification thrown in Zend\Mvc\Service\ViewHelperManagerFactory](https://github.com/zendframework/zf2/issues/4624)
- [4628: Fix misstake detect is active Page\Mvc in IndexController](https://github.com/zendframework/zf2/issues/4628)
- [4629: Zend\Cache\Pattern\CallbackCache doesn't work with NULL](https://github.com/zendframework/zf2/issues/4629)
- [4630: Allow selecting the TranslatorAwareTreeRouteStack via configuration](https://github.com/zendframework/zf2/issues/4630)
- [4632: fixed #4552: Wincache::getItem() have to return NULL in cases of missing items](https://github.com/zendframework/zf2/issues/4632)
- [4633: removed checks of not existing class Zend\Math\BigInteger](https://github.com/zendframework/zf2/issues/4633)
- [4634: Navigation\Page\Mvc Can't return false whithout call parent::isActive](https://github.com/zendframework/zf2/issues/4634)
- [4636: Punycode decoding fails if encoded string has not hyphen](https://github.com/zendframework/zf2/issues/4636)
- [4641: Zend\Paginator\Adapter\DbSelect alternative solution to count, with subselect](https://github.com/zendframework/zf2/issues/4641)

## 2.2.0 (2013-05-15):

- [2865: (Enhancement) Add an easier way to use i18n view helpers.](https://github.com/zendframework/zf2/issues/2865)
- [2903: add AdapterManager in to Zend\Db\Adapter namespace](https://github.com/zendframework/zf2/issues/2903)
- [2984: Add full stop at end of validator messages (fixes #2966)](https://github.com/zendframework/zf2/issues/2984)
- [3490: Added support for callable credential validator](https://github.com/zendframework/zf2/issues/3490)
- [3580: Feature/context aware hydrator strategies](https://github.com/zendframework/zf2/issues/3580)
- [3632: New DateTimeFormatter Filter (#3617)](https://github.com/zendframework/zf2/issues/3632)
- [3646: Zend\I18n\View\Helper\NumberFormat param to set the number of decimals](https://github.com/zendframework/zf2/issues/3646)
- [3693: Add RBAC support for navigation helper.](https://github.com/zendframework/zf2/issues/3693)
- [3709: Redis cache storage](https://github.com/zendframework/zf2/issues/3709)
- [3710: Allow to remove delimiters for DateSelect and fix bugs with some locales](https://github.com/zendframework/zf2/issues/3710)
- [3747: Add getFilename() to Zend\Cache\Pattern\CaptureCache](https://github.com/zendframework/zf2/issues/3747)
- [3754: Update library/Zend/Stdlib/Hydrator/ClassMethods.php](https://github.com/zendframework/zf2/issues/3754)
- [3792: Sets specific attributes (as class,title...) to "Zend\Form\Select" options](https://github.com/zendframework/zf2/issues/3792)
- [3812: Zend\Form\FormInterface causes Di to attempt to instantiate Interface](https://github.com/zendframework/zf2/issues/3812)
- [3814: Improve module manager to accept instance](https://github.com/zendframework/zf2/issues/3814)
- [3818: Invalid instantiator of type “NULL” for “Zend\Form\FormInterface”](https://github.com/zendframework/zf2/issues/3818)
- [3844: Added new option to fix a little issue originated from last PR](https://github.com/zendframework/zf2/issues/3844)
- [3876: Implementing and re-utilizing an abstract aggregate listener](https://github.com/zendframework/zf2/issues/3876)
- [3877: HeadTitle renderTitle returns rendered title without title tags](https://github.com/zendframework/zf2/issues/3877)
- [3878: Created an adapter Zend Paginator instance using TableGateway](https://github.com/zendframework/zf2/issues/3878)
- [3879: Feature CollectionInputFilter](https://github.com/zendframework/zf2/issues/3879)
- [3896: Added ability to ignore namespaces to classmap generator](https://github.com/zendframework/zf2/issues/3896)
- [3919: WSDL Generation rewrite (with new tests also) as a base for future changes.](https://github.com/zendframework/zf2/issues/3919)
- [3922: Added the ability to disable the getValidator input specification on Select Elements](https://github.com/zendframework/zf2/issues/3922)
- [3930: Added abstract service factory for logger component to provide several loggers for application.](https://github.com/zendframework/zf2/issues/3930)
- [3931: Added ability to configure MvcEvent listeners.](https://github.com/zendframework/zf2/issues/3931)
- [3933: Added database adapter abstract service factory.](https://github.com/zendframework/zf2/issues/3933)
- [3942: Feature/zend test load module](https://github.com/zendframework/zf2/issues/3942)
- [3944: Enable ExceptionStrategy to return json](https://github.com/zendframework/zf2/issues/3944)
- [3949: Invalid argument supplied for foreach()](https://github.com/zendframework/zf2/issues/3949)
- [3951: Deprecate Zend\Stdlib\DateTime and use \DateTime constructor internally instead](https://github.com/zendframework/zf2/issues/3951)
- [3958: Oci8 Driver generating "Fetch out of sequence warning"](https://github.com/zendframework/zf2/issues/3958)
- [3965: Add removeMethod method in ClassGenerator](https://github.com/zendframework/zf2/issues/3965)
- [3979: Fixes #3978](https://github.com/zendframework/zf2/issues/3979)
- [3990: Zend\Filter\File\RenameUpload - Added possibility to maintain original file extension](https://github.com/zendframework/zf2/issues/3990)
- [3999: Chain route](https://github.com/zendframework/zf2/issues/3999)
- [4011: extend HeadMeta view helper to allow microdata #3751](https://github.com/zendframework/zf2/issues/4011)
- [4016: Hydrator aware interface](https://github.com/zendframework/zf2/issues/4016)
- [4032: Class was supporting limit + offset or limit, but only offset does not support](https://github.com/zendframework/zf2/issues/4032)
- [4048: Moved ext-intl to suggest instead of require to avoid silent fallback.](https://github.com/zendframework/zf2/issues/4048)
- [4050: Translable routing segments](https://github.com/zendframework/zf2/issues/4050)
- [4073: Fixed issue #3064](https://github.com/zendframework/zf2/issues/4073)
- [4098: fix php docblock : boolean should be bool](https://github.com/zendframework/zf2/issues/4098)
- [4099: fix (bool) casting : add space and use (bool) instead of (boolean) to cast](https://github.com/zendframework/zf2/issues/4099)
- [4104: Allow to change option creations for plugin manager](https://github.com/zendframework/zf2/issues/4104)
- [4120: (Validator) Only return unique messages](https://github.com/zendframework/zf2/issues/4120)
- [4127: Added I18n PhoneNumber validator based off of country](https://github.com/zendframework/zf2/issues/4127)
- [4137: View helpers cleanup](https://github.com/zendframework/zf2/issues/4137)
- [4139: Service manager performance optimized](https://github.com/zendframework/zf2/issues/4139)
- [4145: Delegate factories](https://github.com/zendframework/zf2/issues/4145)
- [4146: Lazy services](https://github.com/zendframework/zf2/issues/4146)
- [4155: Move Identity closure to separate factory](https://github.com/zendframework/zf2/issues/4155)
- [4165: Validate empty with context](https://github.com/zendframework/zf2/issues/4165)
- [4169: Fixed error in adapter paginator DbTableGateway](https://github.com/zendframework/zf2/issues/4169)
- [4170: Hydrator aware interface](https://github.com/zendframework/zf2/issues/4170)
- [4175: AbstractRestfulController uses wrong action for id=0](https://github.com/zendframework/zf2/issues/4175)
- [4178: Allow passing objects to the url helper](https://github.com/zendframework/zf2/issues/4178)
- [4181: Make identifier name configurable for AbstractRestfulController](https://github.com/zendframework/zf2/issues/4181)
- [4187: Add event manager as soft dependency to translator](https://github.com/zendframework/zf2/issues/4187)
- [4202: Zend\Log has dependency on Zend\ServiceManager](https://github.com/zendframework/zf2/issues/4202)
- [4204: Hotfix for #4202](https://github.com/zendframework/zf2/issues/4204)
- [4206: Added sequence name for PostgreSQL](https://github.com/zendframework/zf2/issues/4206)
- [4215: Bugfix for redirection handling in Zend\Http\Client](https://github.com/zendframework/zf2/issues/4215)
- [4219: Custom validators registered through ValidatorProviderInterface not found](https://github.com/zendframework/zf2/issues/4219)
- [4231: (Form) Get Elements for Collection](https://github.com/zendframework/zf2/issues/4231)
- [4238: ValueGenerator constant detection](https://github.com/zendframework/zf2/issues/4238)
- [4247: Added Brazilian IBAN format to IBAN validation](https://github.com/zendframework/zf2/issues/4247)
- [4250: (#4249) Override 'ServiceManager::has' to do not use peering service managers](https://github.com/zendframework/zf2/issues/4250)
- [4251: Create factories for selected view collaborators](https://github.com/zendframework/zf2/issues/4251)
- [4252: Auto-upgrading and then displaying composer version](https://github.com/zendframework/zf2/issues/4252)
- [4253: Create AbstractFactory for Cache](https://github.com/zendframework/zf2/issues/4253)
- [4254: Use prefix in Logger abstract factory](https://github.com/zendframework/zf2/issues/4254)
- [4259: Hotfix: Changed array\_walk to foreach in Zend\Stdlib\Hydrator\ArraySerializable](https://github.com/zendframework/zf2/issues/4259)
- [4260: Validator\Explode can take option validator as array](https://github.com/zendframework/zf2/issues/4260)
- [4262: Fixed console routes when using same name for group and parameter](https://github.com/zendframework/zf2/issues/4262)
- [4263: Remove superfluous indentation from one line of code](https://github.com/zendframework/zf2/issues/4263)
- [4268: Session service factories](https://github.com/zendframework/zf2/issues/4268)
- [4269: Hotfix: cs fixer check](https://github.com/zendframework/zf2/issues/4269)
- [4276: allow default http responses to be sent in mvc stack](https://github.com/zendframework/zf2/issues/4276)
- [4279: Remove needless is\_object check](https://github.com/zendframework/zf2/issues/4279)
- [4282: fix getHref strategy in PageMvc](https://github.com/zendframework/zf2/issues/4282)
- [4284: Main framework composer.json is incorrectly configured](https://github.com/zendframework/zf2/issues/4284)
- [4285: Fix for a problem with Service Manager and Abstract Factories](https://github.com/zendframework/zf2/issues/4285)
- [4288: Reset URI parts before parse](https://github.com/zendframework/zf2/issues/4288)
- [4289: Minor CS fix](https://github.com/zendframework/zf2/issues/4289)
- [4293: Better fix for #4284](https://github.com/zendframework/zf2/issues/4293)
- [4294: BaseInputFilter not populating InputFilters of Element\Collection](https://github.com/zendframework/zf2/issues/4294)
- [4295: Console route defaults should be overridden by entered values](https://github.com/zendframework/zf2/issues/4295)
- [4296: illegal usage of array\_walk in ObjectProperty, ClassMapAutoloader](https://github.com/zendframework/zf2/issues/4296)
- [4298: View\Helper\Navigation\Menu: add flag to set page class to &lt;li&gt;](https://github.com/zendframework/zf2/issues/4298)
- [4299: Suggestion: Don't render empty module console information](https://github.com/zendframework/zf2/issues/4299)
- [4300: Maestro detection improvements in Zend\Validator\CreditCard](https://github.com/zendframework/zf2/issues/4300)
- [4301: remove extra semicolon](https://github.com/zendframework/zf2/issues/4301)
- [4303: Method annotations of Zend\Validator\Hostname constructor](https://github.com/zendframework/zf2/issues/4303)
- [4311: DDL support for Zend\Db](https://github.com/zendframework/zf2/issues/4311)
- [4312: POP3 protocol "return;" is needed after APOP request](https://github.com/zendframework/zf2/issues/4312)
- [4313: update docblock for ZendTest : /Db/, /Code/ , /Di/, /Log/, Mvc/](https://github.com/zendframework/zf2/issues/4313)
- [4317: Fix #4315 - Console routes with dashes are not understood.](https://github.com/zendframework/zf2/issues/4317)
- [4319: Add various plugin manager](https://github.com/zendframework/zf2/issues/4319)
- [4321: Hotfix/cs fixer installation](https://github.com/zendframework/zf2/issues/4321)
- [4326: Add zh\_TW translations ](https://github.com/zendframework/zf2/issues/4326)
- [4328: Fix 4294](https://github.com/zendframework/zf2/issues/4328)
- [4330: Remove SM-Aware requirement from Forward plugin](https://github.com/zendframework/zf2/issues/4330)
- [4331: Changed default version service to Zend.](https://github.com/zendframework/zf2/issues/4331)
- [4336: Use is\_int() instead of is\_integer()](https://github.com/zendframework/zf2/issues/4336)
- [4337: Fix alignment of values, add trailing comma](https://github.com/zendframework/zf2/issues/4337)
- [4339: Remove @return annotation from constructor doc-block](https://github.com/zendframework/zf2/issues/4339)
- [4341: Docblocks do not match](https://github.com/zendframework/zf2/issues/4341)
- [4344: Add missing file level doc-block](https://github.com/zendframework/zf2/issues/4344)
- [4347: Add empty line after namespace declaration](https://github.com/zendframework/zf2/issues/4347)
- [4349: Alphabetically order use statements (related to #4338)](https://github.com/zendframework/zf2/issues/4349)
- [4350: Remove comma before value in array initialization](https://github.com/zendframework/zf2/issues/4350)
- [4351: fix the constructor's type-autodetection accepts wrong parameters](https://github.com/zendframework/zf2/issues/4351)
- [4352: Fix doc blocks consistency and coding standards PSR2](https://github.com/zendframework/zf2/issues/4352)
- [4353: Glob::glob() should throw an exception on error](https://github.com/zendframework/zf2/issues/4353)
- [4354: Corrected wrong year](https://github.com/zendframework/zf2/issues/4354)
- [4355: fix docblock : @throw should be @throws](https://github.com/zendframework/zf2/issues/4355)
- [4356: FormSelect translate optgroup label fix](https://github.com/zendframework/zf2/issues/4356)
- [4358: Form abstract factory](https://github.com/zendframework/zf2/issues/4358)
- [4361: Ldap Ldif Decoder bug fix](https://github.com/zendframework/zf2/issues/4361)
- [4364: AbstractFactory consistency](https://github.com/zendframework/zf2/issues/4364)
- [4365: Use InputFilterPluginManager in InputFilter\Factory](https://github.com/zendframework/zf2/issues/4365)
- [4366: Fix for issue #3945, and fix for PUT with request content](https://github.com/zendframework/zf2/issues/4366)
- [4367: Remove reference to root namespace (fixes #4363)](https://github.com/zendframework/zf2/issues/4367)
- [4372: Ability to load custom form classes from FormElementManager in Mvc.](https://github.com/zendframework/zf2/issues/4372)
- [4373: PHP Warning:  call\_user\_func() expects…when Weakref enabled](https://github.com/zendframework/zf2/issues/4373)
- [4374: CollectionInputFilter returns always valid for empty collections](https://github.com/zendframework/zf2/issues/4374)
- [4376: Fix get with body in ClientStatic](https://github.com/zendframework/zf2/issues/4376)
- [4378: Add patchList method to AbstractRestfulController](https://github.com/zendframework/zf2/issues/4378)
- [4379: Fix for #4175](https://github.com/zendframework/zf2/issues/4379)
- [4380: Decouple I18n\View\Helper\AbstractTranslatorHelper from ext\intl](https://github.com/zendframework/zf2/issues/4380)
- [4382: Fix conflict InputFilter::type with Input::name in InputFilter factory](https://github.com/zendframework/zf2/issues/4382)
- [4383: ensure the wrapElements option in Zend\Form\Form::prepareElement](https://github.com/zendframework/zf2/issues/4383)
- [4389: Remove cache and log abstract factories from MVC](https://github.com/zendframework/zf2/issues/4389)
- [4391: Segregated interfaces for Translator dependency of Validator component](https://github.com/zendframework/zf2/issues/4391)
- [4392: Remove Version dependency from Feed component](https://github.com/zendframework/zf2/issues/4392)
- [4393: 2.2RC1 BC Break: DateTimeFormatter sets blank data to today's date](https://github.com/zendframework/zf2/issues/4393)
- [4394: Ensure that DateTimeFormatter doesn't format an empty string](https://github.com/zendframework/zf2/issues/4394)
- [4396: Make ServiceManager dependency optional in Feed component](https://github.com/zendframework/zf2/issues/4396)
- [4398: Allow DateTimeFormatter to format zero.](https://github.com/zendframework/zf2/issues/4398)
- [4405: 2.2.0RC1 Form\View\Helper\FormRow "partial view" messed up](https://github.com/zendframework/zf2/issues/4405)
- [4408: Optimize MutableCreationOptionsInterface capability](https://github.com/zendframework/zf2/issues/4408)
- [4410: Fix conflict between translator service in ZF2 and skeleton app](https://github.com/zendframework/zf2/issues/4410)
- [4411: Fix BC break in HTTP client resetParameters signature](https://github.com/zendframework/zf2/issues/4411)
- [4412: FormRow: enable partial rendering](https://github.com/zendframework/zf2/issues/4412)
- [4415: Remove URI dependency and make HTTP dependency optional in Feed](https://github.com/zendframework/zf2/issues/4415)
- [4417: add docblock to I18n\Validator\PhoneNumber\{Code\}.php](https://github.com/zendframework/zf2/issues/4417)
- [4418: remove @package docblock from demos files](https://github.com/zendframework/zf2/issues/4418)
- [4420: sync svn r23693 - (ZF-11002) ehancement implemented as proposed](https://github.com/zendframework/zf2/issues/4420)
- [4423: Minor param overflow](https://github.com/zendframework/zf2/issues/4423)
- [4424: Edit config composer.json](https://github.com/zendframework/zf2/issues/4424)
- [4425: Fix FormElementManagerFactory breaks csrf validation (in Mvc)](https://github.com/zendframework/zf2/issues/4425)
- [4431: sync svn r24702 - support application/x-zip in Validator\File\IsCompressed](https://github.com/zendframework/zf2/issues/4431)
- [4432: code concistency : update Zend\Mvc\Application::bootstrap](https://github.com/zendframework/zf2/issues/4432)
- [4435: Di compatibility (#4434)](https://github.com/zendframework/zf2/issues/4435)
- [4437: I18n currencyFormat helper: add the currencyPattern attribute and extend the unittest](https://github.com/zendframework/zf2/issues/4437)
- [4441: Fixed unnecessary error rendering in form row helper.](https://github.com/zendframework/zf2/issues/4441)
- [4444: Issues found by hphp static analysis](https://github.com/zendframework/zf2/issues/4444)
- [4447: typo fixes](https://github.com/zendframework/zf2/issues/4447)
- [4448: Aggregate hydrator ](https://github.com/zendframework/zf2/issues/4448)
- [4450: Fix iterating over empty result set with buffering enabled](https://github.com/zendframework/zf2/issues/4450)
- [4451: Form InputFilterSpecification: incorrect propagation](https://github.com/zendframework/zf2/issues/4451)
- [4454: Fix for expiration value](https://github.com/zendframework/zf2/issues/4454)

### Potential Breakage

`Zend\Validator` was altered to remove the dependency on `Zend\I18n` by creating
[Segregated Interfaces](http://en.wikipedia.org/wiki/Interface_segregation_principle).
The practical upshot is that `Zend\Validator\AbstractValidator` no longer
implements `Zend\I18n\Translator\TranslatorAwareInterface`, but rather
`Zend\Validator\Translator\TranslatorAwareInterface`, which now typehints on
`Zend\Validator\Translator\TranslatorInterface` instead of
`Zend\I18n\Translator\Translator`. This means you cannot pass a
`Zend\I18n\Translator\Translator` instance directly to a validator any longer.

However, we have included a new class, `Zend\Mvc\I18n\Translator`, that extends
the i18n Translator class and implements the Validator TranslatorInterface. This
class may be used as a drop-in replacement. In fact, by default,
`Zend\Validator\ValidatorPluginManager` is now using the `MvcTranslator`
service, which utilizes this new class, making the change seamless for most
users.

The above change will only affect you if you were manually injecting a
translator instance into your validators.

## 2.1.5 (17 Apr 2013):

- 2536: `Zend\Validate` translations out of date
  (https://github.com/zendframework/zf2/issues/2536)
- 2898: `ConstructedNavigationFactory` does not inject components
  (https://github.com/zendframework/zf2/issues/2898)
- 3373: `Collection` in `Form` not binds values when form has no object and hydrator set
  (https://github.com/zendframework/zf2/issues/3373)
- 3534: ZF2 2.0.6 Authentication and postgres database
  (https://github.com/zendframework/zf2/issues/3534)
- 3626: `Zend\Form\View\Helper\FormRow`: labels are appended by default
  (https://github.com/zendframework/zf2/issues/3626)
- 3685: Problem on appending new identifier on `EventManager`
  (https://github.com/zendframework/zf2/issues/3685)
- 3695: Adapter name and sequence problems
  (https://github.com/zendframework/zf2/issues/3695)
- 3719: `Zend\Db\Metadata\Source\AbstractSource` Notice: Undefined index
  (https://github.com/zendframework/zf2/issues/3719)
- 3731: Console banners are all shown consecutively
  (https://github.com/zendframework/zf2/issues/3731)
- 3882: `EventManager` or `Stdlib\CallbackHandler` can't handle `WeakRef` enough.
  (https://github.com/zendframework/zf2/issues/3882)
- 3898: `Zend\Navigation\Service\ConstructedNavigationFactory` not inject
  dependences (router, action and etc)
  (https://github.com/zendframework/zf2/issues/3898)
- 3912: Ajustment `SequenceFeature` generic drivers
  (https://github.com/zendframework/zf2/issues/3912)
- 3934: `Acl` allow role access on all resources not honoured if added after resources
  (https://github.com/zendframework/zf2/issues/3934)
- 3983: Update `BaseInputFilter`
  (https://github.com/zendframework/zf2/issues/3983)
- 4002: Update `DocBlockScanner`
  (https://github.com/zendframework/zf2/issues/4002)
- 4013: Fix PHP Notice in `Translator` class
  (https://github.com/zendframework/zf2/issues/4013)
- 4014: update to `FlashMessenger` view helper to allow for classes on separator
  (https://github.com/zendframework/zf2/issues/4014)
- 4020: Add parent roles with traversable object
  (https://github.com/zendframework/zf2/issues/4020)
- 4026: `Zend\Validator` Test Suite Fix
  (https://github.com/zendframework/zf2/issues/4026)
- 4027: Move deprecation notice inside constructor of `Query` class
  (https://github.com/zendframework/zf2/issues/4027)
- 4035: [Router] non existent child route during assembly doesn't throw exception
  (https://github.com/zendframework/zf2/issues/4035)
- 4037: Remove unnecessary `autoload.php` from composer config.
  (https://github.com/zendframework/zf2/issues/4037)
- 4047: Update `InArray.php`
  (https://github.com/zendframework/zf2/issues/4047)
- 4049: removed unused cache test assets from test suite
  (https://github.com/zendframework/zf2/issues/4049)
- 4051: `writeLine()` with console is (literally) breaking when the string is "too long"?
  (https://github.com/zendframework/zf2/issues/4051)
- 4053: Implement better text domain merging support
  (https://github.com/zendframework/zf2/issues/4053)
- 4054: 2.1.4: `Zend/Stdlib/composer.json` requires  "Zend/Stdlib/compatibility/autoload.php"
  (https://github.com/zendframework/zf2/issues/4054)
- 4055: Fix #4051 `console::writeLine()` 
  (https://github.com/zendframework/zf2/issues/4055)
- 4061: Normalize console usage
  (https://github.com/zendframework/zf2/issues/4061)
- 4063: Resolved Issue #2898
  (https://github.com/zendframework/zf2/issues/4063)
- 4064: Fixed issue with invalid `@cover` annotations, pointed to not existed class
  (https://github.com/zendframework/zf2/issues/4064)
- 4066: `HttpControllerTestCase` gives wrong messages for `assertRedirect`/`assertNotRedirect`
  (https://github.com/zendframework/zf2/issues/4066)
- 4070: Hotfix for issue #4069
  (https://github.com/zendframework/zf2/issues/4070)
- 4074: fix typos
  (https://github.com/zendframework/zf2/issues/4074)
- 4075: `Form\Collection`: allow create new objects
  (https://github.com/zendframework/zf2/issues/4075)
- 4077: Fix `Collection` form element replacing bound objects with dummies upon form validation
  (https://github.com/zendframework/zf2/issues/4077)
- 4079: Some fixes for phpDoc in `Zend\Mvc`
  (https://github.com/zendframework/zf2/issues/4079)
- 4084: Introduce query parameter for `Navigation\Page\Mvc`
  (https://github.com/zendframework/zf2/issues/4084)
- 4085: Fix loading of a text domain from different sources, fixes issue #4045
  (https://github.com/zendframework/zf2/issues/4085)
- 4089: Zend\Test - set the request's `requestUri` to the dispatched url
  (https://github.com/zendframework/zf2/issues/4089)
- 4095: `Zend\Navigation\Page\Mvc::getHref` does not use `RouteMatch` parameters
  (https://github.com/zendframework/zf2/issues/4095)
- 4102: simplify constant usage. `FILEINFO_MIME_TYPE` is available since PHP 5.3.0
  (https://github.com/zendframework/zf2/issues/4102)
- 4103: `FormDateTimeSelect` - minutes delimiter always shown
  (https://github.com/zendframework/zf2/issues/4103)
- 4111: Updated translations
  (https://github.com/zendframework/zf2/issues/4111)
- 4117: [InputFilter] Allow specification of error message via `Factory`
  (https://github.com/zendframework/zf2/issues/4117)
- 4118: Fix name of variable used for capturing output when executing shell command
  (https://github.com/zendframework/zf2/issues/4118)
- 4119: Fix weird verbalization
  (https://github.com/zendframework/zf2/issues/4119)
- 4123: Fix#3373
  (https://github.com/zendframework/zf2/issues/4123)
- 4129: Update to `ServiceManager` to provide more precise error messages
  (https://github.com/zendframework/zf2/issues/4129)
- 4133: Fix#4103
  (https://github.com/zendframework/zf2/issues/4133)
- 4134: Zend\Mvc\Router\Console\Simple not compatible with older versions of pcre (and therefore CentOS)
  (https://github.com/zendframework/zf2/issues/4134)
- 4135: Update Czech validator messages
  (https://github.com/zendframework/zf2/issues/4135)
- 4138: Modified Router to use backwards compatible regex expression Issue: 4134
  (https://github.com/zendframework/zf2/issues/4138)
- 4140: When displaying navigations three times last navigation has data of prev...
  (https://github.com/zendframework/zf2/issues/4140)
- 4143: Fixed issue #3626
  (https://github.com/zendframework/zf2/issues/4143)
- 4144: feature / `quoteTrustedValueList`
  (https://github.com/zendframework/zf2/issues/4144)
- 4147: Reset stop-propagation flag when triggering event
  (https://github.com/zendframework/zf2/issues/4147)
- 4148: Filters priority setting when populating filters in inputfilter factory and not losing it when merging filter chains
  (https://github.com/zendframework/zf2/issues/4148)
- 4150: Hotfix - `callable` type introspection for method parameters
  (https://github.com/zendframework/zf2/issues/4150)
- 4152: Fixed some EMail Validation Strings - German
  (https://github.com/zendframework/zf2/issues/4152)
- 4153: [Feed] sync svn r24842 - Fix ZF-4491
  (https://github.com/zendframework/zf2/issues/4153)
- 4154: Catch `LogicException` for Rewind and fix CP errors
  (https://github.com/zendframework/zf2/issues/4154)
- 4157: end autoload classmap generated file with EOL
  (https://github.com/zendframework/zf2/issues/4157)
- 4161: servicemanager is a requirement
  (https://github.com/zendframework/zf2/issues/4161)
- 4164: Fetch model from event parameter
  (https://github.com/zendframework/zf2/issues/4164)
- 4167: `Console` posix adapter `writeLine()` background color bleeding through to the next line.
  (https://github.com/zendframework/zf2/issues/4167)
- 4168: Fix #4167 - Console posix adapter `writeLine()` background color bleeding through to the next line.
  (https://github.com/zendframework/zf2/issues/4168)
- 4171: Fix BC break in 2.1.5dev - Revert to previous `isRequired` behavior for file upload inputs
  (https://github.com/zendframework/zf2/issues/4171)
- 4172: [Form] Remove after Add doesn't restore initial state
  (https://github.com/zendframework/zf2/issues/4172)
- 4180: Radio & Multicheckbox Problem with selected & disabled attributes
  (https://github.com/zendframework/zf2/issues/4180)
- 4182: Issue #3358 - Fix for console router not accepting controller word as part of a route
  (https://github.com/zendframework/zf2/issues/4182)
- 4183: Update `Zend_Validate.php` resource
  (https://github.com/zendframework/zf2/issues/4183)
- 4184: Updated `Page\Mvc::getHref` to grab correct controller name from `routeMatch`
  (https://github.com/zendframework/zf2/issues/4184)
- 4191: `Zend\Stdlib\Hydrator\ClassMethods::hydrate()` - support for `__call()` magic method
  (https://github.com/zendframework/zf2/issues/4191)
- 4198: fixed typo in french `Zend_Validator_StringLength`
  (https://github.com/zendframework/zf2/issues/4198)
- 4199:  Issue #4172 - Fixed empty priority queue state
  (https://github.com/zendframework/zf2/issues/4199)
- 4201: Issue #4172 - Added tests for add/remove sequence in `Zend\Form`
  (https://github.com/zendframework/zf2/issues/4201)
- 4203: Allow an instance of `Zend\Stdlib\AbstractOptions` to set configuration properties of the same class
  (https://github.com/zendframework/zf2/issues/4203)
- 4207: Fixed default plural rule.
  (https://github.com/zendframework/zf2/issues/4207)
- 4210: Fixed failure when implementing custom rbac roles
  (https://github.com/zendframework/zf2/issues/4210)
- 4213: [Curl] `setOptions` should merge config items that can be arrays
  (https://github.com/zendframework/zf2/issues/4213)
- 4216: Require `Zend\Config` in `Zend\Mvc`
  (https://github.com/zendframework/zf2/issues/4216)
- 4224: `Mail\Headers.php`: Adjust regex for field name to RFC 5322
  (https://github.com/zendframework/zf2/issues/4224)
- 4225: change variable naming
  (https://github.com/zendframework/zf2/issues/4225)
- 4226: ZF2 ACL full access
  (https://github.com/zendframework/zf2/issues/4226)
- 4227: Updated `Zend_Captcha` and `Zend_Validate` for catalan language
  (https://github.com/zendframework/zf2/issues/4227)
- 4232: Correct tests for group multicheckbox & radio attributes
  (https://github.com/zendframework/zf2/issues/4232)
- 4233: remove mistake doc for `Zend\Http\PhpEnvironment\Request::detectBaseUrl()`
  (https://github.com/zendframework/zf2/issues/4233)
- 4235: fixed `setEventManager`
  (https://github.com/zendframework/zf2/issues/4235)
- 4236: Update `ProvidesEvents.php`
  (https://github.com/zendframework/zf2/issues/4236)
- 4237: Update `ModuleManager.php`
  (https://github.com/zendframework/zf2/issues/4237)
- 4239: Remove annotation in `Zend\Db\Adapter\AdapterAwareTrait`
  (https://github.com/zendframework/zf2/issues/4239)
- 4240: A Better fix for #3912
  (https://github.com/zendframework/zf2/issues/4240)
- 4241: `Zend\Db\Metadata` - remove quoting of known scalars, use `quoteTrustedValue()` for provided values
  (https://github.com/zendframework/zf2/issues/4241)
- 4242: fix `Zend\Json` doc and little typo
  (https://github.com/zendframework/zf2/issues/4242)
- 4243: remove `if` `else` for same return
  (https://github.com/zendframework/zf2/issues/4243)
- 4244: remove unused `require_once __DIR__ . '/SplAutoloader.php';`
  (https://github.com/zendframework/zf2/issues/4244)
- 4246: replaced `get_called_class()` with `get_class($this)` in non-static context
  (https://github.com/zendframework/zf2/issues/4246)

## 2.1.4 (13 Mar 2013):

- ZF2013-01: Query route (http://framework.zend.com/security/ZF2013-01)
- ZF2013-02: RNG support (http://framework.zend.com/security/ZF2013-02)
- ZF2013-03: DB platform quoting (http://framework.zend.com/security/ZF2013-03)
- 2752: `Zend_Json_Server` to accept null parameters
  (https://github.com/zendframework/zf2/issues/2752)
- 3696: `Zend\Json\Server\Server` should allow parameters with NULL values
  (https://github.com/zendframework/zf2/issues/3696)
- 3767: Allow NULL parameter values in `Zend/Json/Server`
  (https://github.com/zendframework/zf2/issues/3767)
- 3827: Fix mismatches between the PHPDoc and the method signatures
  (https://github.com/zendframework/zf2/issues/3827)
- 3840: allow a null page in pages array, to compensate for ZF issue #3823
  (https://github.com/zendframework/zf2/issues/3840)
- 3842: Hotfix/zend test improve console usage
  (https://github.com/zendframework/zf2/issues/3842)
- 3849: Check if values are set in `Zend\Db\Sql\Insert.php` for prepared
  statement
  (https://github.com/zendframework/zf2/issues/3849)
- 3867: `FileGenerator::setUses()` MUST can take arguments from
  `FileGenerator::getUses()`
  (https://github.com/zendframework/zf2/issues/3867)
- 3868: `ClassGenerator::fromReflection` not generate class properties
  (https://github.com/zendframework/zf2/issues/3868)
- 3869: Remove BC break in `Identical` validator
  (https://github.com/zendframework/zf2/issues/3869)
- 3871: The method delete on the `RowGateway` now returns the affected rows
  (https://github.com/zendframework/zf2/issues/3871)
- 3873: Fixes an issue when binding a model to a form collection element
  (https://github.com/zendframework/zf2/issues/3873)
- 3885: Hotfix/add tests console adapter
  (https://github.com/zendframework/zf2/issues/3885)
- 3886: Add tests console prompt
  (https://github.com/zendframework/zf2/issues/3886)
- 3888: `DefinitionList` `hasMethod` fix
  (https://github.com/zendframework/zf2/issues/3888)
- 3907: Add tests console request response
  (https://github.com/zendframework/zf2/issues/3907)
- 3916: Fix PUT HTTP method usage with params
  (https://github.com/zendframework/zf2/issues/3916)
- 3917: Clean the Console abstract adapter
  (https://github.com/zendframework/zf2/issues/3917)
- 3921: [+BUGFIX] Fixed column names bug `Zend\Db\Sql\Select`
  (https://github.com/zendframework/zf2/issues/3921)
- 3925: Added view and validator dependency
  (https://github.com/zendframework/zf2/issues/3925)
- 3936: Improve the remove of `SendResponseListener`
  (https://github.com/zendframework/zf2/issues/3936)
- 3946: Adding config to `openssl_pkey_export()`
  (https://github.com/zendframework/zf2/issues/3946)
- 3947: fix exception %s passed variable of 'A service by the name or alias %s'  should be $name
  (https://github.com/zendframework/zf2/issues/3947)
- 3948: Bug/merging translator textdomains
  (https://github.com/zendframework/zf2/issues/3948)
- 3950: Fix zero value in argument
  (https://github.com/zendframework/zf2/issues/3950)
- 3957: [Hotfix] Fixed incorrect `PDO_Oci` platform recognition
  (https://github.com/zendframework/zf2/issues/3957)
- 3960: Update toString() to use late static binding for encoding methods
  (https://github.com/zendframework/zf2/issues/3960)
- 3964: Fix fluent interface
  (https://github.com/zendframework/zf2/issues/3964)
- 3966: Better polyfill support for `Stdlib` and `Session`
  (https://github.com/zendframework/zf2/issues/3966)
- 3968: fixed `Exception\InvalidArgumentException` messages in `Zend\Log`
  (https://github.com/zendframework/zf2/issues/3968)
- 3971: SessionArrayStorage doesn't preserve `_REQUEST_ACCESS_TIME`
  (https://github.com/zendframework/zf2/issues/3971)
- 3973: Documentation improvement `Zend\View\Stream`
  (https://github.com/zendframework/zf2/issues/3973)
- 3980: change `HOST_DNS_OR_IPV4_OR_IPV6` to `0x13` for `$validHostTypes`
  (https://github.com/zendframework/zf2/issues/3980)
- 3981: Improve exception messages
  (https://github.com/zendframework/zf2/issues/3981)
- 3982: Fix `\Zend\Soap\AutoDiscover` constructor
  (https://github.com/zendframework/zf2/issues/3982)
- 3984: Update `ArrayStack.php`
  (https://github.com/zendframework/zf2/issues/3984)
- 3987: Fix ChromePhp logger interface and debug level
  (https://github.com/zendframework/zf2/issues/3987)
- 3988: Fix & Unit test for `preparestatement` notices
  (https://github.com/zendframework/zf2/issues/3988)
- 3991: Hotfix/3858 - `findHelper` problem in Navigation Helper
  (https://github.com/zendframework/zf2/issues/3991)
- 3993: `SessionArrayStorage` Request Access Time and Storage Initialization
  (https://github.com/zendframework/zf2/issues/3993)
- 3997: Allow https on scheme without a hostname
  (https://github.com/zendframework/zf2/issues/3997)
- 4001: Fix `ViewFeedStrategyFactory` comment
  (https://github.com/zendframework/zf2/issues/4001)
- 4005: Hotfix/case sensitive console
  (https://github.com/zendframework/zf2/issues/4005)
- 4007: Pass `ClassGenerator` instance instead of boolean
  (https://github.com/zendframework/zf2/issues/4007)
- 4009: Minor if to else if improvement
  (https://github.com/zendframework/zf2/issues/4009)
- 4010: Hotfix/zend test with console route
  (https://github.com/zendframework/zf2/issues/4010)

## 2.1.3 (21 Feb 2013):

- 3714: Zend\Stdlib\ArrayObject::offsetExists() returning by reference
  (https://github.com/zendframework/zf2/issues/3714)
- 3855: Fix #3852
  (https://github.com/zendframework/zf2/issues/3855)
- 3856: Simple route case insensitive
  (https://github.com/zendframework/zf2/issues/3856)

## 2.1.2 (20 Feb 2013):

- 3085: create controller via Zend\Mvc\Controller\ControllerManager
  (https://github.com/zendframework/zf2/issues/3085)
- 3469: ConnectionInterface docblock is wrong or implementation is wrong..
  (https://github.com/zendframework/zf2/issues/3469)
- 3506: [WIP] [#3113] Fix spelling in error validation messages
  (https://github.com/zendframework/zf2/issues/3506)
- 3636: If route has child routes and in URL has arbitrary query like "?lang=de"
  it does not work
  (https://github.com/zendframework/zf2/issues/3636)
- 3652: Query parameter ?action=somevalue will get 404 error
  (https://github.com/zendframework/zf2/issues/3652)
- 3683: Fix to make sure NotEmpty validator is not already set
  (https://github.com/zendframework/zf2/issues/3683)
- 3691: Fix for GitHub issue 3469
  (https://github.com/zendframework/zf2/issues/3691)
- 3698: Openssl error string
  (https://github.com/zendframework/zf2/issues/3698)
- 3699: Certain servers may not set a whitespace after a colon 
  (Set-Cookie: header)
  (https://github.com/zendframework/zf2/issues/3699)
- 3701: Synced pt\_BR\Zend\_Validate.php with en\Zend\_Validate.php
  (https://github.com/zendframework/zf2/issues/3701)
- 3702: added new file: resources\languages\pt\_BR\Zend\_Captcha.php
  (https://github.com/zendframework/zf2/issues/3702)
- 3703: [WIP] Adding parallel testing ANT build configuration and related files
  (https://github.com/zendframework/zf2/issues/3703)
- 3705: Recent composer.json update of stdlib package
  (https://github.com/zendframework/zf2/issues/3705)
- 3706: clear joins and create without columns
  (https://github.com/zendframework/zf2/issues/3706)
- 3707: quoteIdentifier problem in sequence
  (https://github.com/zendframework/zf2/issues/3707)
- 3708: Filter\File\RenameUpload: wrap move\_uploaded\_file to be easly mocked
  (https://github.com/zendframework/zf2/issues/3708)
- 3712: Fix for URIs with a query string not matching
  (https://github.com/zendframework/zf2/issues/3712)
- 3713: Session Container Mismatch & Version Compare fixes for 5.3.3
  (https://github.com/zendframework/zf2/issues/3713)
- 3715: [#3705] Fix autoload.files setting in composer.json
  (https://github.com/zendframework/zf2/issues/3715)
- 3716: Added the Zend\Form decepence in composer.json for Zend\Mvc
  (https://github.com/zendframework/zf2/issues/3716)
- 3721: Created README.md files for each component
  (https://github.com/zendframework/zf2/issues/3721)
- 3722: [Form] [DateTimeSelect] Filter, manager, and view helper fixes
  (https://github.com/zendframework/zf2/issues/3722)
- 3725: Use built-in php constants
  (https://github.com/zendframework/zf2/issues/3725)
- 3729: Zend\Barcode (Fixes #2862)
  (https://github.com/zendframework/zf2/issues/3729)
- 3732: Fix for #2531 - Multiplie navigation don't work
  (https://github.com/zendframework/zf2/issues/3732)
- 3733: Fix/select where
  (https://github.com/zendframework/zf2/issues/3733)
- 3735: [Form] [FormElementManager] don't overwrite form factory if already set
  (https://github.com/zendframework/zf2/issues/3735)
- 3742: Object+hydrator element annotation fix
  (https://github.com/zendframework/zf2/issues/3742)
- 3743: [#3739 & #3740] Using version-compare in accept header handler params.
  (https://github.com/zendframework/zf2/issues/3743)
- 3746: Fix bugs for some locales!
  (https://github.com/zendframework/zf2/issues/3746)
- 3757: Fixed a bug where mail messages were malformed when using the Sendmail
  (https://github.com/zendframework/zf2/issues/3757)
- 3764: Validator File MimeType (IsImage & IsCompressed)
  (https://github.com/zendframework/zf2/issues/3764)
- 3771: Zend\File\Transfer\Adapter\Http on receive : error "File was not found"  in ZF 2.1
  (https://github.com/zendframework/zf2/issues/3771)
- 3778: [#3711] Fix regression in query string matching
  (https://github.com/zendframework/zf2/issues/3778)
- 3782: [WIP] Zend\Di\Di::get() with call parameters ignored shared instances.
  (https://github.com/zendframework/zf2/issues/3782)
- 3783: Provide branch-alias entries for each component composer.json
  (https://github.com/zendframework/zf2/issues/3783)
- 3785: Zend\Db\Sql\Literal Fix when % is used in string
  (https://github.com/zendframework/zf2/issues/3785)
- 3786: Inject shared event manager in initializer
  (https://github.com/zendframework/zf2/issues/3786)
- 3789: Update library/Zend/Mail/Header/AbstractAddressList.php
  (https://github.com/zendframework/zf2/issues/3789)
- 3793: Resolved Issue: #3748 - offsetGet and __get should do a direct proxy to
  $_SESSION
  (https://github.com/zendframework/zf2/issues/3793)
- 3794: Implement query and fragment assembling into the HTTP router itself
  (https://github.com/zendframework/zf2/issues/3794)
- 3797: remove @category, @package, and @subpackage docblocks
  (https://github.com/zendframework/zf2/issues/3797)
- 3798: Remove extra semicolons
  (https://github.com/zendframework/zf2/issues/3798)
- 3803: Fix identical validator
  (https://github.com/zendframework/zf2/issues/3803)
- 3806: Remove obsolete catch statement
  (https://github.com/zendframework/zf2/issues/3806)
- 3807: Resolve undefined classes in phpDoc
  (https://github.com/zendframework/zf2/issues/3807)
- 3808: Add missing @return annotations
  (https://github.com/zendframework/zf2/issues/3808)
- 3813: Bug fix for GlobIterator extending service
  (https://github.com/zendframework/zf2/issues/3813)
- 3817: Add failing tests for Simple console route
  (https://github.com/zendframework/zf2/issues/3817)
- 3819: Allow form element filter to convert a string to array
  (https://github.com/zendframework/zf2/issues/3819)
- 3828: Cannot validate form when keys of collection in data are non consecutive
  (https://github.com/zendframework/zf2/issues/3828)
- 3831: Non-matching argument type for ArrayObject
  (https://github.com/zendframework/zf2/issues/3831)
- 3832: Zend\Db\Sql\Predicate\Predicate->literal() does not work with integer 0
  as $expressionParameters
  (https://github.com/zendframework/zf2/issues/3832)
- 3836: Zend\Db\Sql\Predicate\Predicate Fix for literal() usage
  (https://github.com/zendframework/zf2/issues/3836)
- 3837: Fix for legacy Transfer usage of File Validators
  (https://github.com/zendframework/zf2/issues/3837)
- 3838: Stdlib\ArrayObject & Zend\Session\Container Compatibility with ArrayObject
  (https://github.com/zendframework/zf2/issues/3838)
- 3839: Fixes #2477 - Implemented optional subdomains using regex
  (https://github.com/zendframework/zf2/issues/3839)

## 2.1.1 (06 Feb 2013):

- 2510: Zend\Session\Container does not allow modification by reference
  (https://github.com/zendframework/zf2/issues/2510)
- 2899: Can't inherit abstract function
  Zend\Console\Prompt\PromptInterface::show()
  (https://github.com/zendframework/zf2/issues/2899)
- 3455: Added DISTINCT on Zend\Db\Sql\Select
  (https://github.com/zendframework/zf2/issues/3455)
- 3456: Connection creation added in Pgsql.php createStatement method
  (https://github.com/zendframework/zf2/issues/3456)
- 3608: Fix validate data contains arrays as values
  (https://github.com/zendframework/zf2/issues/3608)
- 3610: Form: rely on specific setter
  (https://github.com/zendframework/zf2/issues/3610)
- 3618: Fix bug when $indent have some string
  (https://github.com/zendframework/zf2/issues/3618)
- 3622: Updated Changelog with BC notes for 2.1 and 2.0.7
  (https://github.com/zendframework/zf2/issues/3622)
- 3623: Authentication using DbTable Adapter doesn't work for 2.1.0
  (https://github.com/zendframework/zf2/issues/3623)
- 3625: Missing instance/object for parameter route upgrading to 2.1.\*
  (https://github.com/zendframework/zf2/issues/3625)
- 3627: Making relative links in Markdown files
  (https://github.com/zendframework/zf2/issues/3627)
- 3629: Zend\Db\Select using alias in joins can results in wrong SQL
  (https://github.com/zendframework/zf2/issues/3629)
- 3638: Fixed method that removed part from parts in Mime\Message
  (https://github.com/zendframework/zf2/issues/3638)
- 3639: Session Metadata and SessionArrayStorage requestaccesstime fixes.
  (https://github.com/zendframework/zf2/issues/3639)
- 3640: [#3625] Do not query abstract factories for registered invokables
  (https://github.com/zendframework/zf2/issues/3640)
- 3641: Zend\Db\Sql\Select Fix for #3629
  (https://github.com/zendframework/zf2/issues/3641)
- 3645: Exception on destructing the SMTP Transport instance
  (https://github.com/zendframework/zf2/issues/3645)
- 3648: Ensure run() always returns Application instance
  (https://github.com/zendframework/zf2/issues/3648)
- 3649: Created script to aggregate return status
  (https://github.com/zendframework/zf2/issues/3649)
- 3650: InjectControllerDependencies initializer overriding an previously
  defined EventManager
  (https://github.com/zendframework/zf2/issues/3650)
- 3651: Hotfix/3650
  (https://github.com/zendframework/zf2/issues/3651)
- 3656: Zend\Validator\Db\AbstractDb.php and mysqli
  (https://github.com/zendframework/zf2/issues/3656)
- 3658: Zend\Validator\Db\AbstractDb.php and mysqli (issue: 3656)
  (https://github.com/zendframework/zf2/issues/3658)
- 3661: ZF HTTP Status Code overwritten
  (https://github.com/zendframework/zf2/issues/3661)
- 3662: Remove double injection in Plugin Controller Manager
  (https://github.com/zendframework/zf2/issues/3662)
- 3663: Remove useless shared in ServiceManager
  (https://github.com/zendframework/zf2/issues/3663)
- 3671: Hotfix/restful head identifier
  (https://github.com/zendframework/zf2/issues/3671)
- 3673: Add translations for Zend\Validator\File\UploadFile
  (https://github.com/zendframework/zf2/issues/3673)
- 3679: remove '\' character from Traversable 
  (https://github.com/zendframework/zf2/issues/3679)
- 3680: Zend\Validator\Db Hotfix (supersedes #3658)
  (https://github.com/zendframework/zf2/issues/3680)
- 3681: [#2899] Remove redundant method declaration
  (https://github.com/zendframework/zf2/issues/3681)
- 3682: Zend\Db\Sql\Select Quantifier (DISTINCT, ALL, + Expression) support -
  supersedes #3455
  (https://github.com/zendframework/zf2/issues/3682)
- 3684: Remove the conditional class declaration of ArrayObject
  (https://github.com/zendframework/zf2/issues/3684)
- 3687: fix invalid docblock
  (https://github.com/zendframework/zf2/issues/3687)
- 3689: [#3684] Polyfill support for version-dependent classes
  (https://github.com/zendframework/zf2/issues/3689)
- 3690: oracle transaction support
  (https://github.com/zendframework/zf2/issues/3690)
- 3692: Hotfix/db parametercontainer mixed use
  (https://github.com/zendframework/zf2/issues/3692)

## 2.1.0 (29 Jan 2013):

- 2378: ZF2-417 Form Annotation Hydrator options support
  (https://github.com/zendframework/zf2/issues/2378)
- 2390: Expose formally protected method in ConfigListener
  (https://github.com/zendframework/zf2/issues/2390)
- 2405: [WIP] Feature/accepted model controller plugin
  (https://github.com/zendframework/zf2/issues/2405)
- 2424: Decorator plugin manager was pointing to an inexistent class
  (https://github.com/zendframework/zf2/issues/2424)
- 2428: Form develop/allow date time
  (https://github.com/zendframework/zf2/issues/2428)
- 2430: [2.1] Added the scrypt key derivation algorithm in Zend\Crypt
  (https://github.com/zendframework/zf2/issues/2430)
- 2439: [2.1] Form File Upload refactor
  (https://github.com/zendframework/zf2/issues/2439)
- 2486: The Upload validator might be broken
  (https://github.com/zendframework/zf2/issues/2486)
- 2506: Throwing exception in template (and/or layout) doesnt fails gracefully
  (https://github.com/zendframework/zf2/issues/2506)
- 2524: Throws exception when trying to generate bcrypt
  (https://github.com/zendframework/zf2/issues/2524)
- 2537: Create a NotIn predicate
  (https://github.com/zendframework/zf2/issues/2537)
- 2616: Initial ZF2 RBAC Component
  (https://github.com/zendframework/zf2/issues/2616)
- 2629: JsonStrategy should set response charset
  (https://github.com/zendframework/zf2/issues/2629)
- 2647: Fix/bcrypt: added the set/get BackwardCompatibility
  (https://github.com/zendframework/zf2/issues/2647)
- 2668: Implement XCache storage adapter (fixes #2581)
  (https://github.com/zendframework/zf2/issues/2668)
- 2671: Added fluent inteface to prepend and set method. Zend\View\Container\AbstractContainer
  (https://github.com/zendframework/zf2/issues/2671)
- 2725: Feature/logger factory
  (https://github.com/zendframework/zf2/issues/2725)
- 2726: Zend\Validator\Explode does not handle NULL
  (https://github.com/zendframework/zf2/issues/2726)
- 2727: Added ability to add additional information to the logs via processors.
  (https://github.com/zendframework/zf2/issues/2727)
- 2772: Adding cookie route. Going to open PR for comments.
  (https://github.com/zendframework/zf2/issues/2772)
- 2815: Fix fro GitHub issue 2600 (Cannot check if a table is read only)
  (https://github.com/zendframework/zf2/issues/2815)
- 2819: Support for ListenerAggregates in SharedEventManager
  (https://github.com/zendframework/zf2/issues/2819)
- 2820: Form plugin manager
  (https://github.com/zendframework/zf2/issues/2820)
- 2863: Handle postgres sequences
  (https://github.com/zendframework/zf2/issues/2863)
- 2876: memcached changes
  (https://github.com/zendframework/zf2/issues/2876)
- 2884: Allow select object to pass on select->join
  (https://github.com/zendframework/zf2/issues/2884)
- 2888: Bugfix dateformat helper
  (https://github.com/zendframework/zf2/issues/2888)
- 2918: \Zend\Mime\Mime::LINEEND causes problems with some SMTP-Servers
  (https://github.com/zendframework/zf2/issues/2918)
- 2945: SOAP 1.2 support for WSDL generation
  (https://github.com/zendframework/zf2/issues/2945)
- 2947: Add DateTimeSelect element to form
  (https://github.com/zendframework/zf2/issues/2947)
- 2950: Abstract row gatewayset from array
  (https://github.com/zendframework/zf2/issues/2950)
- 2968: Zend\Feed\Reader\Extension\Atom\Entry::getAuthors and Feed::getAuthors
  should return Collection\Author
  (https://github.com/zendframework/zf2/issues/2968)
- 2973: Zend\Db\Sql : Create NotIn predicate
  (https://github.com/zendframework/zf2/issues/2973)
- 2977: Method signature of merge() in Zend\Config\Config prevents mocking
  (https://github.com/zendframework/zf2/issues/2977)
- 2988: Cache: Added storage adapter using a session container
  (https://github.com/zendframework/zf2/issues/2988)
- 2990: Added note of new xcache storage adapter
  (https://github.com/zendframework/zf2/issues/2990)
- 3010: [2.1][File Uploads] Multi-File input filtering and FilePRG plugin update
  (https://github.com/zendframework/zf2/issues/3010)
- 3011: Response Json Client
  (https://github.com/zendframework/zf2/issues/3011)
- 3016: [develop] PRG Plugin fixes: Incorrect use of session hops expiration
  (https://github.com/zendframework/zf2/issues/3016)
- 3019: [2.1][develop] PRG Plugins fix
  (https://github.com/zendframework/zf2/issues/3019)
- 3055: Zend Validators complain of array to string conversion for nested array
  values that do not pass validation when using E\_NOTICE
  (https://github.com/zendframework/zf2/issues/3055)
- 3058: [2.1][File Upload] Session Progress fixes
  (https://github.com/zendframework/zf2/issues/3058)
- 3059: [2.1] Add reference to ChromePhp LoggerWriter in WriterPluginManager
  (https://github.com/zendframework/zf2/issues/3059)
- 3069: Hotfix/xcache empty namespace
  (https://github.com/zendframework/zf2/issues/3069)
- 3073: Documentation and code  mismatch
  (https://github.com/zendframework/zf2/issues/3073)
- 3084: Basic support for aggregates in SharedEventManager according to feedback...
  (https://github.com/zendframework/zf2/issues/3084)
- 3086: Updated constructors to accept options array according to AbstractWriter...
  (https://github.com/zendframework/zf2/issues/3086)
- 3088: Zend\Permissions\Rbac roles should inherit parent permissions, not child
  permissions
  (https://github.com/zendframework/zf2/issues/3088)
- 3093: Feature/cookies refactor
  (https://github.com/zendframework/zf2/issues/3093)
- 3105: RFC Send Response Workflow
  (https://github.com/zendframework/zf2/issues/3105)
- 3110: Stdlib\StringUtils
  (https://github.com/zendframework/zf2/issues/3110)
- 3140: Tests for Zend\Cache\Storage\Adapter\MemcachedResourceManager
  (https://github.com/zendframework/zf2/issues/3140)
- 3195: Date element formats not respected in validators.
  (https://github.com/zendframework/zf2/issues/3195)
- 3199: [2.1][FileUploads] FileInput AJAX Post fix
  (https://github.com/zendframework/zf2/issues/3199)
- 3212: Cache: Now an empty namespace means disabling namespace support
  (https://github.com/zendframework/zf2/issues/3212)
- 3215: Check $exception type before throw
  (https://github.com/zendframework/zf2/issues/3215)
- 3219: Fix hook in plugin manager
  (https://github.com/zendframework/zf2/issues/3219)
- 3224: Zend\Db\Sql\Select::getSqlString(Zend\Db\Adapter\Platform\Mysql) doesn't
  work properly with limit param
  (https://github.com/zendframework/zf2/issues/3224)
- 3243: [2.1] Added the support of Apache's passwords
  (https://github.com/zendframework/zf2/issues/3243)
- 3246: [2.1][File Upload] Change file upload filtering to preserve the $\_FILES
  array
  (https://github.com/zendframework/zf2/issues/3246)
- 3247: Fix zend test with the new sendresponselistener
  (https://github.com/zendframework/zf2/issues/3247)
- 3257: Support nested error handler
  (https://github.com/zendframework/zf2/issues/3257)
- 3259: [2.1][File Upload] RenameUpload filter rewrite w/option to use uploaded
  'name'
  (https://github.com/zendframework/zf2/issues/3259)
- 3263: correcting ConsoleResponseSender's __invoke
  (https://github.com/zendframework/zf2/issues/3263)
- 3276: DateElement now support a string
  (https://github.com/zendframework/zf2/issues/3276)
- 3283: fix Undefined function DocBlockReflection::factory error
  (https://github.com/zendframework/zf2/issues/3283)
- 3287: Added missing constructor parameter
  (https://github.com/zendframework/zf2/issues/3287)
- 3308: Update library/Zend/Validator/File/MimeType.php
  (https://github.com/zendframework/zf2/issues/3308)
- 3314: add ContentTransferEncoding Headers
  (https://github.com/zendframework/zf2/issues/3314)
- 3316: Update library/Zend/Mvc/ResponseSender/ConsoleResponseSender.php
  (https://github.com/zendframework/zf2/issues/3316)
- 3334: [2.1][develop] Added missing Exception namespace to Sha1 validator
  (https://github.com/zendframework/zf2/issues/3334)
- 3339: Xterm's 256 colors integration for Console.
  (https://github.com/zendframework/zf2/issues/3339)
- 3343: add SimpleStreamResponseSender + Tests
  (https://github.com/zendframework/zf2/issues/3343)
- 3349: Provide support for more HTTP methods in the AbstractRestfulController
  (https://github.com/zendframework/zf2/issues/3349)
- 3350: Add little more fun to console
  (https://github.com/zendframework/zf2/issues/3350)
- 3357: Add default prototype tags in reflection
  (https://github.com/zendframework/zf2/issues/3357)
- 3359: Added filter possibility
  (https://github.com/zendframework/zf2/issues/3359)
- 3363: Fix minor doc block errors
  (https://github.com/zendframework/zf2/issues/3363)
- 3365: Fix trailing spaces CS error causing all travis builds to fail
  (https://github.com/zendframework/zf2/issues/3365)
- 3366: Zend\Log\Logger::registerErrorHandler() should accept a parameter to set
  the return value of the error_handler callback 
  (https://github.com/zendframework/zf2/issues/3366)
- 3370: [2.1] File PRG plugin issue when merging POST data with nested keys
  (https://github.com/zendframework/zf2/issues/3370)
- 3376: Remove use of deprecated /e-modifier of preg_replace
  (https://github.com/zendframework/zf2/issues/3376)
- 3377: removed test failing since PHP>=5.4
  (https://github.com/zendframework/zf2/issues/3377)
- 3378: Improve code generators consistency
  (https://github.com/zendframework/zf2/issues/3378)
- 3385: render view one last time in case exception thrown from inside view
  (https://github.com/zendframework/zf2/issues/3385)
- 3389: FileExtension validor error in Form context
  (https://github.com/zendframework/zf2/issues/3389)
- 3392: Development branch of AbstractRestfulController->processPostData()
  doesn't handle Content-Type application/x-www-form-urlencoded correctly
  (https://github.com/zendframework/zf2/issues/3392)
- 3404: Provide default $_SESSION array superglobal proxy storage adapter 
  (https://github.com/zendframework/zf2/issues/3404)
- 3405: fix dispatcher to catch legitimate exceptions
  (https://github.com/zendframework/zf2/issues/3405)
- 3414: Zend\Mvc\Controller\AbstractRestfulController: various fixes to Json
  handling
  (https://github.com/zendframework/zf2/issues/3414)
- 3418: [2.1] Additional code comments for FileInput
  (https://github.com/zendframework/zf2/issues/3418)
- 3420: Authentication Validator
  (https://github.com/zendframework/zf2/issues/3420)
- 3421: Allow to set arbitrary status code for Exception strategy
  (https://github.com/zendframework/zf2/issues/3421)
- 3426: Zend\Form\View\Helper\FormSelect
  (https://github.com/zendframework/zf2/issues/3426)
- 3427: `Zend\ModuleManager\Feature\ProvidesDependencyModulesInterface`
  (https://github.com/zendframework/zf2/issues/3427)
- 3440: [#3376] Better fix
  (https://github.com/zendframework/zf2/issues/3440)
- 3442: Better content-type negotiation
  (https://github.com/zendframework/zf2/issues/3442)
- 3446: Zend\Form\Captcha setOptions don't follow interface contract
  (https://github.com/zendframework/zf2/issues/3446)
- 3450: [Session][Auth] Since the recent BC changes to Sessions,
  Zend\Authentication\Storage\Session does not work
  (https://github.com/zendframework/zf2/issues/3450)
- 3454: ACL permissions are not correctly inherited.
  (https://github.com/zendframework/zf2/issues/3454)
- 3458: Session data is empty in Session SaveHandler's write function
  (https://github.com/zendframework/zf2/issues/3458)
- 3461: fix for zendframework/zf2#3458
  (https://github.com/zendframework/zf2/issues/3461)
- 3470: Session not working in current development?
  (https://github.com/zendframework/zf2/issues/3470)
- 3479: Fixed #3454.
  (https://github.com/zendframework/zf2/issues/3479)
- 3482: Feature/rest delete replace collection
  (https://github.com/zendframework/zf2/issues/3482)
- 3483: [#2629] Add charset to Content-Type header
  (https://github.com/zendframework/zf2/issues/3483)
- 3485: Zend\Db Oracle Driver
  (https://github.com/zendframework/zf2/issues/3485)
- 3491: Update library/Zend/Code/Generator/PropertyGenerator.php
  (https://github.com/zendframework/zf2/issues/3491)
- 3493: [Log] fixes #3366: Now Logger::registerErrorHandler() accepts continue
  (https://github.com/zendframework/zf2/issues/3493)
- 3494: [2.1] Zend\Filter\Word\* no longer extends Zend\Filter\PregReplace
  (https://github.com/zendframework/zf2/issues/3494)
- 3495: [2.1] Added Zend\Stdlib\StringUtils::hasPcreUnicodeSupport()
  (https://github.com/zendframework/zf2/issues/3495)
- 3496: [2.1] fixed tons of missing/wrong use statements
  (https://github.com/zendframework/zf2/issues/3496)
- 3498: add method to Zend\Http\Response\Stream
  (https://github.com/zendframework/zf2/issues/3498)
- 3499: removed "self" typehints in Zend\Config and Zend\Mvc
  (https://github.com/zendframework/zf2/issues/3499)
- 3501: Exception while createing RuntimeException in Pdo Connection class
  (https://github.com/zendframework/zf2/issues/3501)
- 3507: hasAcl dosn't cheks $defaultAcl Member Variable
  (https://github.com/zendframework/zf2/issues/3507)
- 3508: Removed all @category, @package, and @subpackage annotations
  (https://github.com/zendframework/zf2/issues/3508)
- 3509: Zend\Form\View\Helper\FormSelect
  (https://github.com/zendframework/zf2/issues/3509)
- 3510: FilePRG: replace array_merge with ArrayUtils::merge
  (https://github.com/zendframework/zf2/issues/3510)
- 3511: Revert PR #3088 as discussed in #3265.
  (https://github.com/zendframework/zf2/issues/3511)
- 3519: Allow to pull route manager from sl
  (https://github.com/zendframework/zf2/issues/3519)
- 3523: Components dependent on Zend\Stdlib but it's not marked in composer.json
  (https://github.com/zendframework/zf2/issues/3523)
- 3531: [2.1] Fix variable Name and Resource on Oracle Driver Test
  (https://github.com/zendframework/zf2/issues/3531)
- 3532: Add legend translation support into formCollection view helper
  (https://github.com/zendframework/zf2/issues/3532)
- 3538: ElementPrepareAwareInterface should use FormInterface
  (https://github.com/zendframework/zf2/issues/3538)
- 3541: \Zend\Filter\Encrypt and \Zend\Filter\Decrypt not working together?
  (https://github.com/zendframework/zf2/issues/3541)
- 3543: Hotfix: Undeprecate PhpEnvironement Response methods
  (https://github.com/zendframework/zf2/issues/3543)
- 3545: Removing service initializer as of zendframework/zf2#3537
  (https://github.com/zendframework/zf2/issues/3545)
- 3546: Add RoleInterface
  (https://github.com/zendframework/zf2/issues/3546)
- 3555: [2.1] [Forms] [Bug] Factory instantiates Elements directly but should be
  using the FormElementManager
  (https://github.com/zendframework/zf2/issues/3555)
- 3556: fix for zendframework/zf2#3555
  (https://github.com/zendframework/zf2/issues/3556)
- 3557: [2.1] Fixes for FilePRG when using nested form elements
  (https://github.com/zendframework/zf2/issues/3557)
- 3559: Feature/translate flash message
  (https://github.com/zendframework/zf2/issues/3559)
- 3561: Zend\Mail SMTP Fix Connection Handling
  (https://github.com/zendframework/zf2/issues/3561)
- 3566: Flash Messenger fixes for PHP < 5.4, and fix for default namespacing
  (https://github.com/zendframework/zf2/issues/3566)
- 3567: Zend\Db: Adapter construction features + IbmDb2 & Oracle Platform
  features
  (https://github.com/zendframework/zf2/issues/3567)
- 3572: Allow to add serializers through config
  (https://github.com/zendframework/zf2/issues/3572)
- 3576: BC Break in Controller Loader, controllers no more present in controller
  loader.
  (https://github.com/zendframework/zf2/issues/3576)
- 3583: [2.1] Fixed an issue on salt check in Apache Password
  (https://github.com/zendframework/zf2/issues/3583)
- 3584: Zend\Db Fix for #3290
  (https://github.com/zendframework/zf2/issues/3584)
- 3585: [2.1] Added the Apache htpasswd support for HTTP Authentication
  (https://github.com/zendframework/zf2/issues/3585)
- 3586: Zend\Db Fix for #2563
  (https://github.com/zendframework/zf2/issues/3586)
- 3587: Zend\Db Fix/Feature for #3294
  (https://github.com/zendframework/zf2/issues/3587)
- 3597: Zend\Db\TableGateway hotfix for MasterSlaveFeature
  (https://github.com/zendframework/zf2/issues/3597)
- 3598: Feature Zend\Db\Adapter\Profiler
  (https://github.com/zendframework/zf2/issues/3598)
- 3599: [WIP] Zend\Db\Sql Literal Objects
  (https://github.com/zendframework/zf2/issues/3599)
- 3600: Fixed the unit test for Zend\Filter\File\Encrypt and Decrypt
  (https://github.com/zendframework/zf2/issues/3600)
- 3605: Restore Zend\File\Transfer
  (https://github.com/zendframework/zf2/issues/3605)
- 3606: Zend\Db\Sql\Select Add Support For SubSelect in Join Table - #2881 &
  #2884
  (https://github.com/zendframework/zf2/issues/3606)

### Potential Breakage

Includes a fix to the classes `Zend\Filter\Encrypt`
and `Zend\Filter\Decrypt` which may pose a small break for end-users. Each
requires an encryption key be passed to either the constructor or the
setKey() method now; this was done to improve the security of each
class.

`Zend\Session` includes a new `Zend\Session\Storage\SessionArrayStorage`
class, which acts as a direct proxy to the $_SESSION superglobal. The
SessionManager class now uses this new storage class by default, in
order to fix an error that occurs when directly manipulating nested
arrays of $_SESSION in third-party code. For most users, the change will
be seamless. Those affected will be those (a) directly accessing the
storage instance, and (b) using object notation to access session
members:

    $foo = null;
    /** @var $storage Zend\Session\Storage\SessionStorage */
    if (isset($storage->foo)) {
        $foo = $storage->foo;
    }

If you are using array notation, as in the following example, your code
remains forwards compatible:

    $foo = null;

    /** @var $storage Zend\Session\Storage\SessionStorage */
    if (isset($storage['foo'])) {
        $foo = $storage['foo'];
    }

If you are not working directly with the storage instance, you will be
unaffected.

For those affected, the following courses of action are possible:

 * Update your code to replace object property notation with array
   notation, OR
 * Initialize and register a Zend\Session\Storage\SessionStorage object
   explicitly with the session manager instance.

## 2.0.8 (13 Mar 2013):

- ZF2013-01: Query route (http://framework.zend.com/security/ZF2013-01)
- ZF2013-02: RNG support (http://framework.zend.com/security/ZF2013-02)
- ZF2013-03: DB platform quoting (http://framework.zend.com/security/ZF2013-03)

## 2.0.7 (29 Jan 2013):

- 1992: [2.1] Adding simple Zend/I18n/Loader/Tmx
  (https://github.com/zendframework/zf2/issues/1992)
- 2024: Add HydratingResultSet::toEntityArray()
  (https://github.com/zendframework/zf2/issues/2024)
- 2031: [2.1] Added MongoDB session save handler
  (https://github.com/zendframework/zf2/issues/2031)
- 2080: [2.1] Added a ChromePhp logger
  (https://github.com/zendframework/zf2/issues/2080)
- 2086: [2.1] Module class map cache
  (https://github.com/zendframework/zf2/issues/2086)
- 2100: [2.1] refresh() method in Redirect plugin
  (https://github.com/zendframework/zf2/issues/2100)
- 2105: [2.1] Feature/unidecoder
  (https://github.com/zendframework/zf2/issues/2105)
- 2106: [2.1] Class annotation scanner
  (https://github.com/zendframework/zf2/issues/2106)
- 2125: [2.1] Add hydrator wildcard and new hydrator strategy
  (https://github.com/zendframework/zf2/issues/2125)
- 2129: [2.1] Feature/overrideable di factories
  (https://github.com/zendframework/zf2/issues/2129)
- 2152: [2.1] [WIP] adding basic table view helper
  (https://github.com/zendframework/zf2/issues/2152)
- 2175: [2.1] Add DateSelect and MonthSelect elements
  (https://github.com/zendframework/zf2/issues/2175)
- 2189: [2.1] Added msgpack serializer
  (https://github.com/zendframework/zf2/issues/2189)
- 2190: [2.1] [WIP] Zend\I18n\Filter\SlugUrl - Made a filter to convert text to
  slugs
  (https://github.com/zendframework/zf2/issues/2190)
- 2208: [2.1] Update library/Zend/View/Helper/HeadScript.php
  (https://github.com/zendframework/zf2/issues/2208)
- 2212: [2.1] Feature/uri normalize filter
  (https://github.com/zendframework/zf2/issues/2212)
- 2225: Zend\Db\Sql : Create NotIn predicate
  (https://github.com/zendframework/zf2/issues/2225)
- 2232: [2.1] Load Messages from other than file
  (https://github.com/zendframework/zf2/issues/2232)
- 2271: [2.1] Ported FingersCrossed handler from monolog to ZF2
  (https://github.com/zendframework/zf2/issues/2271)
- 2288: Allow to create empty option in Select
  (https://github.com/zendframework/zf2/issues/2288)
- 2305: Add support for prev and next link relationships
  (https://github.com/zendframework/zf2/issues/2305)
- 2315: Add MVC service factories for Filters and Validators
  (https://github.com/zendframework/zf2/issues/2315)
- 2316: Add paginator factory & adapter plugin manager
  (https://github.com/zendframework/zf2/issues/2316)
- 2333: Restore mail message from string
  (https://github.com/zendframework/zf2/issues/2333)
- 2339: ZF2-530 Implement PropertyScanner
  (https://github.com/zendframework/zf2/issues/2339)
- 2343: Create Zend Server Monitor Event
  (https://github.com/zendframework/zf2/issues/2343)
- 2367: Convert abstract classes that are only offering static methods
  (https://github.com/zendframework/zf2/issues/2367)
- 2374: Modified Acl/Navigation to be extendable
  (https://github.com/zendframework/zf2/issues/2374)
- 2381: Method Select::from can accept instance of Select as subselect
  (https://github.com/zendframework/zf2/issues/2381)
- 2389: Add plural view helper
  (https://github.com/zendframework/zf2/issues/2389)
- 2396: Rbac component for ZF2
  (https://github.com/zendframework/zf2/issues/2396)
- 2399: Feature/unidecoder new
  (https://github.com/zendframework/zf2/issues/2399)
- 2411: Allow to specify custom pattern for date
  (https://github.com/zendframework/zf2/issues/2411)
- 2414: Added a new validator to check if input is instance of certain class
  (https://github.com/zendframework/zf2/issues/2414)
- 2415: Add plural helper to I18n
  (https://github.com/zendframework/zf2/issues/2415)
- 2417: Allow to render template separately
  (https://github.com/zendframework/zf2/issues/2417)
- 2648: AbstractPluginManager should not respond to...
  (https://github.com/zendframework/zf2/issues/2648)
- 2650: Add view helper and controller plugin to pull the current identity from ...
  (https://github.com/zendframework/zf2/issues/2650)
- 2670: quoteIdentifier() & quoteIdentifierChain() bug
  (https://github.com/zendframework/zf2/issues/2670)
- 2702: Added addUse method in ClassGenerator
  (https://github.com/zendframework/zf2/issues/2702)
- 2704: Functionality/writer plugin manager
  (https://github.com/zendframework/zf2/issues/2704)
- 2706: Feature ini adapter translate
  (https://github.com/zendframework/zf2/issues/2706)
- 2718: Chain authentication storage
  (https://github.com/zendframework/zf2/issues/2718)
- 2774: Fixes #2745 (generate proper query strings).
  (https://github.com/zendframework/zf2/issues/2774)
- 2783: Added methods to allow access to the routes of the SimpleRouteStack.
  (https://github.com/zendframework/zf2/issues/2783)
- 2794: Feature test phpunit lib
  (https://github.com/zendframework/zf2/issues/2794)
- 2801: Improve Zend\Code\Scanner\TokenArrayScanner
  (https://github.com/zendframework/zf2/issues/2801)
- 2807: Add buffer handling to HydratingResultSet
  (https://github.com/zendframework/zf2/issues/2807)
- 2809: Allow Zend\Db\Sql\TableIdentifier in Zend\Db\Sql\Insert, Update & Delete
  (https://github.com/zendframework/zf2/issues/2809)
- 2812: Catch exceptions thrown during rendering
  (https://github.com/zendframework/zf2/issues/2812)
- 2821: Added loadModule.post event to loadModule().
  (https://github.com/zendframework/zf2/issues/2821)
- 2822: Added the ability for FirePhp to understand 'extras' passed to \Zend\Log
  (https://github.com/zendframework/zf2/issues/2822)
- 2841: Allow to remove attribute in form element
  (https://github.com/zendframework/zf2/issues/2841)
- 2844: [Server] & [Soap] Typos and docblocks
  (https://github.com/zendframework/zf2/issues/2844)
- 2848: fixing extract behavior of Zend\Form\Element\Collection and added
  ability to use own fieldset helper within FormCollection-helper
  (https://github.com/zendframework/zf2/issues/2848)
- 2855: add a view event
  (https://github.com/zendframework/zf2/issues/2855)
- 2868: [WIP][Server] Rewrite Reflection API to reuse code from
  Zend\Code\Reflection API
  (https://github.com/zendframework/zf2/issues/2868)
- 2870: [Code] Add support for @throws, multiple types and typed arrays
  (https://github.com/zendframework/zf2/issues/2870)
- 2875: [InputFilter] Adding hasUnknown and getUnknown methods to detect and get
  unknown inputs
  (https://github.com/zendframework/zf2/issues/2875)
- 2919: Select::where should accept PredicateInterface
  (https://github.com/zendframework/zf2/issues/2919)
- 2927: Add a bunch of traits to ZF2
  (https://github.com/zendframework/zf2/issues/2927)
- 2931: Cache: Now an empty namespace means disabling namespace support
  (https://github.com/zendframework/zf2/issues/2931)
- 2953: [WIP] #2743 fix docblock @category/@package/@subpackage
  (https://github.com/zendframework/zf2/issues/2953)
- 2989: Decouple Zend\Db\Sql from concrete Zend\Db\Adapter implementations
  (https://github.com/zendframework/zf2/issues/2989)
- 2995: service proxies / lazy services
  (https://github.com/zendframework/zf2/issues/2995)
- 3017: Fixing the problem with order and \Zend\Db\Sql\Expression
  (https://github.com/zendframework/zf2/issues/3017)
- 3028: Added Json support for POST and PUT operations in restful controller.
  (https://github.com/zendframework/zf2/issues/3028)
- 3056: Add pattern & storage cache factory
  (https://github.com/zendframework/zf2/issues/3056)
- 3057: Pull zend filter compress snappy
  (https://github.com/zendframework/zf2/issues/3057)
- 3078: Allow NodeList to be accessed via array like syntax.
  (https://github.com/zendframework/zf2/issues/3078)
- 3081: Fix for Collection extract method updates targetElement object
  (https://github.com/zendframework/zf2/issues/3081)
- 3106: Added template map generator
  (https://github.com/zendframework/zf2/issues/3106)
- 3189: Added xterm's 256 colors
  (https://github.com/zendframework/zf2/issues/3189)
- 3200: Added ValidatorChain::attach() and ValidatorChain::attachByName() to
  keep consistent with FilterChain
  (https://github.com/zendframework/zf2/issues/3200)
- 3202: Added NTLM authentication support to Zend\Soap\Client\DotNet.
  (https://github.com/zendframework/zf2/issues/3202)
- 3218: Zend-Form: Allow Input Filter Preference Over Element Defaults
  (https://github.com/zendframework/zf2/issues/3218)
- 3230: Add Zend\Stdlib\Hydrator\Strategy\ClosureStrategy
  (https://github.com/zendframework/zf2/issues/3230)
- 3241: Reflection parameter type check
  (https://github.com/zendframework/zf2/issues/3241)
- 3260: Zend/Di, retriving same shared instance for different extra parameters
  (https://github.com/zendframework/zf2/issues/3260)
- 3261: Fix sendmail key
  (https://github.com/zendframework/zf2/issues/3261)
- 3262:  Allows several translation files for same domain / locale 
  (https://github.com/zendframework/zf2/issues/3262)
- 3269: A fix for issue #3195. Date formats are now used during validation.
  (https://github.com/zendframework/zf2/issues/3269)
- 3272: Support for internationalized .IT domain names
  (https://github.com/zendframework/zf2/issues/3272)
- 3273: Parse docblock indented with tabs
  (https://github.com/zendframework/zf2/issues/3273)
- 3285: Fixed wrong return usage and added @throws docblock
  (https://github.com/zendframework/zf2/issues/3285)
- 3286: remove else in already return early
  (https://github.com/zendframework/zf2/issues/3286)
- 3288: Removed unused variable
  (https://github.com/zendframework/zf2/issues/3288)
- 3292: Added Zend Monitor custom event support
  (https://github.com/zendframework/zf2/issues/3292)
- 3295: Proposing removal of subscription record upon unsubscribe
  (https://github.com/zendframework/zf2/issues/3295)
- 3296: Hotfix #3046 - set /dev/urandom as entropy file for Session
  (https://github.com/zendframework/zf2/issues/3296)
- 3298: Add PROPFIND Method to Zend/HTTP/Request
  (https://github.com/zendframework/zf2/issues/3298)
- 3300: Zend\Config - Fix count after merge
  (https://github.com/zendframework/zf2/issues/3300)
- 3302: Fixed #3282
  (https://github.com/zendframework/zf2/issues/3302)
- 3303: Fix indentation, add trailing ',' to last element in array
  (https://github.com/zendframework/zf2/issues/3303)
- 3304: Missed the Zend\Text dependency for Zend\Mvc in composer.json
  (https://github.com/zendframework/zf2/issues/3304)
- 3307: Fix an issue with inheritance of placeholder registry
  (https://github.com/zendframework/zf2/issues/3307)
- 3313: Fix buffering getTotalSpace
  (https://github.com/zendframework/zf2/issues/3313)
- 3317: Fixed FileGenerator::setUse() to ignore already added uses.
  (https://github.com/zendframework/zf2/issues/3317)
- 3318: Fixed FileGenerator::setUses() to allow passing in array of strings.
  (https://github.com/zendframework/zf2/issues/3318)
- 3320: Change @copyright Year : 2012 with 2013
  (https://github.com/zendframework/zf2/issues/3320)
- 3321: remove relative link in CONTRIBUTING.md
  (https://github.com/zendframework/zf2/issues/3321)
- 3322: remove copy variable for no reason
  (https://github.com/zendframework/zf2/issues/3322)
- 3324: enhance strlen to improve performance
  (https://github.com/zendframework/zf2/issues/3324)
- 3326: Minor loop improvements
  (https://github.com/zendframework/zf2/issues/3326)
- 3327: Fix indentation
  (https://github.com/zendframework/zf2/issues/3327)
- 3328: pass on the configured format to the DateValidator instead of hardcoding it
  (https://github.com/zendframework/zf2/issues/3328)
- 3329: Fixed DefinitionList::hasMethod()
  (https://github.com/zendframework/zf2/issues/3329)
- 3331: no chaining in form class' bind method
  (https://github.com/zendframework/zf2/issues/3331)
- 3333: Fixed Zend/Mvc/Router/Http/Segment
  (https://github.com/zendframework/zf2/issues/3333)
- 3340: Add root namespace character
  (https://github.com/zendframework/zf2/issues/3340)
- 3342: change boolean to bool for consistency
  (https://github.com/zendframework/zf2/issues/3342)
- 3345: Update library/Zend/Form/View/Helper/FormRow.php
  (https://github.com/zendframework/zf2/issues/3345)
- 3352: ClassMethods hydrator and wrong method definition
  (https://github.com/zendframework/zf2/issues/3352)
- 3355: Fix for GitHub issue 2511
  (https://github.com/zendframework/zf2/issues/3355)
- 3356: ZF session validators
  (https://github.com/zendframework/zf2/issues/3356)
- 3362: Use CamelCase for naming
  (https://github.com/zendframework/zf2/issues/3362)
- 3369: Removed unused variable in Zend\Json\Decoder.php
  (https://github.com/zendframework/zf2/issues/3369)
- 3386: Adding attributes for a lightweight export
  (https://github.com/zendframework/zf2/issues/3386)
- 3393: [Router] no need to correct ~ in the path encoding
  (https://github.com/zendframework/zf2/issues/3393)
- 3396: change minimal verson of PHPUnit
  (https://github.com/zendframework/zf2/issues/3396)
- 3403: [ZF-8825] Lower-case lookup for "authorization" header
  (https://github.com/zendframework/zf2/issues/3403)
- 3409: Fix for broken handling of
  Zend\ServiceManager\ServiceManager::shareByDefault = false (Issue #3408)
  (https://github.com/zendframework/zf2/issues/3409)
- 3410: [composer] Sync replace package list
  (https://github.com/zendframework/zf2/issues/3410)
- 3415: Remove import of Zend root namespace
  (https://github.com/zendframework/zf2/issues/3415)
- 3423: Issue #3348 fix
  (https://github.com/zendframework/zf2/issues/3423)
- 3425: German Resources Zend\_Validate.php updated.
  (https://github.com/zendframework/zf2/issues/3425)
- 3429: Add __destruct to SessionManager
  (https://github.com/zendframework/zf2/issues/3429)
- 3430: SessionManager: Throw exception when attempting to setId after the
  session has been started
  (https://github.com/zendframework/zf2/issues/3430)
- 3437: Feature/datetime factory format
  (https://github.com/zendframework/zf2/issues/3437)
- 3438: Add @method tags to the AbstractController
  (https://github.com/zendframework/zf2/issues/3438)
- 3439: Individual shared setting does not override the shareByDefault setting
  of the ServiceManager
  (https://github.com/zendframework/zf2/issues/3439)
- 3443: Adding logic to check module dependencies at module loading time
  (https://github.com/zendframework/zf2/issues/3443)
- 3445: Update library/Zend/Validator/Hostname.php
  (https://github.com/zendframework/zf2/issues/3445)
- 3452: Hotfix/session mutability
  (https://github.com/zendframework/zf2/issues/3452)
- 3473: remove surplus call deep namespace
  (https://github.com/zendframework/zf2/issues/3473)
- 3477: The display_exceptions config-option is not passed to 404 views.
  (https://github.com/zendframework/zf2/issues/3477)
- 3480: [Validator][#2538] hostname validator overwrite 
  (https://github.com/zendframework/zf2/issues/3480)
- 3484: [#3055] Remove array to string conversion notice
  (https://github.com/zendframework/zf2/issues/3484)
- 3486: [#3073] Define filter() in Decompress filter
  (https://github.com/zendframework/zf2/issues/3486)
- 3487: [#3446] Allow generic traversable configuration to Captcha element
  (https://github.com/zendframework/zf2/issues/3487)
- 3492: Hotfix/random crypt test fail
  (https://github.com/zendframework/zf2/issues/3492)
- 3502: Features/port supermessenger
  (https://github.com/zendframework/zf2/issues/3502)
- 3513: Fixed bug in acl introduced by acca10b6abe74b3ab51890d5cbe0ab8da4fdf7e0
  (https://github.com/zendframework/zf2/issues/3513)
- 3520: Replace all is_null($value) calls with null === $value
  (https://github.com/zendframework/zf2/issues/3520)
- 3527: Explode validator: allow any value type to be validated
  (https://github.com/zendframework/zf2/issues/3527)
- 3530: The hasACL and hasRole don't check their default member variables
  (https://github.com/zendframework/zf2/issues/3530)
- 3550: Fix for the issue #3541 - salt size for Encrypt/Decrypt Filter
  (https://github.com/zendframework/zf2/issues/3550)
- 3562: Fix: Calling count() results in infinite loop
  (https://github.com/zendframework/zf2/issues/3562)
- 3563: Zend\Db: Fix for #3523 changeset - composer.json and stdlib
  (https://github.com/zendframework/zf2/issues/3563)
- 3571: Correctly parse empty Subject header
  (https://github.com/zendframework/zf2/issues/3571)
- 3575: Fix name of plugin referred to in exception message
  (https://github.com/zendframework/zf2/issues/3575)
- 3579: Some minor fixes in \Zend\View\Helper\HeadScript() class
  (https://github.com/zendframework/zf2/issues/3579)
- 3593: \Zend\Json\Server Fix _getDefaultParams if request-params are an
  associative array
  (https://github.com/zendframework/zf2/issues/3593)
- 3594: Added contstructor to suppressfilter
  (https://github.com/zendframework/zf2/issues/3594)
- 3601: Update Travis to start running tests on PHP 5.5
  (https://github.com/zendframework/zf2/issues/3601)
- 3604: fixed Zend\Log\Logger::registerErrorHandler() doesn't log previous
  exceptions 
  (https://github.com/zendframework/zf2/issues/3604)

### Potential Breakage

Includes a fix to the classes `Zend\Filter\Encrypt`
and `Zend\Filter\Decrypt` which may pose a small break for end-users. Each
requires an encryption key be passed to either the constructor or the
setKey() method now; this was done to improve the security of each
class.

## 2.0.6 (19 Dec 2012):

- 2885: Zend\Db\TableGateway\AbstractTableGateway won't work with Sqlsrv
  db adapter (https://github.com/zendframework/zf2/issues/2885)
- 2922: Fix #2902 (https://github.com/zendframework/zf2/issues/2922)
- 2961: Revert PR #2943 for 5.3.3 fix
  (https://github.com/zendframework/zf2/issues/2961)
- 2962: Allow Accept-Encoding header to be set explicitly by http
  request (https://github.com/zendframework/zf2/issues/2962)
- 3033: Fix error checking on Zend\Http\Client\Adapter\Socket->write().
  (https://github.com/zendframework/zf2/issues/3033)
- 3040: remove unused 'use DOMXPath' and property $count and $xpath
  (https://github.com/zendframework/zf2/issues/3040)
- 3043: improve conditional : reduce file size
  (https://github.com/zendframework/zf2/issues/3043)
- 3044: Extending Zend\Mvc\Router\Http\Segment causes error
  (https://github.com/zendframework/zf2/issues/3044)
- 3047: Fix Zend\Console\Getopt::getUsageMessage()
  (https://github.com/zendframework/zf2/issues/3047)
- 3049: Hotfix/issue #3033
  (https://github.com/zendframework/zf2/issues/3049)
- 3050: Fix : The annotation @\Zend\Form\Annotation\AllowEmpty declared
  on does not accept any values
  (https://github.com/zendframework/zf2/issues/3050)
- 3052: Fixed #3051 (https://github.com/zendframework/zf2/issues/3052)
- 3061: changed it back 'consist' => the 'must' should be applied to all
  parts of the sentence
  (https://github.com/zendframework/zf2/issues/3061)
- 3063: hotfix: change sha382 to sha384 in
  Zend\Crypt\Key\Derivation\SaltedS2k
  (https://github.com/zendframework/zf2/issues/3063)
- 3070: Fix default value unavailable exception for in-build php classes
  (https://github.com/zendframework/zf2/issues/3070)
- 3074: Hotfix/issue #2451 (https://github.com/zendframework/zf2/issues/3074)
- 3091: console exception strategy displays previous exception message
  (https://github.com/zendframework/zf2/issues/3091)
- 3114: Fixed Client to allow also empty passwords in HTTP
  Authentication. (https://github.com/zendframework/zf2/issues/3114)
- 3125: #2607 - Fixing how headers are accessed
  (https://github.com/zendframework/zf2/issues/3125)
- 3126: Fix for GitHub issue 2605
  (https://github.com/zendframework/zf2/issues/3126)
- 3127: fix cs: add space after casting
  (https://github.com/zendframework/zf2/issues/3127)
- 3130: Obey PSR-2 (https://github.com/zendframework/zf2/issues/3130)
- 3144: Zend\Form\View\Helper\Captcha\AbstractWord input and hidden
  attributes (https://github.com/zendframework/zf2/issues/3144)
- 3148: Fixing obsolete method of checking headers, made it use the new
  method. (https://github.com/zendframework/zf2/issues/3148)
- 3149: Zf2634 - Adding missing method Client::encodeAuthHeader
  (https://github.com/zendframework/zf2/issues/3149)
- 3151: Rename variable to what it probably should be
  (https://github.com/zendframework/zf2/issues/3151)
- 3155: strip duplicated semicolon
  (https://github.com/zendframework/zf2/issues/3155)
- 3156: fix typos in docblocks
  (https://github.com/zendframework/zf2/issues/3156)
- 3162: Allow Forms to have an InputFilterSpecification
  (https://github.com/zendframework/zf2/issues/3162)
- 3163: Added support of driver\_options to Mysqli DB Driver
  (https://github.com/zendframework/zf2/issues/3163)
- 3164: Cast $step to float in \Zend\Validator\Step
  (https://github.com/zendframework/zf2/issues/3164)
- 3166: [#2678] Sqlsrv driver incorrectly throwing exception when
  $sqlOrResource... (https://github.com/zendframework/zf2/issues/3166)
- 3167: Fix #3161 by checking if the server port already exists in the
  host (https://github.com/zendframework/zf2/issues/3167)
- 3169: Fixing issue #3036 (https://github.com/zendframework/zf2/issues/3169)
- 3170: Fixing issue #2554 (https://github.com/zendframework/zf2/issues/3170)
- 3171: hotfix : add  '$argName' as 'argument %s' in sprintf ( at 1st
  parameter ) (https://github.com/zendframework/zf2/issues/3171)
- 3178: Maintain priority flag when cloning a Fieldset
  (https://github.com/zendframework/zf2/issues/3178)
- 3184: fix misspelled getCacheStorge()
  (https://github.com/zendframework/zf2/issues/3184)
- 3186: Dispatching to a good controller but wrong action triggers a
  Fatal Error (https://github.com/zendframework/zf2/issues/3186)
- 3187: Fixing ansiColorMap by removing extra m's showed in the console
  (https://github.com/zendframework/zf2/issues/3187)
- 3194: Write clean new line for writeLine method (no background color)
  (https://github.com/zendframework/zf2/issues/3194)
- 3197: Fix spelling error (https://github.com/zendframework/zf2/issues/3197)
- 3201: Session storage set save path
  (https://github.com/zendframework/zf2/issues/3201)
- 3204: [wip] Zend\Http\Client makes 2 requests to url if
  setStream(true) is called
  (https://github.com/zendframework/zf2/issues/3204)
- 3207: dead code clean up.
  (https://github.com/zendframework/zf2/issues/3207)
- 3208: Zend\Mime\Part: Added EOL paramter to getEncodedStream()
  (https://github.com/zendframework/zf2/issues/3208)
- 3213: [#3173] Incorrect creating instance
  Zend/Code/Generator/ClassGenerator.php by fromArray
  (https://github.com/zendframework/zf2/issues/3213)
- 3214: Fix passing of tags to constructor of docblock generator class
  (https://github.com/zendframework/zf2/issues/3214)
- 3217: Cache: Optimized Filesystem::setItem with locking enabled by
  writing the... (https://github.com/zendframework/zf2/issues/3217)
- 3220: [2.0] Log Writer support for MongoClient driver class
  (https://github.com/zendframework/zf2/issues/3220)
- 3226: Licence is not accessable via web
  (https://github.com/zendframework/zf2/issues/3226)
- 3229: fixed bug in DefinitionList::hasMethod()
  (https://github.com/zendframework/zf2/issues/3229)
- 3234: Removed old Form TODO since all items are complete
  (https://github.com/zendframework/zf2/issues/3234)
- 3236: Issue #3222 - Added suport for multi-level nested ini config
  variables (https://github.com/zendframework/zf2/issues/3236)
- 3237: [BUG] Service Manager Not Shared Duplicate new Instance with
  multiple Abstract Factories
  (https://github.com/zendframework/zf2/issues/3237)
- 3238: Added French translation for captcha
  (https://github.com/zendframework/zf2/issues/3238)
- 3250: Issue #2912 - Fix for LicenseTag generation
  (https://github.com/zendframework/zf2/issues/3250)
- 3252: subject prepend text in options for Log\Writer\Mail
  (https://github.com/zendframework/zf2/issues/3252)
- 3254: Better capabilities surrounding console notFoundAction
  (https://github.com/zendframework/zf2/issues/3254)


## 2.0.5 (29 Nov 2012):

- 3004: Zend\Db unit tests fail with code coverage enabled
  (https://github.com/zendframework/zf2/issues/3004)
- 3039: combine double if into single conditional
  (https://github.com/zendframework/zf2/issues/3039)
- 3042: fix typo 'consist of' should be 'consists of' in singular
  (https://github.com/zendframework/zf2/issues/3042)
- 3045: Reduced the #calls of rawurlencode() using a cache mechanism
  (https://github.com/zendframework/zf2/issues/3045)
- 3048: Applying quickfix for zendframework/zf2#3004
  (https://github.com/zendframework/zf2/issues/3048)
- 3095: Process X-Forwarded-For header in correct order
  (https://github.com/zendframework/zf2/issues/3095)

## 2.0.4 (20 Nov 2012):

- 2808: Add serializer better inheritance and extension
  (https://github.com/zendframework/zf2/issues/2808)
- 2813: Add test on canonical name with the ServiceManager
  (https://github.com/zendframework/zf2/issues/2813)
- 2832: bugfix: The helper DateFormat does not cache correctly when a pattern is
  set. (https://github.com/zendframework/zf2/issues/2832)
- 2837: Add empty option before empty check
  (https://github.com/zendframework/zf2/issues/2837)
- 2843: change self:: with static:: in call-ing static property/method
  (https://github.com/zendframework/zf2/issues/2843)
- 2857: Unnecessary path assembly on return in
  Zend\Mvc\Router\Http\TreeRouteStack->assemble() line 236
  (https://github.com/zendframework/zf2/issues/2857)
- 2867: Enable view sub-directories when using ModuleRouteListener
  (https://github.com/zendframework/zf2/issues/2867)
- 2872: Resolve naming conflicts in foreach statements
  (https://github.com/zendframework/zf2/issues/2872)
- 2878: Fix : change self:: with static:: in call-ing static property/method()
  in other components ( all ) (https://github.com/zendframework/zf2/issues/2878)
- 2879: remove unused const in Zend\Barcode\Barcode.php
  (https://github.com/zendframework/zf2/issues/2879)
- 2896: Constraints in Zend\Db\Metadata\Source\AbstractSource::getTable not
  initalised (https://github.com/zendframework/zf2/issues/2896)
- 2907: Fixed proxy adapter keys being incorrectly set due Zend\Http\Client
  (https://github.com/zendframework/zf2/issues/2907)
- 2909: Change format of Form element DateTime and DateTimeLocal
  (https://github.com/zendframework/zf2/issues/2909)
- 2921: Added Chinese translations for zf2 validate/captcha resources
  (https://github.com/zendframework/zf2/issues/2921)
- 2924: small speed-up of Zend\EventManager\EventManager::triggerListeners()
  (https://github.com/zendframework/zf2/issues/2924)
- 2929: SetCookie::getFieldValue() always uses urlencode() for cookie values,
  even in case they are already encoded
  (https://github.com/zendframework/zf2/issues/2929)
- 2930: Add minor test coverage to MvcEvent
  (https://github.com/zendframework/zf2/issues/2930)
- 2932: Sessions: SessionConfig does not allow setting non-directory save path
  (https://github.com/zendframework/zf2/issues/2932)
- 2937: preserve matched route name within route match instance while
  forwarding... (https://github.com/zendframework/zf2/issues/2937)
- 2940: change 'Cloud\Decorator\Tag' to 'Cloud\Decorator\AbstractTag'
  (https://github.com/zendframework/zf2/issues/2940)
- 2941: Logical operator fix : 'or' change to '||' and 'and' change to '&&'
  (https://github.com/zendframework/zf2/issues/2941)
- 2952: Various Zend\Mvc\Router\Http routers turn + into a space in path
  segments (https://github.com/zendframework/zf2/issues/2952)
- 2957: Make Partial proxy to view render function
  (https://github.com/zendframework/zf2/issues/2957)
- 2971: Zend\Http\Cookie undefined self::CONTEXT_REQUEST
  (https://github.com/zendframework/zf2/issues/2971)
- 2976: Fix for #2541 (https://github.com/zendframework/zf2/issues/2976)
- 2981: Controller action HttpResponse is not used by SendResponseListener
  (https://github.com/zendframework/zf2/issues/2981)
- 2983: replaced all calls to $this->xpath with $this->getXpath() to always
  have... (https://github.com/zendframework/zf2/issues/2983)
- 2986: Add class to file missing a class (fixes #2789)
  (https://github.com/zendframework/zf2/issues/2986)
- 2987: fixed Zend\Session\Container::exchangeArray
  (https://github.com/zendframework/zf2/issues/2987)
- 2994: Fixes #2993 - Add missing asterisk to method docblock
  (https://github.com/zendframework/zf2/issues/2994)
- 2997: Fixing abstract factory instantiation time
  (https://github.com/zendframework/zf2/issues/2997)
- 2999: Fix for GitHub issue 2579
  (https://github.com/zendframework/zf2/issues/2999)
- 3002: update master's resources/ja Zend_Validate.php message
  (https://github.com/zendframework/zf2/issues/3002)
- 3003: Adding tests for zendframework/zf2#2593
  (https://github.com/zendframework/zf2/issues/3003)
- 3006: Hotfix for #2497 (https://github.com/zendframework/zf2/issues/3006)
- 3007: Fix for issue 3001 Zend\Db\Sql\Predicate\Between fails with min and max
  ... (https://github.com/zendframework/zf2/issues/3007)
- 3008: Hotfix for #2482 (https://github.com/zendframework/zf2/issues/3008)
- 3009: Hotfix for #2451 (https://github.com/zendframework/zf2/issues/3009)
- 3013: Solved Issue 2857 (https://github.com/zendframework/zf2/issues/3013)
- 3025: Removing the separator between the hidden and the visible inputs. As
  the... (https://github.com/zendframework/zf2/issues/3025)
- 3027: Reduced #calls of plugin() in PhpRenderer using a cache mechanism
  (https://github.com/zendframework/zf2/issues/3027)
- 3029: Fixed the pre-commit script, missed the fix command
  (https://github.com/zendframework/zf2/issues/3029)
- 3030: Mark module as loaded before trigginer EVENT_LOAD_MODULE
  (https://github.com/zendframework/zf2/issues/3030)
- 3031: Zend\Db\Sql Fix for Insert's Merge and Set capabilities with simlar keys
  (https://github.com/zendframework/zf2/issues/3031)


## 2.0.3 (17 Oct 2012):

- 2244: Fix for issue ZF2-503 (https://github.com/zendframework/zf2/issues/2244)
- 2318: Allow to remove decimals in CurrencyFormat
  (https://github.com/zendframework/zf2/issues/2318)
- 2363: Hotfix db features with eventfeature
  (https://github.com/zendframework/zf2/issues/2363)
- 2380: ZF2-482 Attempt to fix the buffer. Also added extra unit tests.
  (https://github.com/zendframework/zf2/issues/2380)
- 2392: Update library/Zend/Db/Adapter/Platform/Mysql.php
  (https://github.com/zendframework/zf2/issues/2392)
- 2395: Fix for http://framework.zend.com/issues/browse/ZF2-571
  (https://github.com/zendframework/zf2/issues/2395)
- 2397: Memcached option merge issuse
  (https://github.com/zendframework/zf2/issues/2397)
- 2402: Adding missing dependencies
  (https://github.com/zendframework/zf2/issues/2402)
- 2404: Fix to comments (https://github.com/zendframework/zf2/issues/2404)
- 2416: Fix expressionParamIndex for AbstractSql
  (https://github.com/zendframework/zf2/issues/2416)
- 2420: Zend\Db\Sql\Select: Fixed issue with join expression named parameters
  overlapping. (https://github.com/zendframework/zf2/issues/2420)
- 2421: Update library/Zend/Http/Header/SetCookie.php
  (https://github.com/zendframework/zf2/issues/2421)
- 2422: fix add 2 space after @param in Zend\Loader
  (https://github.com/zendframework/zf2/issues/2422)
- 2423: ManagerInterface must be interface, remove 'interface' description
  (https://github.com/zendframework/zf2/issues/2423)
- 2425: Use built-in Travis composer
  (https://github.com/zendframework/zf2/issues/2425)
- 2426: Remove need of setter in ClassMethods hydrator
  (https://github.com/zendframework/zf2/issues/2426)
- 2432: Prevent space before end of tag with HTML5 doctype
  (https://github.com/zendframework/zf2/issues/2432)
- 2433: fix for setJsonpCallback not called when recieved JsonModel + test
  (https://github.com/zendframework/zf2/issues/2433)
- 2434: added phpdoc in Zend\Db
  (https://github.com/zendframework/zf2/issues/2434)
- 2437: Hotfix/console 404 reporting
  (https://github.com/zendframework/zf2/issues/2437)
- 2438: Improved previous fix for ZF2-558.
  (https://github.com/zendframework/zf2/issues/2438)
- 2440: Turkish Translations for Captcha and Validate
  (https://github.com/zendframework/zf2/issues/2440)
- 2441: Allow form collection to have any helper
  (https://github.com/zendframework/zf2/issues/2441)
- 2516: limit(20) -> generates LIMIT '20' and throws an IllegalQueryException
  (https://github.com/zendframework/zf2/issues/2516)
- 2545: getSqlStringForSqlObject() returns an invalid SQL statement with LIMIT
  and OFFSET clauses (https://github.com/zendframework/zf2/issues/2545)
- 2595: Pgsql adapater has codes related to MySQL
  (https://github.com/zendframework/zf2/issues/2595)
- 2613: Prevent password to be rendered if form validation fails
  (https://github.com/zendframework/zf2/issues/2613)
- 2617: Fixed Zend\Validator\Iban class name
  (https://github.com/zendframework/zf2/issues/2617)
- 2619: Form enctype fix when File elements are within a collection
  (https://github.com/zendframework/zf2/issues/2619)
- 2620: InputFilter/Input when merging was not using raw value
  (https://github.com/zendframework/zf2/issues/2620)
- 2622: Added ability to specify port
  (https://github.com/zendframework/zf2/issues/2622)
- 2624: Form's default input filters added multiple times
  (https://github.com/zendframework/zf2/issues/2624)
- 2630: fix relative link ( remove the relative links ) in README.md
  (https://github.com/zendframework/zf2/issues/2630)
- 2631: Update library/Zend/Loader/AutoloaderFactory.php
  (https://github.com/zendframework/zf2/issues/2631)
- 2633: fix redundance errors "The input does not appear to be a valid date"
  show twice (https://github.com/zendframework/zf2/issues/2633)
- 2635: Fix potential issue with Sitemap test
  (https://github.com/zendframework/zf2/issues/2635)
- 2636: add isset checks around timeout and maxredirects
  (https://github.com/zendframework/zf2/issues/2636)
- 2641: hotfix : formRow() element error multi-checkbox and radio renderError
  not shown (https://github.com/zendframework/zf2/issues/2641)
- 2642: Fix Travis build for CS issue
  (https://github.com/zendframework/zf2/issues/2642)
- 2643: fix for setJsonpCallback not called when recieved JsonModel + test
  (https://github.com/zendframework/zf2/issues/2643)
- 2644: Add fluidity to the prepare() function for a form
  (https://github.com/zendframework/zf2/issues/2644)
- 2652: Zucchi/filter tweaks (https://github.com/zendframework/zf2/issues/2652)
- 2665: pdftest fix (https://github.com/zendframework/zf2/issues/2665)
- 2666: fixed url change (https://github.com/zendframework/zf2/issues/2666)
- 2667: Possible fix for rartests
  (https://github.com/zendframework/zf2/issues/2667)
- 2669: skip whem gmp is loaded
  (https://github.com/zendframework/zf2/issues/2669)
- 2673: Input fallback value option
  (https://github.com/zendframework/zf2/issues/2673)
- 2676: mysqli::close() never called
  (https://github.com/zendframework/zf2/issues/2676)
- 2677: added phpdoc to Zend\Stdlib
  (https://github.com/zendframework/zf2/issues/2677)
- 2678: Zend\Db\Adapter\Sqlsrv\Sqlsrv never calls Statement\initialize() (fix
  within) (https://github.com/zendframework/zf2/issues/2678)
- 2679: Zend/Log/Logger.php using incorrect php errorLevel
  (https://github.com/zendframework/zf2/issues/2679)
- 2680: Cache: fixed bug on getTotalSpace of filesystem and dba adapter
  (https://github.com/zendframework/zf2/issues/2680)
- 2681: Cache/Dba: fixed notices on tearDown db4 tests
  (https://github.com/zendframework/zf2/issues/2681)
- 2682: Replace 'Configuration' with 'Config' when retrieving configuration
  (https://github.com/zendframework/zf2/issues/2682)
- 2683: Hotfix: Allow items from Abstract Factories to have setShared() called
  (https://github.com/zendframework/zf2/issues/2683)
- 2685: Remove unused Uses (https://github.com/zendframework/zf2/issues/2685)
- 2686: Adding code to allow EventManager trigger listeners using wildcard
  identifier (https://github.com/zendframework/zf2/issues/2686)
- 2687: Hotfix/db sql nested expressions
  (https://github.com/zendframework/zf2/issues/2687)
- 2688: Hotfix/tablegateway event feature
  (https://github.com/zendframework/zf2/issues/2688)
- 2689: Hotfix/composer phpunit
  (https://github.com/zendframework/zf2/issues/2689)
- 2690: Use RFC-3339 full-date format (Y-m-d) in Date element
  (https://github.com/zendframework/zf2/issues/2690)
- 2691: join on conditions don't accept alternatives to columns
  (https://github.com/zendframework/zf2/issues/2691)
- 2693: Update library/Zend/Db/Adapter/Driver/Mysqli/Connection.php
  (https://github.com/zendframework/zf2/issues/2693)
- 2694: Bring fluid interface to Feed Writer
  (https://github.com/zendframework/zf2/issues/2694)
- 2698: fix typo in # should be :: in exception
  (https://github.com/zendframework/zf2/issues/2698)
- 2699: fix elseif in javascript Upload Demo
  (https://github.com/zendframework/zf2/issues/2699)
- 2700: fix cs in casting variable
  (https://github.com/zendframework/zf2/issues/2700)
- 2705: Fix french translation
  (https://github.com/zendframework/zf2/issues/2705)
- 2707: Improved error message when ServiceManager does not find an invokable
  class (https://github.com/zendframework/zf2/issues/2707)
- 2710: #2461 - correcting the url encoding of path segments
  (https://github.com/zendframework/zf2/issues/2710)
- 2711: Fix/demos ProgressBar/ZendForm.php : Object of class Zend\Form\Form
  could not be converted to string
  (https://github.com/zendframework/zf2/issues/2711)
- 2712: fix cs casting variable for (array)
  (https://github.com/zendframework/zf2/issues/2712)
- 2713: Update library/Zend/Mvc/Service/ViewHelperManagerFactory.php
  (https://github.com/zendframework/zf2/issues/2713)
- 2714: Don't add separator if not prefixing columns
  (https://github.com/zendframework/zf2/issues/2714)
- 2717: Extends when it can : Validator\DateStep extends Validator\Date to
  reduce code redundancy (https://github.com/zendframework/zf2/issues/2717)
- 2719: Fixing the Cache Storage Factory Adapter Factory
  (https://github.com/zendframework/zf2/issues/2719)
- 2728: Bad Regex for Content Type header
  (https://github.com/zendframework/zf2/issues/2728)
- 2731: Reset the Order part when resetting Select
  (https://github.com/zendframework/zf2/issues/2731)
- 2732: Removed references to Mysqli in Zend\Db\Adapter\Driver\Pgsql
  (https://github.com/zendframework/zf2/issues/2732)
- 2733: fix @package Zend\_Validate should be Zend\_Validator
  (https://github.com/zendframework/zf2/issues/2733)
- 2734: fix i18n @package and @subpackage value
  (https://github.com/zendframework/zf2/issues/2734)
- 2736: fix captcha helper test.
  (https://github.com/zendframework/zf2/issues/2736)
- 2737: Issue #2728 - Bad Regex for Content Type header
  (https://github.com/zendframework/zf2/issues/2737)
- 2738: fix link 'quickstart' to version 2.0
  (https://github.com/zendframework/zf2/issues/2738)
- 2739: remove '@subpackage'  because Zend\Math is not in subpackage
  (https://github.com/zendframework/zf2/issues/2739)
- 2742: remove () in echo-ing (https://github.com/zendframework/zf2/issues/2742)
- 2749: Fix for #2678 (Zend\Db's Sqlsrv Driver)
  (https://github.com/zendframework/zf2/issues/2749)
- 2750: Adds the ability to instanciate by factory to AbstractPluginManager
  (https://github.com/zendframework/zf2/issues/2750)
- 2754: add the support to register module paths over namespace
  (https://github.com/zendframework/zf2/issues/2754)
- 2755:  remove Zend\Mvc\Controller\PluginBroker from aliases in
  "$defaultServiceConfig" (https://github.com/zendframework/zf2/issues/2755)
- 2759: Fix Zend\Code\Scanner\TokenArrayScanner
  (https://github.com/zendframework/zf2/issues/2759)
- 2764: Fixed Zend\Math\Rand::getString() to pass the parameter $strong to
  ::getBytes() (https://github.com/zendframework/zf2/issues/2764)
- 2765: Csrf: always use dedicated setter
  (https://github.com/zendframework/zf2/issues/2765)
- 2766: Session\Storage: always preserve REQUEST\_ACCESS\_TIME
  (https://github.com/zendframework/zf2/issues/2766)
- 2768: Zend\Validator dependency is missed in Zend\Cache composer.json
  (https://github.com/zendframework/zf2/issues/2768)
- 2769: change valueToLDAP to valueToLdap and valueFromLDAP to valueFromLdap
  (https://github.com/zendframework/zf2/issues/2769)
- 2770: Memcached (https://github.com/zendframework/zf2/issues/2770)
- 2775: Zend\Db\Sql: Fix for Mysql quoting during limit and offset
  (https://github.com/zendframework/zf2/issues/2775)
- 2776: Allow whitespace in Iban
  (https://github.com/zendframework/zf2/issues/2776)
- 2777: Fix issue when PREG\_BAD\_UTF8__OFFSET_ERROR is defined but Unicode support
  is not enabled on PCRE (https://github.com/zendframework/zf2/issues/2777)
- 2778: Undefined Index fix in ViewHelperManagerFactory
  (https://github.com/zendframework/zf2/issues/2778)
- 2779: Allow forms that have been added as fieldsets to bind values to bound
  ob... (https://github.com/zendframework/zf2/issues/2779)
- 2782: Issue 2781 (https://github.com/zendframework/zf2/issues/2782)


## 2.0.2 (21 Sep 2012):

- 2383: Changed unreserved char definition in Zend\Uri (ZF2-533) and added shell
  escaping to the test runner (https://github.com/zendframework/zf2/pull/2383)
- 2393: Trying to solve issue ZF2-558
  (https://github.com/zendframework/zf2/pull/2393)
- 2398: Segment route: add fix for optional groups within optional groups
  (https://github.com/zendframework/zf2/pull/2398)
- 2400: Use 'Router' in http env and 'HttpRouter' in cli
  (https://github.com/zendframework/zf2/pull/2400)
- 2401: Better precision for userland fmod algorithm
  (https://github.com/zendframework/zf2/pull/2401)


## 2.0.1 (20 Sep 2012):

- 2285: Seed RouteMatch params as long as params is set. This permits setting an
  empty array. (https://github.com/zendframework/zf2/pull/2285)
- 2286: prepareNotFoundViewModel listner -  eventResult as ViewModel if set
  (https://github.com/zendframework/zf2/pull/2286)
- 2290: <span>$label</span> only when filled
  (https://github.com/zendframework/zf2/pull/2290)
- 2292: Allow (int)0 in coomments count in entry feed
  (https://github.com/zendframework/zf2/pull/2292)
- 2295: force to check className parameters
  (https://github.com/zendframework/zf2/pull/2295)
- 2296: mini-fix in controller plugin manager
  (https://github.com/zendframework/zf2/pull/2296)
- 2297: fixed phpdoc in Zend\Mvc\ApplicationInterface
  (https://github.com/zendframework/zf2/pull/2297)
- 2298: Update to Date element use statements to make it clearer which DateTime
  (https://github.com/zendframework/zf2/pull/2298)
- 2300: FormRow translate label fix (#ZF2-516)
  (https://github.com/zendframework/zf2/pull/2300)
- 2302: Notifications now to #zftalk.dev
  (https://github.com/zendframework/zf2/pull/2302)
- 2306: Fix several cs (https://github.com/zendframework/zf2/pull/2306)
- 2307: Removed comment about non existent Zend\_Tool
  (https://github.com/zendframework/zf2/pull/2307)
- 2308: Fix pluginmanager get method error
  (https://github.com/zendframework/zf2/pull/2308)
- 2309: Add consistency with event name
  (https://github.com/zendframework/zf2/pull/2309)
- 2310: Update library/Zend/Db/Sql/Select.php
  (https://github.com/zendframework/zf2/pull/2310)
- 2311: Version update (https://github.com/zendframework/zf2/pull/2311)
- 2312: Validator Translations (https://github.com/zendframework/zf2/pull/2312)
- 2313: ZF2-336: Zend\Form adds enctype attribute as multipart/form-data
  (https://github.com/zendframework/zf2/pull/2313)
- 2317: Make Fieldset constructor consistent with parent Element class
  (https://github.com/zendframework/zf2/pull/2317)
- 2321: ZF2-534 Zend\Log\Writer\Syslog prevents setting application name
  (https://github.com/zendframework/zf2/pull/2321)
- 2322: Jump to cache-storing instead of returning
  (https://github.com/zendframework/zf2/pull/2322)
- 2323: Conditional statements improved(minor changes).
  (https://github.com/zendframework/zf2/pull/2323)
- 2324: Fix for ZF2-517: Zend\Mail\Header\GenericHeader fails to parse empty
  header (https://github.com/zendframework/zf2/pull/2324)
- 2328: Wrong \_\_clone method (https://github.com/zendframework/zf2/pull/2328)
- 2331: added validation support for optgroups
  (https://github.com/zendframework/zf2/pull/2331)
- 2332: README-GIT update with optional pre-commit hook
  (https://github.com/zendframework/zf2/pull/2332)
- 2334: Mail\Message::getSubject() should return value the way it was set
  (https://github.com/zendframework/zf2/pull/2334)
- 2335: ZF2-511 Updated refactored names and other fixes
  (https://github.com/zendframework/zf2/pull/2335)
- 2336: ZF-546 Remove duplicate check for time
  (https://github.com/zendframework/zf2/pull/2336)
- 2337: ZF2-539 Input type of image should not have attribute value
  (https://github.com/zendframework/zf2/pull/2337)
- 2338: ZF2-543: removed linked but not implemented cache adapters
  (https://github.com/zendframework/zf2/pull/2338)
- 2341: Updated Zend_Validate.php pt_BR translation to 25.Jul.2011 EN Revision
  (https://github.com/zendframework/zf2/pull/2341)
- 2342: ZF2-549 Zend\Log\Formatter\ErrorHandler does not handle complex events
  (https://github.com/zendframework/zf2/pull/2342)
- 2346: updated Page\Mvc::isActive to check if the controller param was
  tinkered (https://github.com/zendframework/zf2/pull/2346)
- 2349: Zend\Feed Added unittests for more code coverage
  (https://github.com/zendframework/zf2/pull/2349)
- 2350: Bug in Zend\ModuleManager\Listener\LocatorRegistrationListener
  (https://github.com/zendframework/zf2/pull/2350)
- 2351: ModuleManagerInterface is never used
  (https://github.com/zendframework/zf2/pull/2351)
- 2352: Hotfix for AbstractDb and Csrf Validators
  (https://github.com/zendframework/zf2/pull/2352)
- 2354: Update library/Zend/Feed/Writer/AbstractFeed.php
  (https://github.com/zendframework/zf2/pull/2354)
- 2355: Allow setting CsrfValidatorOptions in constructor
  (https://github.com/zendframework/zf2/pull/2355)
- 2356: Update library/Zend/Http/Cookies.php
  (https://github.com/zendframework/zf2/pull/2356)
- 2357: Update library/Zend/Barcode/Object/AbstractObject.php
  (https://github.com/zendframework/zf2/pull/2357)
- 2358: Update library/Zend/ServiceManager/AbstractPluginManager.php
  (https://github.com/zendframework/zf2/pull/2358)
- 2359: Update library/Zend/Server/Method/Parameter.php
  (https://github.com/zendframework/zf2/pull/2359)
- 2361: Zend\Form Added extra unit tests and some code improvements
  (https://github.com/zendframework/zf2/pull/2361)
- 2364: Remove unused use statements
  (https://github.com/zendframework/zf2/pull/2364)
- 2365: Resolve undefined classes and constants
  (https://github.com/zendframework/zf2/pull/2365)
- 2366: fixed typo in Zend\View\HelperPluginManager
  (https://github.com/zendframework/zf2/pull/2366)
- 2370: Error handling in AbstractWriter::write using Zend\Stdlib\ErrorHandler
  (https://github.com/zendframework/zf2/pull/2370)
- 2372: Update library/Zend/ServiceManager/Config.php
  (https://github.com/zendframework/zf2/pull/2372)
- 2375: zend-inputfilter already requires
  (https://github.com/zendframework/zf2/pull/2375)
- 2376: Activate the new GitHub feature: Contributing Guidelines
  (https://github.com/zendframework/zf2/pull/2376)
- 2377: Update library/Zend/Mvc/Controller/AbstractController.php
  (https://github.com/zendframework/zf2/pull/2377)
- 2379: Typo in property name in Zend/Db/Metadata/Object/AbstractTableObject.php
  (https://github.com/zendframework/zf2/pull/2379)
- 2382: PHPDoc params in AbstractTableGateway.php
  (https://github.com/zendframework/zf2/pull/2382)
- 2384: Replace Router with Http router in url view helper
  (https://github.com/zendframework/zf2/pull/2384)
- 2387: Replace PHP internal fmod function because it gives false negatives
  (https://github.com/zendframework/zf2/pull/2387)
- 2388: Proposed fix for ZF2-569 validating float with trailing 0's (10.0,
  10.10) (https://github.com/zendframework/zf2/pull/2388)
- 2391: clone in Filter\FilterChain
  (https://github.com/zendframework/zf2/pull/2391)
- Security fix: a number of classes were not using the Escaper component in
  order to perform URL, HTML, and/or HTML attribute escaping. Please see
  http://framework.zend.com/security/advisory/ZF2012-03 for more details.
