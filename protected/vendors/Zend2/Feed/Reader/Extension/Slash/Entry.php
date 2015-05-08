<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Extension\Slash;

use Zend\Feed\Reader\Extension;

/**
*/
class Entry extends Extension\AbstractEntry
{
    /**
     * Get the entry section
     *
     * @return string|null
     */
    public function getSection()
    {
        return $this->getData('section');
    }

    /**
     * Get the entry department
     *
     * @return string|null
     */
    public function getDepartment()
    {
        return $this->getData('department');
    }

    /**
     * Get the entry hit_parade
     *
     * @return array
     */
    public function getHitParade()
    {
        $name = 'hit_parade';

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        $stringParade = $this->getData($name);
        $hitParade    = array();

        if (!empty($stringParade)) {
            $stringParade = explode(',', $stringParade);

            foreach ($stringParade as $hit)
                $hitParade[] = $hit + 0; //cast to integer
        }

        $this->data[$name] = $hitParade;
        return $hitParade;
    }

    /**
     * Get the entry comments
     *
     * @return int
     */
    public function getCommentCount()
    {
        $name = 'comments';

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        $comments = $this->getData($name, 'string');

        if (!$comments) {
            $this->data[$name] = null;
            return $this->data[$name];
        }

        return $comments;
    }

    /**
     * Get the entry data specified by name
     * @param string $name
     * @param string $type
     *
     * @return mixed|null
     */
    protected function getData($name, $type = 'string')
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $data = $this->xpath->evaluate($type . '(' . $this->getXpathPrefix() . '/slash10:' . $name . ')');

        if (!$data) {
            $data = null;
        }

        $this->data[$name] = $data;

        return $data;
    }

    /**
     * Register Slash namespaces
     *
     * @return void
     */
    protected function registerNamespaces()
    {
        $this->xpath->registerNamespace('slash10', 'http://purl.org/rss/1.0/modules/slash/');
    }
}
