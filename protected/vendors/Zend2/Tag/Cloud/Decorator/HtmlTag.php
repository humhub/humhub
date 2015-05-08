<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Tag\Cloud\Decorator;

use Zend\Tag\Cloud\Decorator\Exception\InvalidArgumentException;
use Zend\Tag\ItemList;

/**
 * Simple HTML decorator for tags
 */
class HtmlTag extends AbstractTag
{
    /**
     * List of tags which get assigned to the inner element instead of
     * font-sizes.
     *
     * @var array
     */
    protected $classList = null;

    /**
     * Unit for the fontsize
     *
     * @var string
     */
    protected $fontSizeUnit = 'px';

    /**
     * Allowed fontsize units
     *
     * @var array
     */
    protected $allowedFontSizeUnits = array('em', 'ex', 'px', 'in', 'cm', 'mm', 'pt', 'pc', '%');

    /**
     * List of HTML tags
     *
     * @var array
     */
    protected $htmlTags = array(
        'li'
    );

    /**
     * Maximum fontsize
     *
     * @var int
     */
    protected $maxFontSize = 20;

    /**
     * Minimum fontsize
     *
     * @var int
     */
    protected $minFontSize = 10;

    /**
     * Set a list of classes to use instead of fontsizes
     *
     * @param  array $classList
     * @throws InvalidArgumentException When the classlist is empty
     * @throws InvalidArgumentException When the classlist contains an invalid classname
     * @return HTMLTag
     */
    public function setClassList(array $classList = null)
    {
        if (is_array($classList)) {
            if (count($classList) === 0) {
                throw new InvalidArgumentException('Classlist is empty');
            }

            foreach ($classList as $class) {
                if (!is_string($class)) {
                    throw new InvalidArgumentException('Classlist contains an invalid classname');
                }
            }
        }

        $this->classList = $classList;
        return $this;
    }

    /**
     * Get class list
     *
     * @return array
     */
    public function getClassList()
    {
        return $this->classList;
    }

    /**
     * Set the font size unit
     *
     * Possible values are: em, ex, px, in, cm, mm, pt, pc and %
     *
     * @param  string $fontSizeUnit
     * @throws InvalidArgumentException When an invalid fontsize unit is specified
     * @return HTMLTag
     */
    public function setFontSizeUnit($fontSizeUnit)
    {
        if (!in_array($fontSizeUnit, $this->allowedFontSizeUnits)) {
            throw new InvalidArgumentException('Invalid fontsize unit specified');
        }

        $this->fontSizeUnit = (string) $fontSizeUnit;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve font size unit
     *
     * @return string
     */
    public function getFontSizeUnit()
    {
        return $this->fontSizeUnit;
    }
     /**
     * Set the HTML tags surrounding the <a> element
     *
     * @param  array $htmlTags
     * @return HTMLTag
     */
    public function setHTMLTags(array $htmlTags)
    {
        $this->htmlTags = $htmlTags;
        return $this;
    }

    /**
     * Get HTML tags map
     *
     * @return array
     */
    public function getHTMLTags()
    {
        return $this->htmlTags;
    }

    /**
     * Set maximum font size
     *
     * @param  int $maxFontSize
     * @throws InvalidArgumentException When fontsize is not numeric
     * @return HTMLTag
     */
    public function setMaxFontSize($maxFontSize)
    {
        if (!is_numeric($maxFontSize)) {
            throw new InvalidArgumentException('Fontsize must be numeric');
        }

        $this->maxFontSize = (int) $maxFontSize;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve maximum font size
     *
     * @return int
     */
    public function getMaxFontSize()
    {
        return $this->maxFontSize;
    }

    /**
     * Set minimum font size
     *
     * @param  int $minFontSize
     * @throws InvalidArgumentException When fontsize is not numeric
     * @return HTMLTag
     */
    public function setMinFontSize($minFontSize)
    {
        if (!is_numeric($minFontSize)) {
            throw new InvalidArgumentException('Fontsize must be numeric');
        }

        $this->minFontSize = (int) $minFontSize;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve minimum font size
     *
     * @return int
     */
    public function getMinFontSize()
    {
        return $this->minFontSize;
    }

    /**
     * Defined by Tag
     *
     * @param  ItemList $tags
     * @throws InvalidArgumentException
     * @return array
     */
    public function render($tags)
    {
        if (!$tags instanceof ItemList) {
            throw new InvalidArgumentException(sprintf(
                'HtmlTag::render() expects a Zend\Tag\ItemList argument; received "%s"',
                (is_object($tags) ? get_class($tags) : gettype($tags))
            ));
        }
        if (null === ($weightValues = $this->getClassList())) {
            $weightValues = range($this->getMinFontSize(), $this->getMaxFontSize());
        }

        $tags->spreadWeightValues($weightValues);

        $result = array();

        $escaper = $this->getEscaper();
        foreach ($tags as $tag) {
            if (null === ($classList = $this->getClassList())) {
                $attribute = sprintf('style="font-size: %d%s;"', $tag->getParam('weightValue'), $this->getFontSizeUnit());
            } else {
                $attribute = sprintf('class="%s"', $escaper->escapeHtmlAttr($tag->getParam('weightValue')));
            }

            $tagHTML  = sprintf('<a href="%s" %s>%s</a>', $escaper->escapeHtml($tag->getParam('url')), $attribute, $escaper->escapeHtml($tag->getTitle()));
            $tagHTML  = $this->wrapTag($tagHTML);
            $result[] = $tagHTML;
        }

        return $result;
    }
}
