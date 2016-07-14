<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node\Schema;

use Zend\Ldap;
use Zend\Ldap\Converter;
use Zend\Ldap\Node;

/**
 * Zend\Ldap\Node\Schema\OpenLDAP provides a simple data-container for the Schema node of
 * an OpenLDAP server.
 */
class OpenLdap extends Node\Schema
{
    /**
     * The attribute Types
     *
     * @var array
     */
    protected $attributeTypes = null;

    /**
     * The object classes
     *
     * @var array
     */
    protected $objectClasses = null;

    /**
     * The LDAP syntaxes
     *
     * @var array
     */
    protected $ldapSyntaxes = null;

    /**
     * The matching rules
     *
     * @var array
     */
    protected $matchingRules = null;

    /**
     * The matching rule use
     *
     * @var array
     */
    protected $matchingRuleUse = null;

    /**
     * Parses the schema
     *
     * @param \Zend\Ldap\Dn   $dn
     * @param \Zend\Ldap\Ldap $ldap
     * @return OpenLdap Provides a fluid interface
     */
    protected function parseSchema(Ldap\Dn $dn, Ldap\Ldap $ldap)
    {
        parent::parseSchema($dn, $ldap);
        $this->loadAttributeTypes();
        $this->loadLdapSyntaxes();
        $this->loadMatchingRules();
        $this->loadMatchingRuleUse();
        $this->loadObjectClasses();
        return $this;
    }

    /**
     * Gets the attribute Types
     *
     * @return array
     */
    public function getAttributeTypes()
    {
        return $this->attributeTypes;
    }

    /**
     * Gets the object classes
     *
     * @return array
     */
    public function getObjectClasses()
    {
        return $this->objectClasses;
    }

    /**
     * Gets the LDAP syntaxes
     *
     * @return array
     */
    public function getLdapSyntaxes()
    {
        return $this->ldapSyntaxes;
    }

    /**
     * Gets the matching rules
     *
     * @return array
     */
    public function getMatchingRules()
    {
        return $this->matchingRules;
    }

    /**
     * Gets the matching rule use
     *
     * @return array
     */
    public function getMatchingRuleUse()
    {
        return $this->matchingRuleUse;
    }

    /**
     * Loads the attribute Types
     *
     * @return void
     */
    protected function loadAttributeTypes()
    {
        $this->attributeTypes = array();
        foreach ($this->getAttribute('attributeTypes') as $value) {
            $val                                   = $this->parseAttributeType($value);
            $val                                   = new AttributeType\OpenLdap($val);
            $this->attributeTypes[$val->getName()] = $val;
        }
        foreach ($this->attributeTypes as $val) {
            if (count($val->sup) > 0) {
                $this->resolveInheritance($val, $this->attributeTypes);
            }
            foreach ($val->aliases as $alias) {
                $this->attributeTypes[$alias] = $val;
            }
        }
        ksort($this->attributeTypes, SORT_STRING);
    }

    /**
     * Parses an attributeType value
     *
     * @param  string $value
     * @return array
     */
    protected function parseAttributeType($value)
    {
        $attributeType = array(
            'oid'                  => null,
            'name'                 => null,
            'desc'                 => null,
            'obsolete'             => false,
            'sup'                  => null,
            'equality'             => null,
            'ordering'             => null,
            'substr'               => null,
            'syntax'               => null,
            'max-length'           => null,
            'single-value'         => false,
            'collective'           => false,
            'no-user-modification' => false,
            'usage'                => 'userApplications',
            '_string'              => $value,
            '_parents'             => array());

        $tokens               = $this->tokenizeString($value);
        $attributeType['oid'] = array_shift($tokens); // first token is the oid
        $this->parseLdapSchemaSyntax($attributeType, $tokens);

        if (array_key_exists('syntax', $attributeType)) {
            // get max length from syntax
            if (preg_match('/^(.+){(\d+)}$/', $attributeType['syntax'], $matches)) {
                $attributeType['syntax']     = $matches[1];
                $attributeType['max-length'] = $matches[2];
            }
        }

        $this->ensureNameAttribute($attributeType);

        return $attributeType;
    }

