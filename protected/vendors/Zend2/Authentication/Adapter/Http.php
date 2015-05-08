<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Authentication\Adapter;

use Zend\Authentication;
use Zend\Http\Request as HTTPRequest;
use Zend\Http\Response as HTTPResponse;
use Zend\Uri\UriFactory;
use Zend\Crypt\Utils as CryptUtils;

/**
 * HTTP Authentication Adapter
 *
 * Implements a pretty good chunk of RFC 2617.
 *
 * @todo       Support auth-int
 * @todo       Track nonces, nonce-count, opaque for replay protection and stale support
 * @todo       Support Authentication-Info header
 */
class Http implements AdapterInterface
{
    /**
     * Reference to the HTTP Request object
     *
     * @var HTTPRequest
     */
    protected $request;

    /**
     * Reference to the HTTP Response object
     *
     * @var HTTPResponse
     */
    protected $response;

    /**
     * Object that looks up user credentials for the Basic scheme
     *
     * @var Http\ResolverInterface
     */
    protected $basicResolver;

    /**
     * Object that looks up user credentials for the Digest scheme
     *
     * @var Http\ResolverInterface
     */
    protected $digestResolver;

    /**
     * List of authentication schemes supported by this class
     *
     * @var array
     */
    protected $supportedSchemes = array('basic', 'digest');

    /**
     * List of schemes this class will accept from the client
     *
     * @var array
     */
    protected $acceptSchemes;

    /**
     * Space-delimited list of protected domains for Digest Auth
     *
     * @var string
     */
    protected $domains;

    /**
     * The protection realm to use
     *
     * @var string
     */
    protected $realm;

    /**
     * Nonce timeout period
     *
     * @var int
     */
    protected $nonceTimeout;

    /**
     * Whether to send the opaque value in the header. True by default
     *
     * @var bool
     */
    protected $useOpaque;

    /**
     * List of the supported digest algorithms. I want to support both MD5 and
     * MD5-sess, but MD5-sess won't make it into the first version.
     *
     * @var array
     */
    protected $supportedAlgos = array('MD5');

    /**
     * The actual algorithm to use. Defaults to MD5
     *
     * @var string
     */
    protected $algo;

    /**
     * List of supported qop options. My intention is to support both 'auth' and
     * 'auth-int', but 'auth-int' won't make it into the first version.
     *
     * @var array
     */
    protected $supportedQops = array('auth');

    /**
     * Whether or not to do Proxy Authentication instead of origin server
     * authentication (send 407's instead of 401's). Off by default.
     *
     * @var bool
     */
    protected $imaProxy;

    /**
     * Flag indicating the client is IE and didn't bother to return the opaque string
     *
     * @var bool
     */
    protected $ieNoOpaque;

