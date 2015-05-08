<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Extension\Thread;

use Zend\Feed\Reader\Extension;

/**
*/
class Entry extends Extension\AbstractEntry
{
    /**
     * Get the "in-reply-to" value
     *
     * @return string
     */
    public function getInReplyTo()
    {
        // TODO: to be implemented
    }

    // TODO: Implement "replies" and "updated" constructs from standard

    /**
     * Get the total number of threaded responses (i.e comments)
     *
     * @return int|null
     */
    public function getCommentCount()
    {
        return $this->getData('total');
    }

    /**
     * Get the entry data specified by name
     *
     * @param  string $name
     * @return mixed|null
     */
    protected function getData($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $data = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/thread10:' . $name . ')');

        if (!$data) {
            $data = null;
        }

        $this->data[$name] = $data;

        return $data;
    }

    /**
     * Register Atom Thread Extension 1.0 namespace
     *
     * @return void
     */
    protected function registerNamespaces()
    {
        $this->xpath->registerNamespace('thread10', 'http://purl.org/syndication/thread/1.0');
    }
}