    /**
     * Loads the object classes
     *
     * @return void
     */
    protected function loadObjectClasses()
    {
        $this->objectClasses = array();
        foreach ($this->getAttribute('objectClasses') as $value) {
            $val                                  = $this->parseObjectClass($value);
            $val                                  = new ObjectClass\OpenLdap($val);
            $this->objectClasses[$val->getName()] = $val;
        }
        foreach ($this->objectClasses as $val) {
            if (count($val->sup) > 0) {
                $this->resolveInheritance($val, $this->objectClasses);
            }
            foreach ($val->aliases as $alias) {
                $this->objectClasses[$alias] = $val;
            }
        }
        ksort($this->objectClasses, SORT_STRING);
    }

    /**
     * Parses an objectClasses value
     *
     * @param string $value
     * @return array
     */
    protected function parseObjectClass($value)
    {
        $objectClass = array(
            'oid'        => null,
            'name'       => null,
            'desc'       => null,
            'obsolete'   => false,
            'sup'        => array(),
            'abstract'   => false,
            'structural' => false,
            'auxiliary'  => false,
            'must'       => array(),
            'may'        => array(),
            '_string'    => $value,
            '_parents'   => array());

        $tokens             = $this->tokenizeString($value);
        $objectClass['oid'] = array_shift($tokens); // first token is the oid
        $this->parseLdapSchemaSyntax($objectClass, $tokens);

        $this->ensureNameAttribute($objectClass);

        return $objectClass;
    }

    /**
     * Resolves inheritance in objectClasses and attributes
     *
     * @param AbstractItem $node
     * @param array        $repository
     */
    protected function resolveInheritance(AbstractItem $node, array $repository)
    {
        $data    = $node->getData();
        $parents = $data['sup'];
        if ($parents === null || !is_array($parents) || count($parents) < 1) {
            return;
        }
        foreach ($parents as $parent) {
            if (!array_key_exists($parent, $repository)) {
                continue;
            }
            if (!array_key_exists('_parents', $data) || !is_array($data['_parents'])) {
                $data['_parents'] = array();
            }
            $data['_parents'][] = $repository[$parent];
        }
        $node->setData($data);
    }

    /**
     * Loads the LDAP syntaxes
     *
     * @return void
     */
    protected function loadLdapSyntaxes()
    {
        $this->ldapSyntaxes = array();
        foreach ($this->getAttribute('ldapSyntaxes') as $value) {
            $val                             = $this->parseLdapSyntax($value);
            $this->ldapSyntaxes[$val['oid']] = $val;
        }
        ksort($this->ldapSyntaxes, SORT_STRING);
    }

    /**
     * Parses an ldapSyntaxes value
     *
     * @param  string $value
     * @return array
     */
    protected function parseLdapSyntax($value)
    {
        $ldapSyntax = array(
            'oid'      => null,
            'desc'     => null,
            '_string'  => $value);

        $tokens            = $this->tokenizeString($value);
        $ldapSyntax['oid'] = array_shift($tokens); // first token is the oid
        $this->parseLdapSchemaSyntax($ldapSyntax, $tokens);

        return $ldapSyntax;
    }

    /**
     * Loads the matching rules
     *
     * @return void
     */
    protected function loadMatchingRules()
    {
        $this->matchingRules = array();
        foreach ($this->getAttribute('matchingRules') as $value) {
            $val                               = $this->parseMatchingRule($value);
            $this->matchingRules[$val['name']] = $val;
        }
        ksort($this->matchingRules, SORT_STRING);
    }

    /**
     * Parses a matchingRules value
     *
     * @param  string $value
     * @return array
     */
    protected function parseMatchingRule($value)
    {
        $matchingRule = array(
            'oid'      => null,
            'name'     => null,
            'desc'     => null,
            'obsolete' => false,
            'syntax'   => null,
            '_string'  => $value);

        $tokens              = $this->tokenizeString($value);
        $matchingRule['oid'] = array_shift($tokens); // first token is the oid
        $this->parseLdapSchemaSyntax($matchingRule, $tokens);

        $this->ensureNameAttribute($matchingRule);

        return $matchingRule;
    }