    /**
     * Constructor
     *
     * @param  array $config Configuration settings:
     *    'accept_schemes' => 'basic'|'digest'|'basic digest'
     *    'realm' => <string>
     *    'digest_domains' => <string> Space-delimited list of URIs
     *    'nonce_timeout' => <int>
     *    'use_opaque' => <bool> Whether to send the opaque value in the header
     *    'algorithm' => <string> See $supportedAlgos. Default: MD5
     *    'proxy_auth' => <bool> Whether to do authentication as a Proxy
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $config)
    {
        $this->request  = null;
        $this->response = null;
        $this->ieNoOpaque = false;

        if (empty($config['accept_schemes'])) {
            throw new Exception\InvalidArgumentException('Config key "accept_schemes" is required');
        }

        $schemes = explode(' ', $config['accept_schemes']);
        $this->acceptSchemes = array_intersect($schemes, $this->supportedSchemes);
        if (empty($this->acceptSchemes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'No supported schemes given in "accept_schemes". Valid values: %s',
                implode(', ', $this->supportedSchemes)
            ));
        }

        // Double-quotes are used to delimit the realm string in the HTTP header,
        // and colons are field delimiters in the password file.
        if (empty($config['realm']) ||
            !ctype_print($config['realm']) ||
            strpos($config['realm'], ':') !== false ||
            strpos($config['realm'], '"') !== false) {
            throw new Exception\InvalidArgumentException(
                'Config key \'realm\' is required, and must contain only printable characters,'
                . 'excluding quotation marks and colons'
            );
        } else {
            $this->realm = $config['realm'];
        }

        if (in_array('digest', $this->acceptSchemes)) {
            if (empty($config['digest_domains']) ||
                !ctype_print($config['digest_domains']) ||
                strpos($config['digest_domains'], '"') !== false) {
                throw new Exception\InvalidArgumentException(
                    'Config key \'digest_domains\' is required, and must contain '
                    . 'only printable characters, excluding quotation marks'
                );
            } else {
                $this->domains = $config['digest_domains'];
            }

            if (empty($config['nonce_timeout']) ||
                !is_numeric($config['nonce_timeout'])) {
                throw new Exception\InvalidArgumentException(
                    'Config key \'nonce_timeout\' is required, and must be an integer'
                );
            } else {
                $this->nonceTimeout = (int) $config['nonce_timeout'];
            }

            // We use the opaque value unless explicitly told not to
            if (isset($config['use_opaque']) && false == (bool) $config['use_opaque']) {
                $this->useOpaque = false;
            } else {
                $this->useOpaque = true;
            }

            if (isset($config['algorithm']) && in_array($config['algorithm'], $this->supportedAlgos)) {
                $this->algo = $config['algorithm'];
            } else {
                $this->algo = 'MD5';
            }
        }

        // Don't be a proxy unless explicitly told to do so
        if (isset($config['proxy_auth']) && true == (bool) $config['proxy_auth']) {
            $this->imaProxy = true;  // I'm a Proxy
        } else {
            $this->imaProxy = false;
        }
    }

    /**
     * Setter for the basicResolver property
     *
     * @param  Http\ResolverInterface $resolver
     * @return Http Provides a fluent interface
     */
    public function setBasicResolver(Http\ResolverInterface $resolver)
    {
        $this->basicResolver = $resolver;

        return $this;
    }

    /**
     * Getter for the basicResolver property
     *
     * @return Http\ResolverInterface
     */
    public function getBasicResolver()
    {
        return $this->basicResolver;
    }

    /**
     * Setter for the digestResolver property
     *
     * @param  Http\ResolverInterface $resolver
     * @return Http Provides a fluent interface
     */
    public function setDigestResolver(Http\ResolverInterface $resolver)
    {
        $this->digestResolver = $resolver;

        return $this;
    }

    /**
     * Getter for the digestResolver property
     *
     * @return Http\ResolverInterface
     */
    public function getDigestResolver()
    {
        return $this->digestResolver;
    }

