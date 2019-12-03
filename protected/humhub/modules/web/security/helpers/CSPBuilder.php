<?php

namespace humhub\modules\web\security\helpers;

use Psr\Http\Message\MessageInterface;

/**
 * Class CSPBuilder from https://github.com/paragonie/csp-builder/blob/master/src/CSPBuilder.php made compatible with
 * PHP 5.6
 *
 * HumHub Patches:
 *  - PHP 5.6 compatibility
 *  - Removed report-to since it borke reporting
 *  - Added rtrim to compile to remove tailing ;
 *  - Added report-sample support
 *
 * @package humhub\modules\web\security\helpers
 */
class CSPBuilder
{
    const FORMAT_APACHE = 'apache';
    const FORMAT_NGINX = 'nginx';

    /**
     * @var array
     */
    private $policies = [];

    /**
     * @var array<int, string>
     */
    private $requireSRIFor = [];

    /**
     * @var bool
     */
    private $needsCompile = true;

    /**
     * @var string
     */
    private $compiled = '';

    /**
     * @var bool
     */
    private $reportOnly = false;

    /**
     * @var bool
     */
    protected $supportOldBrowsers = true;

    /**
     * @var bool
     */
    protected $httpsTransformOnHttpsConnections = true;

    /**
     * @var string[]
     */
    private static $directives = [
        'base-uri',
        'default-src',
        'child-src',
        'connect-src',
        'font-src',
        'form-action',
        'frame-ancestors',
        'frame-src',
        'img-src',
        'media-src',
        'object-src',
        'plugin-types',
        'manifest-src',
        'script-src',
        'style-src',
        'worker-src'
    ];

    /**
     * @param array $policy
     */
    public function __construct(array $policy = [])
    {
        $this->policies = $policy;
    }

    /**
     * Compile the current policies into a CSP header
     *
     * @return string
     * @throws \TypeError
     */
    public function compile()
    {
        $ruleKeys = \array_keys($this->policies);
        if (\in_array('report-only', $ruleKeys)) {
            $this->reportOnly = !!$this->policies['report-only'];
        } else {
            $this->reportOnly = false;
        }

        $compiled = [];

        foreach (self::$directives as $dir) {
            if (\in_array($dir, $ruleKeys)) {
                if (empty($ruleKeys)) {
                    if ($dir === 'base-uri') {
                        continue;
                    }
                }
                $compiled []= $this->compileSubgroup(
                    $dir,
                    $this->policies[$dir]
                );
            }
        }

        if (!empty($this->policies['report-uri'])) {
            if (!\is_string($this->policies['report-uri'])) {
                throw new \TypeError('report-uri policy somehow not a string');
            }
            if ($this->supportOldBrowsers) {
                $compiled [] = 'report-uri ' . $this->policies['report-uri'] . '; ';
            }
        }
        if (!empty($this->policies['upgrade-insecure-requests'])) {
            $compiled []= 'upgrade-insecure-requests';
        }

        $this->compiled = \implode('', $compiled);
        $this->needsCompile = false;
        return rtrim($this->compiled, ';');
    }

    /**
     * Add a source to our allow white-list
     *
     * @param string $directive
     * @param string $path
     *
     * @return self
     */
    public function addSource( $directive,  $path)
    {
        switch ($directive) {
            case 'child':
            case 'frame':
            case 'frame-src':
                if ($this->supportOldBrowsers) {
                    $this->policies['child-src']['allow'][] = $path;
                    $this->policies['frame-src']['allow'][] = $path;
                    return $this;
                }
                $directive = 'child-src';
                break;
            case 'connect':
            case 'socket':
            case 'websocket':
                $directive = 'connect-src';
                break;
            case 'font':
            case 'fonts':
                $directive = 'font-src';
                break;
            case 'form':
            case 'forms':
                $directive = 'form-action';
                break;
            case 'ancestor':
            case 'parent':
                $directive = 'frame-ancestors';
                break;
            case 'img':
            case 'image':
            case 'image-src':
                $directive = 'img-src';
                break;
            case 'media':
                $directive = 'media-src';
                break;
            case 'object':
                $directive = 'object-src';
                break;
            case 'js':
            case 'javascript':
            case 'script':
            case 'scripts':
                $directive = 'script-src';
                break;
            case 'style':
            case 'css':
            case 'css-src':
                $directive = 'style-src';
                break;
            case 'worker':
                $directive = 'worker-src';
                break;
        }
        $this->policies[$directive]['allow'][] = $path;
        return $this;
    }