    /**
     * Loads the matching rule use
     *
     * @return void
     */
    protected function loadMatchingRuleUse()
    {
        $this->matchingRuleUse = array();
        foreach ($this->getAttribute('matchingRuleUse') as $value) {
            $val                                 = $this->parseMatchingRuleUse($value);
            $this->matchingRuleUse[$val['name']] = $val;
        }
        ksort($this->matchingRuleUse, SORT_STRING);
    }

    /**
     * Parses a matchingRuleUse value
     *
     * @param  string $value
     * @return array
     */
    protected function parseMatchingRuleUse($value)
    {
        $matchingRuleUse = array(
            'oid'      => null,
            'name'     => null,
            'desc'     => null,
            'obsolete' => false,
            'applies'  => array(),
            '_string'  => $value);

        $tokens                 = $this->tokenizeString($value);
        $matchingRuleUse['oid'] = array_shift($tokens); // first token is the oid
        $this->parseLdapSchemaSyntax($matchingRuleUse, $tokens);

        $this->ensureNameAttribute($matchingRuleUse);

        return $matchingRuleUse;
    }

    /**
     * Ensures that a name element is present and that it is single-values.
     *
     * @param array $data
     */
    protected function ensureNameAttribute(array &$data)
    {
        if (!array_key_exists('name', $data) || empty($data['name'])) {
            // force a name
            $data['name'] = $data['oid'];
        }
        if (is_array($data['name'])) {
            // make one name the default and put the other ones into aliases
            $aliases         = $data['name'];
            $data['name']    = array_shift($aliases);
            $data['aliases'] = $aliases;
        } else {
            $data['aliases'] = array();
        }
    }

    /**
     * Parse the given tokens into a data structure
     *
     * @param  array $data
     * @param  array $tokens
     * @return void
     */
    protected function parseLdapSchemaSyntax(array &$data, array $tokens)
    {
        // tokens that have no value associated
        $noValue = array('single-value',
                         'obsolete',
                         'collective',
                         'no-user-modification',
                         'abstract',
                         'structural',
                         'auxiliary');
        // tokens that can have multiple values
        $multiValue = array('must', 'may', 'sup');

        while (count($tokens) > 0) {
            $token = strtolower(array_shift($tokens));
            if (in_array($token, $noValue)) {
                $data[$token] = true; // single value token
            } else {
                $data[$token] = array_shift($tokens);
                // this one follows a string or a list if it is multivalued
                if ($data[$token] == '(') {
                    // this creates the list of values and cycles through the tokens
                    // until the end of the list is reached ')'
                    $data[$token] = array();

                    $tmp = array_shift($tokens);
                    while ($tmp) {
                        if ($tmp == ')') {
                            break;
                        }
                        if ($tmp != '$') {
                            $data[$token][] = Converter\Converter::fromLdap($tmp);
                        }
                        $tmp = array_shift($tokens);
                    }
                } else {
                    $data[$token] = Converter\Converter::fromLdap($data[$token]);
                }
                // create an array if the value should be multivalued but was not
                if (in_array($token, $multiValue) && !is_array($data[$token])) {
                    $data[$token] = array($data[$token]);
                }
            }
        }
    }

    /**
     * Tokenizes the given value into an array
     *
     * @param  string $value
     * @return array tokens
     */
    protected function tokenizeString($value)
    {
        $tokens  = array();
        $matches = array();
        // this one is taken from PEAR::Net_LDAP2
        $pattern = "/\\s* (?:([()]) | ([^'\\s()]+) | '((?:[^']+|'[^\\s)])*)') \\s*/x";
        preg_match_all($pattern, $value, $matches);
        $cMatches = count($matches[0]);
        $cPattern = count($matches);
        for ($i = 0; $i < $cMatches; $i++) { // number of tokens (full pattern match)
            for ($j = 1; $j < $cPattern; $j++) { // each subpattern
                $tok = trim($matches[$j][$i]);
                if (!empty($tok)) { // pattern match in this subpattern
                    $tokens[$i] = $tok; // this is the token
                }
            }
        }
        if ($tokens[0] == '(') {
            array_shift($tokens);
        }
        if ($tokens[count($tokens) - 1] == ')') {
            array_pop($tokens);
        }

        return $tokens;
    }
}