    /**
     * Setter for the Request object
     *
     * @param  HTTPRequest $request
     * @return Http Provides a fluent interface
     */
    public function setRequest(HTTPRequest $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Getter for the Request object
     *
     * @return HTTPRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Setter for the Response object
     *
     * @param  HTTPResponse $response
     * @return Http Provides a fluent interface
     */
    public function setResponse(HTTPResponse $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Getter for the Response object
     *
     * @return HTTPResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Authenticate
     *
     * @throws Exception\RuntimeException
     * @return Authentication\Result
     */
    public function authenticate()
    {
        if (empty($this->request) || empty($this->response)) {
            throw new Exception\RuntimeException('Request and Response objects must be set before calling '
                                                . 'authenticate()');
        }

        if ($this->imaProxy) {
            $getHeader = 'Proxy-Authorization';
        } else {
            $getHeader = 'Authorization';
        }

        $headers = $this->request->getHeaders();
        if (!$headers->has($getHeader)) {
            return $this->_challengeClient();
        }
        $authHeader = $headers->get($getHeader)->getFieldValue();
        if (!$authHeader) {
            return $this->_challengeClient();
        }

        list($clientScheme) = explode(' ', $authHeader);
        $clientScheme = strtolower($clientScheme);

        // The server can issue multiple challenges, but the client should
        // answer with only the selected auth scheme.
        if (!in_array($clientScheme, $this->supportedSchemes)) {
            $this->response->setStatusCode(400);
            return new Authentication\Result(
                Authentication\Result::FAILURE_UNCATEGORIZED,
                array(),
                array('Client requested an incorrect or unsupported authentication scheme')
            );
        }

        // client sent a scheme that is not the one required
        if (!in_array($clientScheme, $this->acceptSchemes)) {
            // challenge again the client
            return $this->_challengeClient();
        }

        switch ($clientScheme) {
            case 'basic':
                $result = $this->_basicAuth($authHeader);
                break;
            case 'digest':
                $result = $this->_digestAuth($authHeader);
                break;
            default:
                throw new Exception\RuntimeException('Unsupported authentication scheme: ' . $clientScheme);
        }

        return $result;
    }

    /**
     * Challenge Client
     *
     * Sets a 401 or 407 Unauthorized response code, and creates the
     * appropriate Authenticate header(s) to prompt for credentials.
     *
     * @return Authentication\Result Always returns a non-identity Auth result
     */
    protected function _challengeClient()
    {
        if ($this->imaProxy) {
            $statusCode = 407;
            $headerName = 'Proxy-Authenticate';
        } else {
            $statusCode = 401;
            $headerName = 'WWW-Authenticate';
        }

        $this->response->setStatusCode($statusCode);

        // Send a challenge in each acceptable authentication scheme
        $headers = $this->response->getHeaders();
        if (in_array('basic', $this->acceptSchemes)) {
            $headers->addHeaderLine($headerName, $this->_basicHeader());
        }
        if (in_array('digest', $this->acceptSchemes)) {
            $headers->addHeaderLine($headerName, $this->_digestHeader());
        }
        return new Authentication\Result(
            Authentication\Result::FAILURE_CREDENTIAL_INVALID,
            array(),
            array('Invalid or absent credentials; challenging client')
        );
    }

    /**
     * Basic Header
     *
     * Generates a Proxy- or WWW-Authenticate header value in the Basic
     * authentication scheme.
     *
     * @return string Authenticate header value
     */
    protected function _basicHeader()
    {
        return 'Basic realm="' . $this->realm . '"';
    }

    /**
     * Digest Header
     *
     * Generates a Proxy- or WWW-Authenticate header value in the Digest
     * authentication scheme.
     *
     * @return string Authenticate header value
     */
    protected function _digestHeader()
    {
        $wwwauth = 'Digest realm="' . $this->realm . '", '
                 . 'domain="' . $this->domains . '", '
                 . 'nonce="' . $this->_calcNonce() . '", '
                 . ($this->useOpaque ? 'opaque="' . $this->_calcOpaque() . '", ' : '')
                 . 'algorithm="' . $this->algo . '", '
                 . 'qop="' . implode(',', $this->supportedQops) . '"';

        return $wwwauth;
    }

    /**
     * Basic Authentication
     *
     * @param  string $header Client's Authorization header
     * @throws Exception\ExceptionInterface
     * @return Authentication\Result
     */
    protected function _basicAuth($header)
    {
        if (empty($header)) {
            throw new Exception\RuntimeException('The value of the client Authorization header is required');
        }
        if (empty($this->basicResolver)) {
            throw new Exception\RuntimeException(
                'A basicResolver object must be set before doing Basic '
                . 'authentication');
        }

        // Decode the Authorization header
        $auth = substr($header, strlen('Basic '));
        $auth = base64_decode($auth);
        if (!$auth) {
            throw new Exception\RuntimeException('Unable to base64_decode Authorization header value');
        }

        // See ZF-1253. Validate the credentials the same way the digest
        // implementation does. If invalid credentials are detected,
        // re-challenge the client.
        if (!ctype_print($auth)) {
            return $this->_challengeClient();
        }
        // Fix for ZF-1515: Now re-challenges on empty username or password
        $creds = array_filter(explode(':', $auth));
        if (count($creds) != 2) {
            return $this->_challengeClient();
        }

        $result = $this->basicResolver->resolve($creds[0], $this->realm, $creds[1]);

        if ($result instanceof Authentication\Result && $result->isValid()) {
            return $result;
        }

        if (!$result instanceof Authentication\Result
            && !is_array($result)
            && CryptUtils::compareStrings($result, $creds[1])
        ) {
            $identity = array('username' => $creds[0], 'realm' => $this->realm);
            return new Authentication\Result(Authentication\Result::SUCCESS, $identity);
        } elseif (is_array($result)) {
            return new Authentication\Result(Authentication\Result::SUCCESS, $result);
        }

        return $this->_challengeClient();
    }

    /**
     * Digest Authentication
     *
     * @param  string $header Client's Authorization header
     * @throws Exception\ExceptionInterface
     * @return Authentication\Result Valid auth result only on successful auth
     */
    protected function _digestAuth($header)
    {
        if (empty($header)) {
            throw new Exception\RuntimeException('The value of the client Authorization header is required');
        }
        if (empty($this->digestResolver)) {
            throw new Exception\RuntimeException('A digestResolver object must be set before doing Digest authentication');
        }

        $data = $this->_parseDigestAuth($header);
        if ($data === false) {
            $this->response->setStatusCode(400);
            return new Authentication\Result(
                Authentication\Result::FAILURE_UNCATEGORIZED,
                array(),
                array('Invalid Authorization header format')
            );
        }

        // See ZF-1052. This code was a bit too unforgiving of invalid
        // usernames. Now, if the username is bad, we re-challenge the client.
        if ('::invalid::' == $data['username']) {
            return $this->_challengeClient();
        }

        // Verify that the client sent back the same nonce
        if ($this->_calcNonce() != $data['nonce']) {
            return $this->_challengeClient();
        }
        // The opaque value is also required to match, but of course IE doesn't
        // play ball.
        if (!$this->ieNoOpaque && $this->_calcOpaque() != $data['opaque']) {
            return $this->_challengeClient();
        }

        // Look up the user's password hash. If not found, deny access.
        // This makes no assumptions about how the password hash was
        // constructed beyond that it must have been built in such a way as
        // to be recreatable with the current settings of this object.
        $ha1 = $this->digestResolver->resolve($data['username'], $data['realm']);
        if ($ha1 === false) {
            return $this->_challengeClient();
        }

        // If MD5-sess is used, a1 value is made of the user's password
        // hash with the server and client nonce appended, separated by
        // colons.
        if ($this->algo == 'MD5-sess') {
            $ha1 = hash('md5', $ha1 . ':' . $data['nonce'] . ':' . $data['cnonce']);
        }

        // Calculate h(a2). The value of this hash depends on the qop
        // option selected by the client and the supported hash functions
        switch ($data['qop']) {
            case 'auth':
                $a2 = $this->request->getMethod() . ':' . $data['uri'];
                break;
            case 'auth-int':
                // Should be REQUEST_METHOD . ':' . uri . ':' . hash(entity-body),
                // but this isn't supported yet, so fall through to default case
            default:
                throw new Exception\RuntimeException('Client requested an unsupported qop option');
        }
        // Using hash() should make parameterizing the hash algorithm
        // easier
        $ha2 = hash('md5', $a2);


        // Calculate the server's version of the request-digest. This must
        // match $data['response']. See RFC 2617, section 3.2.2.1
        $message = $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $ha2;
        $digest  = hash('md5', $ha1 . ':' . $message);

        // If our digest matches the client's let them in, otherwise return
        // a 401 code and exit to prevent access to the protected resource.
        if (CryptUtils::compareStrings($digest, $data['response'])) {
            $identity = array('username' => $data['username'], 'realm' => $data['realm']);
            return new Authentication\Result(Authentication\Result::SUCCESS, $identity);
        }

        return $this->_challengeClient();
    }

    /**
     * Calculate Nonce
     *
     * @return string The nonce value
     */
    protected function _calcNonce()
    {
        // Once subtle consequence of this timeout calculation is that it
        // actually divides all of time into nonceTimeout-sized sections, such
        // that the value of timeout is the point in time of the next
        // approaching "boundary" of a section. This allows the server to
        // consistently generate the same timeout (and hence the same nonce
        // value) across requests, but only as long as one of those
        // "boundaries" is not crossed between requests. If that happens, the
        // nonce will change on its own, and effectively log the user out. This
        // would be surprising if the user just logged in.
        $timeout = ceil(time() / $this->nonceTimeout) * $this->nonceTimeout;

        $userAgentHeader = $this->request->getHeaders()->get('User-Agent');
        if ($userAgentHeader) {
            $userAgent = $userAgentHeader->getFieldValue();
        } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $userAgent = 'Zend_Authenticaion';
        }
        $nonce = hash('md5', $timeout . ':' . $userAgent . ':' . __CLASS__);
        return $nonce;
    }

    /**
     * Calculate Opaque
     *
     * The opaque string can be anything; the client must return it exactly as
     * it was sent. It may be useful to store data in this string in some
     * applications. Ideally, a new value for this would be generated each time
     * a WWW-Authenticate header is sent (in order to reduce predictability),
     * but we would have to be able to create the same exact value across at
     * least two separate requests from the same client.
     *
     * @return string The opaque value
     */
    protected function _calcOpaque()
    {
        return hash('md5', 'Opaque Data:' . __CLASS__);
    }

    /**
     * Parse Digest Authorization header
     *
     * @param  string $header Client's Authorization: HTTP header
     * @return array|bool Data elements from header, or false if any part of
     *                    the header is invalid
     */
    protected function _parseDigestAuth($header)
    {
        $temp = null;
        $data = array();

        // See ZF-1052. Detect invalid usernames instead of just returning a
        // 400 code.
        $ret = preg_match('/username="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])
                  || !ctype_print($temp[1])
                  || strpos($temp[1], ':') !== false) {
            $data['username'] = '::invalid::';
        } else {
            $data['username'] = $temp[1];
        }
        $temp = null;

        $ret = preg_match('/realm="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!ctype_print($temp[1]) || strpos($temp[1], ':') !== false) {
            return false;
        } else {
            $data['realm'] = $temp[1];
        }
        $temp = null;

        $ret = preg_match('/nonce="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!ctype_xdigit($temp[1])) {
            return false;
        }

        $data['nonce'] = $temp[1];
        $temp = null;

        $ret = preg_match('/uri="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        // Section 3.2.2.5 in RFC 2617 says the authenticating server must
        // verify that the URI field in the Authorization header is for the
        // same resource requested in the Request Line.
        $rUri = $this->request->getUri();
        $cUri = UriFactory::factory($temp[1]);

        // Make sure the path portion of both URIs is the same
        if ($rUri->getPath() != $cUri->getPath()) {
            return false;
        }

        // Section 3.2.2.5 seems to suggest that the value of the URI
        // Authorization field should be made into an absolute URI if the
        // Request URI is absolute, but it's vague, and that's a bunch of
        // code I don't want to write right now.
        $data['uri'] = $temp[1];
        $temp = null;

        $ret = preg_match('/response="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (32 != strlen($temp[1]) || !ctype_xdigit($temp[1])) {
            return false;
        }

        $data['response'] = $temp[1];
        $temp = null;

        // The spec says this should default to MD5 if omitted. OK, so how does
        // that square with the algo we send out in the WWW-Authenticate header,
        // if it can easily be overridden by the client?
        $ret = preg_match('/algorithm="?(' . $this->algo . ')"?/', $header, $temp);
        if ($ret && !empty($temp[1])
                 && in_array($temp[1], $this->supportedAlgos)) {
            $data['algorithm'] = $temp[1];
        } else {
            $data['algorithm'] = 'MD5';  // = $this->algo; ?
        }
        $temp = null;

        // Not optional in this implementation
        $ret = preg_match('/cnonce="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!ctype_print($temp[1])) {
            return false;
        }

        $data['cnonce'] = $temp[1];
        $temp = null;

        // If the server sent an opaque value, the client must send it back
        if ($this->useOpaque) {
            $ret = preg_match('/opaque="([^"]+)"/', $header, $temp);
            if (!$ret || empty($temp[1])) {

                // Big surprise: IE isn't RFC 2617-compliant.
                $headers = $this->request->getHeaders();
                if (!$headers->has('User-Agent')) {
                    return false;
                }
                $userAgent = $headers->get('User-Agent')->getFieldValue();
                if (false === strpos($userAgent, 'MSIE')) {
                    return false;
                }

                $temp[1] = '';
                $this->ieNoOpaque = true;
            }

            // This implementation only sends MD5 hex strings in the opaque value
            if (!$this->ieNoOpaque &&
                (32 != strlen($temp[1]) || !ctype_xdigit($temp[1]))) {
                return false;
            }

            $data['opaque'] = $temp[1];
            $temp = null;
        }

        // Not optional in this implementation, but must be one of the supported
        // qop types
        $ret = preg_match('/qop="?(' . implode('|', $this->supportedQops) . ')"?/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!in_array($temp[1], $this->supportedQops)) {
            return false;
        }

        $data['qop'] = $temp[1];
        $temp = null;

        // Not optional in this implementation. The spec says this value
        // shouldn't be a quoted string, but apparently some implementations
        // quote it anyway. See ZF-1544.
        $ret = preg_match('/nc="?([0-9A-Fa-f]{8})"?/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (8 != strlen($temp[1]) || !ctype_xdigit($temp[1])) {
            return false;
        }

        $data['nc'] = $temp[1];
        $temp = null;

        return $data;
    }
}