    /**
     * Add a directive if it doesn't already exist
     *
     * If it already exists, do nothing
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function addDirective( $key, $value = null)
    {
        if ($value === null) {
            if (!isset($this->policies[$key])) {
                $this->policies[$key] = true;
            }
        } elseif (empty($this->policies[$key])) {
            $this->policies[$key] = $value;
        }
        return $this;
    }

    /**
     * Add a plugin type to be added
     *
     * @param string $mime
     * @return self
     */
    public function allowPluginType($mime = 'text/plain')
    {
        $this->policies['plugin-types']['types'] []= $mime;

        $this->needsCompile = true;
        return $this;
    }

    /**
     * Disable old browser support (e.g. Safari)
     *
     * @return self
     */
    public function disableOldBrowserSupport()
    {
        $this->needsCompile = ($this->needsCompile || $this->supportOldBrowsers !== false);
        $this->supportOldBrowsers = false;
        return $this;
    }

    /**
     * Enable old browser support (e.g. Safari)
     *
     * This is enabled by default
     *
     * @return self
     */
    public function enableOldBrowserSupport()
    {
        $this->needsCompile = ($this->needsCompile || $this->supportOldBrowsers !== true);
        $this->supportOldBrowsers = true;
        return $this;
    }

    /**
     * This just passes the array to the constructor, but hopefully will save
     * someone in a hurry from a moment of frustration.
     *
     * @param array $array
     * @return self
     */
    public static function fromArray(array $array = [])
    {
        return new CSPBuilder($array);
    }

    /**
     * Factory method - create a new CSPBuilder object from a JSON data
     *
     * @param string $data
     * @return self
     * @throws \Exception
     */
    public static function fromData($data = '')
    {
        $array = \json_decode($data, true);

        if (!\is_array($array)) {
            throw new \Exception('Is not array valid');
        }

        return new CSPBuilder($array);
    }

    /**
     * Factory method - create a new CSPBuilder object from a JSON file
     *
     * @param string $filename
     * @return self
     * @throws \Exception
     */
    public static function fromFile($filename = '')
    {
        if (!\file_exists($filename)) {
            throw new \Exception($filename.' does not exist');
        }
        $contents = \file_get_contents($filename);
        if (!\is_string($contents)) {
            throw new \Exception('Could not read file contents');
        }
        return self::fromData($contents);
    }

    /**
     * Get the formatted CSP header
     *
     * @return string
     */
    public function getCompiledHeader()
    {
        if ($this->needsCompile) {
            $this->compile();
        }
        return $this->compiled;
    }

    /**
     * Get an associative array of headers to return.
     *
     * @param bool $legacy
     * @return array<string, string>
     */
    public function getHeaderArray($legacy = true)
    {
        if ($this->needsCompile) {
            $this->compile();
        }
        $return = [];
        foreach ($this->getHeaderKeys($legacy) as $key) {
            $return[(string) $key] = $this->compiled;
        }
        return $return;
    }

    /**
     * @return array<int, array{0:string, 1:string}>
     */
    public function getRequireHeaders()
    {
        $headers = [];
        foreach ($this->requireSRIFor as $directive) {
            $headers[] = [
                'Content-Security-Policy',
                'require-sri-for ' . $directive
            ];
        }
        return $headers;
    }

    /**
     * Add a new hash to the existing CSP
     *
     * @param string $directive
     * @param string $script
     * @param string $algorithm
     * @return self
     */
    public function hash(
        $directive = 'script-src',
        $script = '',
        $algorithm = 'sha384'
    ) {
        $ruleKeys = \array_keys($this->policies);
        if (\in_array($directive, $ruleKeys)) {
            $this->policies[$directive]['hashes'] []= [
                $algorithm => base64_encode(
                    \hash($algorithm, $script, true)
                )
            ];
        }
        return $this;
    }

