<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer\Renderer\Feed;

use DateTime;
use DOMDocument;
use DOMElement;
use Zend\Feed\Uri;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Renderer;
use Zend\Feed\Writer\Version;

/**
*/
class Rss extends Renderer\AbstractRenderer implements Renderer\RendererInterface
{
    /**
     * Constructor
     *
     * @param  Writer\Feed $container
     */
    public function __construct(Writer\Feed $container)
    {
        parent::__construct($container);
    }

    /**
     * Render RSS feed
     *
     * @return self
     */
    public function render()
    {
        $this->dom = new DOMDocument('1.0', $this->container->getEncoding());
        $this->dom->formatOutput = true;
        $this->dom->substituteEntities = false;
        $rss = $this->dom->createElement('rss');
        $this->setRootElement($rss);
        $rss->setAttribute('version', '2.0');

        $channel = $this->dom->createElement('channel');
        $rss->appendChild($channel);
        $this->dom->appendChild($rss);
        $this->_setLanguage($this->dom, $channel);
        $this->_setBaseUrl($this->dom, $channel);
        $this->_setTitle($this->dom, $channel);
        $this->_setDescription($this->dom, $channel);
        $this->_setImage($this->dom, $channel);
        $this->_setDateCreated($this->dom, $channel);
        $this->_setDateModified($this->dom, $channel);
        $this->_setLastBuildDate($this->dom, $channel);
        $this->_setGenerator($this->dom, $channel);
        $this->_setLink($this->dom, $channel);
        $this->_setAuthors($this->dom, $channel);
        $this->_setCopyright($this->dom, $channel);
        $this->_setCategories($this->dom, $channel);

        foreach ($this->extensions as $ext) {
            $ext->setType($this->getType());
            $ext->setRootElement($this->getRootElement());
            $ext->setDOMDocument($this->getDOMDocument(), $channel);
            $ext->render();
        }

        foreach ($this->container as $entry) {
            if ($this->getDataContainer()->getEncoding()) {
                $entry->setEncoding($this->getDataContainer()->getEncoding());
            }
            if ($entry instanceof Writer\Entry) {
                $renderer = new Renderer\Entry\Rss($entry);
            } else {
                continue;
            }
            if ($this->ignoreExceptions === true) {
                $renderer->ignoreExceptions();
            }
            $renderer->setType($this->getType());
            $renderer->setRootElement($this->dom->documentElement);
            $renderer->render();
            $element = $renderer->getElement();
            $imported = $this->dom->importNode($element, true);
            $channel->appendChild($imported);
        }
        return $this;
    }

    /**
     * Set feed language
     *
     * @param DOMDocument $dom
     * @param DOMElement $root
     * @return void
     */
    protected function _setLanguage(DOMDocument $dom, DOMElement $root)
    {
        $lang = $this->getDataContainer()->getLanguage();
        if (!$lang) {
            return;
        }
        $language = $dom->createElement('language');
        $root->appendChild($language);
        $language->nodeValue = $lang;
    }

