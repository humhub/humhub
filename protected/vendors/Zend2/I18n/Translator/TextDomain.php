<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Translator;

use ArrayObject;
use Zend\I18n\Exception;
use Zend\I18n\Translator\Plural\Rule as PluralRule;

/**
 * Text domain.
 */
class TextDomain extends ArrayObject
{
    /**
     * Plural rule.
     *
     * @var PluralRule
     */
    protected $pluralRule;

    /**
     * Set the plural rule
     *
     * @param  PluralRule $rule
     * @return TextDomain
     */
    public function setPluralRule(PluralRule $rule)
    {
        $this->pluralRule = $rule;
        return $this;
    }

    /**
     * Get the plural rule.
     *
     * Lazy loads a default rule if none already registered
     *
     * @return PluralRule
     */
    public function getPluralRule()
    {
        if ($this->pluralRule === null) {
            $this->setPluralRule(PluralRule::fromString('nplurals=2; plural=n != 1;'));
        }

        return $this->pluralRule;
    }

    /**
     * Merge another text domain with the current one.
     *
     * The plural rule of both text domains must be compatible for a successful
     * merge. We are only validating the number of plural forms though, as the
     * same rule could be made up with different expression.
     *
     * @param  TextDomain $textDomain
     * @return TextDomain
     * @throws Exception\RuntimeException
     */
    public function merge(TextDomain $textDomain)
    {
        if ($this->getPluralRule()->getNumPlurals() !== $textDomain->getPluralRule()->getNumPlurals()) {
            throw new Exception\RuntimeException('Plural rule of merging text domain is not compatible with the current one');
        }

        $this->exchangeArray(
            array_replace(
                $this->getArrayCopy(),
                $textDomain->getArrayCopy()
            )
        );

        return $this;
    }
}
