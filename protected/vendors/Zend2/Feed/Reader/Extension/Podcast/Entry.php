<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Extension\Podcast;

use Zend\Feed\Reader\Extension;

/**
*/
class Entry extends Extension\AbstractEntry
{
    /**
     * Get the entry author
     *
     * @return string
     */
    public function getCastAuthor()
    {
        if (isset($this->data['author'])) {
            return $this->data['author'];
        }

        $author = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:author)');

        if (!$author) {
            $author = null;
        }

        $this->data['author'] = $author;

        return $this->data['author'];
    }

    /**
     * Get the entry block
     *
     * @return string
     */
    public function getBlock()
    {
        if (isset($this->data['block'])) {
            return $this->data['block'];
        }

        $block = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:block)');

        if (!$block) {
            $block = null;
        }

        $this->data['block'] = $block;

        return $this->data['block'];
    }

    /**
     * Get the entry duration
     *
     * @return string
     */
    public function getDuration()
    {
        if (isset($this->data['duration'])) {
            return $this->data['duration'];
        }

        $duration = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:duration)');

        if (!$duration) {
            $duration = null;
        }

        $this->data['duration'] = $duration;

        return $this->data['duration'];
    }

    /**
     * Get the entry explicit
     *
     * @return string
     */
    public function getExplicit()
    {
        if (isset($this->data['explicit'])) {
            return $this->data['explicit'];
        }

        $explicit = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:explicit)');

        if (!$explicit) {
            $explicit = null;
        }

        $this->data['explicit'] = $explicit;

        return $this->data['explicit'];
    }

    /**
     * Get the entry keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        if (isset($this->data['keywords'])) {
            return $this->data['keywords'];
        }

        $keywords = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:keywords)');

        if (!$keywords) {
            $keywords = null;
        }

        $this->data['keywords'] = $keywords;

        return $this->data['keywords'];
    }

    /**
     * Get the entry subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        if (isset($this->data['subtitle'])) {
            return $this->data['subtitle'];
        }

        $subtitle = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:subtitle)');

        if (!$subtitle) {
            $subtitle = null;
        }

        $this->data['subtitle'] = $subtitle;

        return $this->data['subtitle'];
    }

    /**
     * Get the entry summary
     *
     * @return string
     */
    public function getSummary()
    {
        if (isset($this->data['summary'])) {
            return $this->data['summary'];
        }

        $summary = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:summary)');

        if (!$summary) {
            $summary = null;
        }

        $this->data['summary'] = $summary;

        return $this->data['summary'];
    }

    /**
     * Register iTunes namespace
     *
     */
    protected function registerNamespaces()
    {
        $this->xpath->registerNamespace('itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
    }
}
