<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Extension\Content;

use Zend\Feed\Reader;
use Zend\Feed\Reader\Extension;

class Entry extends Extension\AbstractEntry
{

    public function getContent()
    {
        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
            && $this->getType() !== Reader\Reader::TYPE_RSS_090
        ) {
            $content = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/content:encoded)');
        } else {
            $content = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/content:encoded)');
        }
        return $content;
    }

    /**
     * Register RSS Content Module namespace
     */
    protected function registerNamespaces()
    {
        $this->xpath->registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
    }
}