    /**
     * PSR-7 header injection.
     *
     * This will inject the header into your PSR-7 object. (Request, Response,
     * etc.) This method returns an instance of whatever you passed, so long
     * as it implements MessageInterface.
     *
     * @param \Psr\Http\Message\MessageInterface $message
     * @param bool $legacy
     * @return \Psr\Http\Message\MessageInterface
     */
    public function injectCSPHeader(MessageInterface $message, $legacy = false)
    {
        if ($this->needsCompile) {
            $this->compile();
        }
        foreach ($this->getRequireHeaders() as $header) {
            list ($key, $value) = $header;
            $message = $message->withAddedHeader($key, $value);
        }
        foreach ($this->getHeaderKeys($legacy) as $key) {
            $message = $message->withAddedHeader($key, $this->compiled);
        }
        return $message;
    }

    /**
     * Add a new nonce to the existing CSP. Returns the nonce generated.
     *
     * @param string $directive
     * @param string $nonce (if empty, it will be generated)
     * @return string
     * @throws \Exception
     */
    public function nonce($directive = 'script-src', $nonce = '')
    {
        $ruleKeys = \array_keys($this->policies);
        if (!\in_array($directive, $ruleKeys)) {
            return '';
        }

        if (empty($nonce)) {
            $nonce = base64_encode(\random_bytes(18));
        }
        $this->policies[$directive]['nonces'] []= $nonce;
        return $nonce;
    }

    /**
     * Add a new (pre-calculated) base64-encoded hash to the existing CSP
     *
     * @param string $directive
     * @param string $hash
     * @param string $algorithm
     * @return self
     */
    public function preHash(
        $directive = 'script-src',
        $hash = '',
        $algorithm = 'sha384'
    ) {
        $ruleKeys = \array_keys($this->policies);
        if (\in_array($directive, $ruleKeys)) {
            $this->policies[$directive]['hashes'] []= [
                $algorithm => $hash
            ];
        }
        return $this;
    }

    /**
     * @param string $directive
     * @return self
     */
    public function requireSRIFor($directive)
    {
        if (!\in_array($directive, $this->requireSRIFor, true)) {
            $this->requireSRIFor[] = $directive;
        }
        return $this;
    }

    /**
     * Save CSP to a snippet file
     *
     * @param string $outputFile Output file name
     * @param string $format Which format are we saving in?
     * @return bool
     * @throws \Exception
     */
    public function saveSnippet(
        $outputFile,
        $format = self::FORMAT_NGINX
    ) {
        if ($this->needsCompile) {
            $this->compile();
        }

        // Are we doing a report-only header?
        $which = $this->reportOnly
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        switch ($format) {
            case self::FORMAT_NGINX:
                // In PHP < 7, implode() is faster than concatenation
                $output = \implode('', [
                    'add_header ',
                    $which,
                    ' "',
                    \rtrim($this->compiled, ' '),
                    '" always;',
                    "\n"
                ]);
                break;
            case self::FORMAT_APACHE:
                $output = \implode('', [
                    'Header add ',
                    $which,
                    ' "',
                    \rtrim($this->compiled, ' '),
                    '"',
                    "\n"
                ]);
                break;
            default:
                throw new \Exception('Unknown format: '.$format);
        }
        return \file_put_contents($outputFile, $output) !== false;
    }

    /**
     * Send the compiled CSP as a header()
     *
     * @param bool $legacy Send legacy headers?
     *
     * @return bool
     * @throws \Exception
     */
    public function sendCSPHeader($legacy = true)
    {
        if (\headers_sent()) {
            throw new \Exception('Headers already sent!');
        }
        if ($this->needsCompile) {
            $this->compile();
        }
        foreach ($this->getRequireHeaders() as $header) {
            list ($key, $value) = $header;
            \header($key.': '.$value);
        }
        foreach ($this->getHeaderKeys($legacy) as $key) {
            \header($key.': '.$this->compiled);
        }
        return true;
    }