    /**
     * Set feed title
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setTitle(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getTitle()) {
            $message = 'RSS 2.0 feed elements MUST contain exactly one'
            . ' title element but a title has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }

        $title = $dom->createElement('title');
        $root->appendChild($title);
        $text = $dom->createTextNode($this->getDataContainer()->getTitle());
        $title->appendChild($text);
    }

    /**
     * Set feed description
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setDescription(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDescription()) {
            $message = 'RSS 2.0 feed elements MUST contain exactly one'
            . ' description element but one has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        $subtitle = $dom->createElement('description');
        $root->appendChild($subtitle);
        $text = $dom->createTextNode($this->getDataContainer()->getDescription());
        $subtitle->appendChild($text);
    }

    /**
     * Set date feed was last modified
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setDateModified(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDateModified()) {
            return;
        }

        $updated = $dom->createElement('pubDate');
        $root->appendChild($updated);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getDateModified()->format(DateTime::RSS)
        );
        $updated->appendChild($text);
    }

    /**
     * Set feed generator string
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setGenerator(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getGenerator()) {
            $this->getDataContainer()->setGenerator('Zend_Feed_Writer',
                Version::VERSION, 'http://framework.zend.com');
        }

        $gdata = $this->getDataContainer()->getGenerator();
        $generator = $dom->createElement('generator');
        $root->appendChild($generator);
        $name = $gdata['name'];
        if (array_key_exists('version', $gdata)) {
            $name .= ' ' . $gdata['version'];
        }
        if (array_key_exists('uri', $gdata)) {
            $name .= ' (' . $gdata['uri'] . ')';
        }
        $text = $dom->createTextNode($name);
        $generator->appendChild($text);
    }

    /**
     * Set link to feed
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setLink(DOMDocument $dom, DOMElement $root)
    {
        $value = $this->getDataContainer()->getLink();
        if (!$value) {
            $message = 'RSS 2.0 feed elements MUST contain exactly one'
            . ' link element but one has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        $link = $dom->createElement('link');
        $root->appendChild($link);
        $text = $dom->createTextNode($value);
        $link->appendChild($text);
        if (!Uri::factory($value)->isValid()) {
            $link->setAttribute('isPermaLink', 'false');
        }
    }

    /**
     * Set feed authors
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setAuthors(DOMDocument $dom, DOMElement $root)
    {
        $authors = $this->getDataContainer()->getAuthors();
        if (!$authors || empty($authors)) {
            return;
        }
        foreach ($authors as $data) {
            $author = $this->dom->createElement('author');
            $name = $data['name'];
            if (array_key_exists('email', $data)) {
                $name = $data['email'] . ' (' . $data['name'] . ')';
            }
            $text = $dom->createTextNode($name);
            $author->appendChild($text);
            $root->appendChild($author);
        }
    }

    /**
     * Set feed copyright
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setCopyright(DOMDocument $dom, DOMElement $root)
    {
        $copyright = $this->getDataContainer()->getCopyright();
        if (!$copyright) {
            return;
        }
        $copy = $dom->createElement('copyright');
        $root->appendChild($copy);
        $text = $dom->createTextNode($copyright);
        $copy->appendChild($text);
    }

    /**
     * Set feed channel image
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setImage(DOMDocument $dom, DOMElement $root)
    {
        $image = $this->getDataContainer()->getImage();
        if (!$image) {
            return;
        }

        if (!isset($image['title']) || empty($image['title'])
            || !is_string($image['title'])
        ) {
            $message = 'RSS 2.0 feed images must include a title';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }

        if (empty($image['link']) || !is_string($image['link'])
            || !Uri::factory($image['link'])->isValid()
        ) {
            $message = 'Invalid parameter: parameter \'link\''
            . ' must be a non-empty string and valid URI/IRI';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }

        $img   = $dom->createElement('image');
        $root->appendChild($img);

        $url   = $dom->createElement('url');
        $text  = $dom->createTextNode($image['uri']);
        $url->appendChild($text);

        $title = $dom->createElement('title');
        $text  = $dom->createTextNode($image['title']);
        $title->appendChild($text);

        $link  = $dom->createElement('link');
        $text  = $dom->createTextNode($image['link']);
        $link->appendChild($text);

        $img->appendChild($url);
        $img->appendChild($title);
        $img->appendChild($link);

        if (isset($image['height'])) {
            if (!ctype_digit((string) $image['height']) || $image['height'] > 400) {
                $message = 'Invalid parameter: parameter \'height\''
                         . ' must be an integer not exceeding 400';
                $exception = new Writer\Exception\InvalidArgumentException($message);
                if (!$this->ignoreExceptions) {
                    throw $exception;
                } else {
                    $this->exceptions[] = $exception;
                    return;
                }
            }
            $height = $dom->createElement('height');
            $text   = $dom->createTextNode($image['height']);
            $height->appendChild($text);
            $img->appendChild($height);
        }
        if (isset($image['width'])) {
            if (!ctype_digit((string) $image['width']) || $image['width'] > 144) {
                $message = 'Invalid parameter: parameter \'width\''
                         . ' must be an integer not exceeding 144';
                $exception = new Writer\Exception\InvalidArgumentException($message);
                if (!$this->ignoreExceptions) {
                    throw $exception;
                } else {
                    $this->exceptions[] = $exception;
                    return;
                }
            }
            $width = $dom->createElement('width');
            $text  = $dom->createTextNode($image['width']);
            $width->appendChild($text);
            $img->appendChild($width);
        }
        if (isset($image['description'])) {
            if (empty($image['description']) || !is_string($image['description'])) {
                $message = 'Invalid parameter: parameter \'description\''
                         . ' must be a non-empty string';
                $exception = new Writer\Exception\InvalidArgumentException($message);
                if (!$this->ignoreExceptions) {
                    throw $exception;
                } else {
                    $this->exceptions[] = $exception;
                    return;
                }
            }
            $desc = $dom->createElement('description');
            $text = $dom->createTextNode($image['description']);
            $desc->appendChild($text);
            $img->appendChild($desc);
        }
    }

    /**
     * Set date feed was created
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setDateCreated(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDateCreated()) {
            return;
        }
        if (!$this->getDataContainer()->getDateModified()) {
            $this->getDataContainer()->setDateModified(
                $this->getDataContainer()->getDateCreated()
            );
        }
    }

    /**
     * Set date feed last build date
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setLastBuildDate(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getLastBuildDate()) {
            return;
        }

        $lastBuildDate = $dom->createElement('lastBuildDate');
        $root->appendChild($lastBuildDate);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getLastBuildDate()->format(DateTime::RSS)
        );
        $lastBuildDate->appendChild($text);
    }

    /**
     * Set base URL to feed links
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setBaseUrl(DOMDocument $dom, DOMElement $root)
    {
        $baseUrl = $this->getDataContainer()->getBaseUrl();
        if (!$baseUrl) {
            return;
        }
        $root->setAttribute('xml:base', $baseUrl);
    }

    /**
     * Set feed categories
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setCategories(DOMDocument $dom, DOMElement $root)
    {
        $categories = $this->getDataContainer()->getCategories();
        if (!$categories) {
            return;
        }
        foreach ($categories as $cat) {
            $category = $dom->createElement('category');
            if (isset($cat['scheme'])) {
                $category->setAttribute('domain', $cat['scheme']);
            }
            $text = $dom->createTextNode($cat['term']);
            $category->appendChild($text);
            $root->appendChild($category);
        }
    }
}
