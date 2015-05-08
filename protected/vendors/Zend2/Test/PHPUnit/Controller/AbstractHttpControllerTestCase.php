<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Test\PHPUnit\Controller;

use PHPUnit_Framework_ExpectationFailedException;
use Zend\Dom;

abstract class AbstractHttpControllerTestCase extends AbstractControllerTestCase
{
    /**
     * HTTP controller must not use the console request
     * @var bool
     */
    protected $useConsoleRequest = false;

    /**
     * XPath namespaces
     * @var array
     */
    protected $xpathNamespaces = array();

    /**
     * Get response header by key
     *
     * @param  string $header
     * @return \Zend\Http\Header\HeaderInterface|false
     */
    protected function getResponseHeader($header)
    {
        $response       = $this->getResponse();
        $headers        = $response->getHeaders();
        $responseHeader = $headers->get($header, false);
        return $responseHeader;
    }

    /**
     * Assert response header exists
     *
     * @param  string $header
     */
    public function assertHasResponseHeader($header)
    {
        $responseHeader = $this->getResponseHeader($header);
        if (false === $responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" found',
                $header
            ));
        }
        $this->assertNotEquals(false, $responseHeader);
    }

    /**
     * Assert response header does not exist
     *
     * @param  string $header
     */
    public function assertNotHasResponseHeader($header)
    {
        $responseHeader = $this->getResponseHeader($header);
        if (false !== $responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" WAS NOT found',
                $header
            ));
        }
        $this->assertFalse($responseHeader);
    }

    /**
     * Assert response header exists and contains the given string
     *
     * @param  string $header
     * @param  string $match
     */
    public function assertResponseHeaderContains($header, $match)
    {
        $responseHeader = $this->getResponseHeader($header);
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists',
                $header
            ));
        }
        if ($match != $responseHeader->getFieldValue()) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" exists and contains "%s", actual content is "%s"',
                $header,
                $match,
                $responseHeader->getFieldValue()
            ));
        }
        $this->assertEquals($match, $responseHeader->getFieldValue());
    }

    /**
     * Assert response header exists and contains the given string
     *
     * @param  string $header
     * @param  string $match
     */
    public function assertNotResponseHeaderContains($header, $match)
    {
        $responseHeader = $this->getResponseHeader($header);
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists',
                $header
            ));
        }
        if ($match == $responseHeader->getFieldValue()) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" DOES NOT CONTAIN "%s"',
                $header,
                $match
            ));
        }
        $this->assertNotEquals($match, $responseHeader->getFieldValue());
    }

    /**
     * Assert response header exists and matches the given pattern
     *
     * @param  string $header
     * @param  string $pattern
     */
    public function assertResponseHeaderRegex($header, $pattern)
    {
        $responseHeader = $this->getResponseHeader($header);
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists',
                $header
            ));
        }
        if (!preg_match($pattern, $responseHeader->getFieldValue())) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" exists and matches regex "%s", actual content is "%s"',
                $header,
                $pattern,
                $responseHeader->getFieldValue()
            ));
        }
        $this->assertTrue((bool) preg_match($pattern, $responseHeader->getFieldValue()));
    }

    /**
     * Assert response header does not exist and/or does not match the given regex
     *
     * @param  string $header
     * @param  string $pattern
     */
    public function assertNotResponseHeaderRegex($header, $pattern)
    {
        $responseHeader = $this->getResponseHeader($header);
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists',
                $header
            ));
        }
        if (preg_match($pattern, $responseHeader->getFieldValue())) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" DOES NOT MATCH regex "%s"',
                $header,
                $pattern
            ));
        }
        $this->assertFalse((bool) preg_match($pattern, $responseHeader->getFieldValue()));
    }

    /**
     * Assert that response is a redirect
     */
    public function assertRedirect()
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (false === $responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Failed asserting response is a redirect'
            );
        }
        $this->assertNotEquals(false, $responseHeader);
    }

    /**
     * Assert that response is NOT a redirect
     */
    public function assertNotRedirect()
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (false !== $responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response is NOT a redirect, actual redirection is "%s"',
                $responseHeader->getFieldValue()
            ));
        }
        $this->assertFalse($responseHeader);
    }

    /**
     * Assert that response redirects to given URL
     *
     * @param  string $url
     */
    public function assertRedirectTo($url)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Failed asserting response is a redirect'
            );
        }
        if ($url != $responseHeader->getFieldValue()) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response redirects to "%s", actual redirection is "%s"',
                $url,
                $responseHeader->getFieldValue()
            ));
        }
        $this->assertEquals($url, $responseHeader->getFieldValue());
    }

    /**
     * Assert that response does not redirect to given URL
     *
     * @param  string $url
     */
    public function assertNotRedirectTo($url)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Failed asserting response is a redirect'
            );
        }
        if ($url == $responseHeader->getFieldValue()) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response redirects to "%s"',
                $url
            ));
        }
        $this->assertNotEquals($url, $responseHeader->getFieldValue());
    }

    /**
     * Assert that redirect location matches pattern
     *
     * @param  string $pattern
     */
    public function assertRedirectRegex($pattern)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Failed asserting response is a redirect'
            );
        }
        if (!preg_match($pattern, $responseHeader->getFieldValue())) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response redirects to URL MATCHING "%s", actual redirection is "%s"',
                $pattern,
                $responseHeader->getFieldValue()
            ));
        }
        $this->assertTrue((bool) preg_match($pattern, $responseHeader->getFieldValue()));
    }

    /**
     * Assert that redirect location does not match pattern
     *
     * @param  string $pattern
     */
    public function assertNotRedirectRegex($pattern)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if (!$responseHeader) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Failed asserting response is a redirect'
            );
        }
        if (preg_match($pattern, $responseHeader->getFieldValue())) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response DOES NOT redirect to URL MATCHING "%s"',
                $pattern
            ));
        }
        $this->assertFalse((bool) preg_match($pattern, $responseHeader->getFieldValue()));
    }

    /**
     * Register XPath namespaces
     *
     * @param   array $xpathNamespaces
     */
    public function registerXpathNamespaces(array $xpathNamespaces)
    {
        $this->xpathNamespaces = $xpathNamespaces;
    }

    /**
     * Execute a DOM/XPath query
     *
     * @param  string $path
     * @param  bool $useXpath
     * @return array
     */
    private function query($path, $useXpath = false)
    {
        $response = $this->getResponse();
        $dom      = new Dom\Query($response->getContent());
        if ($useXpath) {
            $dom->registerXpathNamespaces($this->xpathNamespaces);
            return $dom->queryXpath($path);
        }
        return $dom->execute($path);
    }

    /**
     * Execute a xpath query
     *
     * @param  string $path
     * @return array
     */
    private function xpathQuery($path)
    {
        return $this->query($path, true);
    }

    /**
     * Count the dom query executed
     *
     * @param  string $path
     * @return int
     */
    private function queryCount($path)
    {
        return count($this->query($path, false));
    }

    /**
     * Count the dom query executed
     *
     * @param  string $path
     * @return int
     */
    private function xpathQueryCount($path)
    {
        return count($this->xpathQuery($path));
    }

    /**
     * Assert against DOM/XPath selection
     *
     * @param string $path
     * @param bool $useXpath
     */
    private function queryAssertion($path, $useXpath = false)
    {
        $method = $useXpath ? 'xpathQueryCount' : 'queryCount';
        $match = $this->$method($path);
        if (!$match > 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }
        $this->assertTrue($match > 0);
    }

    /**
     * Assert against DOM selection
     *
     * @param  string $path CSS selector path
     */
    public function assertQuery($path)
    {
        $this->queryAssertion($path, false);
    }

    /**
     * Assert against XPath selection
     *
     * @param  string $path XPath path
     */
    public function assertXpathQuery($path)
    {
        $this->queryAssertion($path, true);
    }

    /**
     * Assert against DOM/XPath selection
     *
     * @param string $path CSS selector path
     * @param bool $useXpath
     */
    private function notQueryAssertion($path, $useXpath = false)
    {
        $method = $useXpath ? 'xpathQueryCount' : 'queryCount';
        $match  = $this->$method($path);
        if ($match != 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT EXIST',
                $path
            ));
        }
        $this->assertEquals(0, $match);
    }

    /**
     * Assert against DOM selection
     *
     * @param  string $path CSS selector path
     */
    public function assertNotQuery($path)
    {
        $this->notQueryAssertion($path, false);
    }

    /**
     * Assert against XPath selection
     *
     * @param  string $path XPath path
     */
    public function assertNotXpathQuery($path)
    {
        $this->notQueryAssertion($path, true);
    }

    /**
     * Assert against DOM/XPath selection; should contain exact number of nodes
     *
     * @param string $path CSS selector path
     * @param string $count Number of nodes that should match
     * @param bool $useXpath
     */
    private function queryCountAssertion($path, $count, $useXpath = false)
    {
        $method = $useXpath ? 'xpathQueryCount' : 'queryCount';
        $match = $this->$method($path);
        if ($match != $count) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s OCCURS EXACTLY %d times, actually occurs %d times',
                $path,
                $count,
                $match
            ));
        }
        $this->assertEquals($match, $count);
    }

    /**
     * Assert against DOM selection; should contain exact number of nodes
     *
     * @param  string $path CSS selector path
     * @param  string $count Number of nodes that should match
     */
    public function assertQueryCount($path, $count)
    {
        $this->queryCountAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should contain exact number of nodes
     *
     * @param  string $path XPath path
     * @param  string $count Number of nodes that should match
     */
    public function assertXpathQueryCount($path, $count)
    {
        $this->queryCountAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; should NOT contain exact number of nodes
     *
     * @param  string $path CSS selector path
     * @param  string $count Number of nodes that should NOT match
     * @param bool $useXpath
     */
    private function notQueryCountAssertion($path, $count, $useXpath = false)
    {
        $method = $useXpath ? 'xpathQueryCount' : 'queryCount';
        $match = $this->$method($path);
        if ($match == $count) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT OCCUR EXACTLY %d times',
                $path,
                $count
            ));
        }
        $this->assertNotEquals($match, $count);
    }

    /**
     * Assert against DOM selection; should NOT contain exact number of nodes
     *
     * @param  string $path CSS selector path
     * @param  string $count Number of nodes that should NOT match
     */
    public function assertNotQueryCount($path, $count)
    {
        $this->notQueryCountAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should NOT contain exact number of nodes
     *
     * @param  string $path XPath path
     * @param  string $count Number of nodes that should NOT match
     */
    public function assertNotXpathQueryCount($path, $count)
    {
        $this->notQueryCountAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; should contain at least this number of nodes
     *
     * @param string $path CSS selector path
     * @param string $count Minimum number of nodes that should match
     * @param bool $useXpath
     */
    private function queryCountMinAssertion($path, $count, $useXpath = false)
    {
        $method = $useXpath ? 'xpathQueryCount' : 'queryCount';
        $match = $this->$method($path);
        if ($match < $count) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s OCCURS AT LEAST %d times, actually occurs %d times',
                $path,
                $count,
                $match
            ));
        }
        $this->assertTrue($match >= $count);
    }

    /**
     * Assert against DOM selection; should contain at least this number of nodes
     *
     * @param  string $path CSS selector path
     * @param  string $count Minimum number of nodes that should match
     */
    public function assertQueryCountMin($path, $count)
    {
        $this->queryCountMinAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should contain at least this number of nodes
     *
     * @param  string $path XPath path
     * @param  string $count Minimum number of nodes that should match
     */
    public function assertXpathQueryCountMin($path, $count)
    {
        $this->queryCountMinAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; should contain no more than this number of nodes
     *
     * @param  string $path CSS selector path
     * @param  string $count Maximum number of nodes that should match
     * @param bool $useXpath
     */
    private function queryCountMaxAssertion($path, $count, $useXpath = false)
    {
        $method = $useXpath ? 'xpathQueryCount' : 'queryCount';
        $match = $this->$method($path);
        if ($match > $count) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s OCCURS AT MOST %d times, actually occurs %d times',
                $path,
                $count,
                $match
            ));
        }
        $this->assertTrue($match <= $count);
    }

    /**
     * Assert against DOM selection; should contain no more than this number of nodes
     *
     * @param  string $path CSS selector path
     * @param  string $count Maximum number of nodes that should match
     */
    public function assertQueryCountMax($path, $count)
    {
        $this->queryCountMaxAssertion($path, $count, false);
    }

    /**
     * Assert against XPath selection; should contain no more than this number of nodes
     *
     * @param  string $path XPath path
     * @param  string $count Maximum number of nodes that should match
     */
    public function assertXpathQueryCountMax($path, $count)
    {
        $this->queryCountMaxAssertion($path, $count, true);
    }

    /**
     * Assert against DOM/XPath selection; node should contain content
     *
     * @param  string $path CSS selector path
     * @param  string $match content that should be contained in matched nodes
     * @param bool $useXpath
     */
    private function queryContentContainsAssertion($path, $match, $useXpath = false)
    {
        $result = $this->query($path, $useXpath);
        if ($result->count() == 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }
        foreach ($result as $node) {
            if ($node->nodeValue == $match) {
                $this->assertEquals($match, $node->nodeValue);
                return;
            }
        }
        throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
            'Failed asserting node denoted by %s CONTAINS content "%s"',
            $path,
            $match
        ));
    }

    /**
     * Assert against DOM selection; node should contain content
     *
     * @param  string $path CSS selector path
     * @param  string $match content that should be contained in matched nodes
     */
    public function assertQueryContentContains($path, $match)
    {
        $this->queryContentContainsAssertion($path, $match, false);
    }

    /**
     * Assert against XPath selection; node should contain content
     *
     * @param  string $path XPath path
     * @param  string $match content that should be contained in matched nodes
     */
    public function assertXpathQueryContentContains($path, $match)
    {
        $this->queryContentContainsAssertion($path, $match, true);
    }

    /**
     * Assert against DOM/XPath selection; node should NOT contain content
     *
     * @param  string $path CSS selector path
     * @param  string $match content that should NOT be contained in matched nodes
     * @param bool $useXpath
     */
    private function notQueryContentContainsAssertion($path, $match, $useXpath = false)
    {
        $result = $this->query($path, $useXpath);
        if ($result->count() == 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }
        foreach ($result as $node) {
            if ($node->nodeValue == $match) {
                throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content "%s"',
                    $path,
                    $match
                ));
            }
        }
        $currentValue = $node->nodeValue;
        $this->assertNotEquals($currentValue, $match);
    }

    /**
     * Assert against DOM selection; node should NOT contain content
     *
     * @param  string $path CSS selector path
     * @param  string $match content that should NOT be contained in matched nodes
     */
    public function assertNotQueryContentContains($path, $match)
    {
        $this->notQueryContentContainsAssertion($path, $match, false);
    }

    /**
     * Assert against XPath selection; node should NOT contain content
     *
     * @param  string $path XPath path
     * @param  string $match content that should NOT be contained in matched nodes
     */
    public function assertNotXpathQueryContentContains($path, $match)
    {
        $this->notQueryContentContainsAssertion($path, $match, true);
    }

    /**
     * Assert against DOM/XPath selection; node should match content
     *
     * @param  string $path CSS selector path
     * @param  string $pattern Pattern that should be contained in matched nodes
     * @param bool $useXpath
     */
    private function queryContentRegexAssertion($path, $pattern, $useXpath = false)
    {
        $result = $this->query($path, $useXpath);
        if ($result->count() == 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }
        if (!preg_match($pattern, $result->current()->nodeValue)) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node denoted by %s CONTAINS content MATCHING "%s", actual content is "%s"',
                $path,
                $pattern,
                $result->current()->nodeValue
            ));
        }
        $this->assertTrue((bool) preg_match($pattern, $result->current()->nodeValue));
    }

    /**
     * Assert against DOM selection; node should match content
     *
     * @param  string $path CSS selector path
     * @param  string $pattern Pattern that should be contained in matched nodes
     */
    public function assertQueryContentRegex($path, $pattern)
    {
        $this->queryContentRegexAssertion($path, $pattern, false);
    }

    /**
     * Assert against XPath selection; node should match content
     *
     * @param  string $path XPath path
     * @param  string $pattern Pattern that should be contained in matched nodes
     */
    public function assertXpathQueryContentRegex($path, $pattern)
    {
        $this->queryContentRegexAssertion($path, $pattern, true);
    }

    /**
     * Assert against DOM/XPath selection; node should NOT match content
     *
     * @param string $path CSS selector path
     * @param string $pattern pattern that should NOT be contained in matched nodes
     * @param bool $useXpath
     */
    private function notQueryContentRegexAssertion($path, $pattern, $useXpath = false)
    {
        $result = $this->query($path, $useXpath);
        if ($result->count() == 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }
        if (preg_match($pattern, $result->current()->nodeValue)) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content MATCHING "%s"',
                $path,
                $pattern
            ));
        }
        $this->assertFalse((bool) preg_match($pattern, $result->current()->nodeValue));
    }

    /**
     * Assert against DOM selection; node should NOT match content
     *
     * @param  string $path CSS selector path
     * @param  string $pattern pattern that should NOT be contained in matched nodes
     */
    public function assertNotQueryContentRegex($path, $pattern)
    {
        $this->notQueryContentRegexAssertion($path, $pattern, false);
    }

    /**
     * Assert against XPath selection; node should NOT match content
     *
     * @param  string $path XPath path
     * @param  string $pattern pattern that should NOT be contained in matched nodes
     */
    public function assertNotXpathQueryContentRegex($path, $pattern)
    {
        $this->notQueryContentRegexAssertion($path, $pattern, true);
    }
}