    /**
     * Allow/disallow unsafe-eval within a given directive.
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setAllowUnsafeEval($directive = '', $allow = false)
    {
        if (!\in_array($directive, self::$directives)) {
            throw new \Exception('Directive ' . $directive . ' does not exist');
        }
        $this->policies[$directive]['unsafe-eval'] = $allow;
        return $this;
    }

    /**
     * Allow/disallow unsafe-inline within a given directive.
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setAllowUnsafeInline($directive = '', $allow = false)
    {
        if (!\in_array($directive, self::$directives)) {
            throw new \Exception('Directive ' . $directive . ' does not exist');
        }
        $this->policies[$directive]['unsafe-inline'] = $allow;
        return $this;
    }

    /**
     * Allow/disallow blob: URIs for a given directive
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setBlobAllowed($directive = '', $allow = false)
    {
        if (!\in_array($directive, self::$directives)) {
            throw new \Exception('Directive ' . $directive . ' does not exist');
        }
        $this->policies[$directive]['blob'] = $allow;
        return $this;
    }

    /**
     * Allow/disallow data: URIs for a given directive
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setDataAllowed($directive = '', $allow = false)
    {
        if (!\in_array($directive, self::$directives)) {
            throw new \Exception('Directive ' . $directive . ' does not exist');
        }
        $this->policies[$directive]['data'] = $allow;
        return $this;
    }

    /**
     * Set a directive.
     *
     * This lets you overwrite a complex directive entirely (e.g. script-src)
     * or set a top-level directive (e.g. report-uri).
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function setDirective($key, $value = [])
    {
        $this->policies[$key] = $value;
        return $this;
    }

    /**
     * Allow/disallow filesystem: URIs for a given directive
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setFileSystemAllowed($directive = '', $allow = false)
    {
        if (!\in_array($directive, self::$directives)) {
            throw new \Exception('Directive ' . $directive . ' does not exist');
        }
        $this->policies[$directive]['filesystem'] = $allow;
        return $this;
    }

    /**
     * Allow/disallow mediastream: URIs for a given directive
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setMediaStreamAllowed($directive = '', $allow = false)
    {
        if (!\in_array($directive, self::$directives)) {
            throw new \Exception('Directive ' . $directive . ' does not exist');
        }
        $this->policies[$directive]['mediastream'] = $allow;
        return $this;
    }

    /**
     * Allow/disallow self URIs for a given directive
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setSelfAllowed($directive = '', $allow = false)
    {
        if (!\in_array($directive, self::$directives)) {
            throw new \Exception('Directive ' . $directive . ' does not exist');
        }
        $this->policies[$directive]['self'] = $allow;
        return $this;
    }

    /**
     * @see CSPBuilder::setAllowUnsafeEval()
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setUnsafeEvalAllowed($directive = '', $allow = false)
    {
        return $this->setAllowUnsafeEval($directive, $allow);
    }

    /**
     * @see CSPBuilder::setAllowUnsafeInline()
     *
     * @param string $directive
     * @param bool $allow
     * @return self
     * @throws \Exception
     */
    public function setUnsafeInlineAllowed($directive = '', $allow = false)
    {
        return $this->setAllowUnsafeInline($directive, $allow);
    }

    /**
     * Set strict-dynamic for a given directive.
     *
     * @param string $directive
     * @param bool $allow
     *
     * @return self
     * @throws \Exception
     */
    public function setStrictDynamic($directive = '', $allow = false)
    {
        $this->policies[$directive]['strict-dynamic'] = $allow;
        return $this;
    }

    /**
     * Set the Report URI to the desired string. This also sets the 'report-to'
     * component of the CSP header for CSP Level 3 compatibility.
     *
     * @param string $url
     * @return self
     */
    public function setReportUri($url = '')
    {
        $this->policies['report-uri'] = $url;
        return $this;
    }

    /**
     * Compile a subgroup into a policy string
     *
     * @param string $directive
     * @param mixed $policies
     *
     * @return string
     */
    protected function compileSubgroup($directive, $policies = [])
    {
        if ($policies === '*') {
            // Don't even waste the overhead adding this to the header
            return '';
        } elseif (empty($policies)) {
            if ($directive === 'plugin-types') {
                return '';
            }
            return $directive." 'none'; ";
        }
        $ret = $directive.' ';
        if ($directive === 'plugin-types') {
            // Expects MIME types, not URLs
            return $ret . \implode(' ', $policies['allow']).'; ';
        }
        if (!empty($policies['self'])) {
            $ret .= "'self' ";
        }

        if (!empty($policies['allow'])) {
            foreach ($policies['allow'] as $url) {
                $url = \filter_var($url, FILTER_SANITIZE_URL);
                if ($url !== false) {
                    if ($this->supportOldBrowsers) {
                        if (\strpos($url, '://') === false) {
                            if (($this->isHTTPSConnection() && $this->httpsTransformOnHttpsConnections)
                                || !empty($this->policies['upgrade-insecure-requests'])) {
                                // We only want HTTPS connections here.
                                $ret .= 'https://'.$url.' ';
                            } else {
                                $ret .= 'https://'.$url.' http://'.$url.' ';
                            }
                        }
                    }
                    if (($this->isHTTPSConnection() && $this->httpsTransformOnHttpsConnections)
                        || !empty($this->policies['upgrade-insecure-requests'])) {
                        $ret .= \str_replace('http://', 'https://', $url).' ';
                    } else {
                        $ret .= $url.' ';
                    }
                }
            }
        }

        if (!empty($policies['hashes'])) {
            foreach ($policies['hashes'] as $hash) {
                foreach ($hash as $algo => $hashval) {
                    $ret .= \implode('', [
                        "'",
                        \preg_replace('/[^A-Za-z0-9]/', '', $algo),
                        '-',
                        \preg_replace('/[^A-Za-z0-9\+\/=]/', '', $hashval),
                        "' "
                    ]);
                }
            }
        }

        if (!empty($policies['nonces'])) {
            foreach ($policies['nonces'] as $nonce) {
                $ret .= \implode('', [
                    "'nonce-",
                    \preg_replace('/[^A-Za-z0-9\+\/=]/', '', $nonce),
                    "' "
                ]);
            }
        }

        if (!empty($policies['types'])) {
            foreach ($policies['types'] as $type) {
                $ret .= $type.' ';
            }
        }

        if (!empty($policies['unsafe-inline'])) {
            $ret .= "'unsafe-inline' ";
        }
        if (!empty($policies['unsafe-eval'])) {
            $ret .= "'unsafe-eval' ";
        }
        if (!empty($policies['report-sample'])) {
            $ret .= "'report-sample' ";
        }
        if (!empty($policies['blob'])) {
            $ret .= "blob: ";
        }
        if (!empty($policies['data'])) {
            $ret .= "data: ";
        }
        if (!empty($policies['mediastream'])) {
            $ret .= "mediastream: ";
        }
        if (!empty($policies['filesystem'])) {
            $ret .= "filesystem: ";
        }
        if (!empty($policies['strict-dynamic'])) {
            $ret .= "'strict-dynamic' ";
        }
        if (!empty($policies['unsafe-hashed-attributes'])) {
            $ret .= "'unsafe-hashed-attributes' ";
        }
        return \rtrim($ret, ' ').'; ';
    }

    /**
     * Get an array of header keys to return
     *
     * @param bool $legacy
     * @return array
     */
    protected function getHeaderKeys($legacy = true)
    {
        // We always want this
        $return = [
            $this->reportOnly
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy'
        ];

        // If we're supporting legacy devices, include these too:
        if ($legacy) {
            $return []= $this->reportOnly
                ? 'X-Content-Security-Policy-Report-Only'
                : 'X-Content-Security-Policy';
            $return []= $this->reportOnly
                ? 'X-Webkit-CSP-Report-Only'
                : 'X-Webkit-CSP';
        }
        return $return;
    }

    /**
     * Is this user currently connected over HTTPS?
     *
     * @return bool
     */
    protected function isHTTPSConnection()
    {
        if (!empty($_SERVER['HTTPS'])) {
            return $_SERVER['HTTPS'] !== 'off';
        }
        return false;
    }

    /**
     * Disable that HTTP sources get converted to HTTPS if the connection is such.
     *
     * @return self
     */
    public function disableHttpsTransformOnHttpsConnections()
    {
        $this->needsCompile = ($this->needsCompile || $this->httpsTransformOnHttpsConnections !== false);
        $this->httpsTransformOnHttpsConnections = false;

        return $this;
    }

    /**
     * Enable that HTTP sources get converted to HTTPS if the connection is such.
     *
     * This is enabled by default
     *
     * @return self
     */
    public function enableHttpsTransformOnHttpsConnections()
    {
        $this->needsCompile = ($this->needsCompile || $this->httpsTransformOnHttpsConnections !== true);
        $this->httpsTransformOnHttpsConnections = true;

        return $this;
    }
}
