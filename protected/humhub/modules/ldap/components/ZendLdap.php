<?php


namespace humhub\modules\ldap\components;


use Laminas\Ldap\Ldap;
use Laminas\Ldap\Filter;
use Laminas\Ldap\Dn;
use Laminas\Ldap\Exception;
use Laminas\Ldap\Exception\LdapException;
use Traversable;
use Laminas\Ldap\ErrorHandler;


class ZendLdap extends Ldap
{
    /**
     * An LDAP search routine for finding information and returning paginated results
     * https://stackoverflow.com/questions/16892693/zf2-ldap-pagination
     *
     * Options can be either passed as single parameters according to the
     * method signature or as an array with one or more of the following keys
     * - filter
     * - baseDn
     * - scope
     * - attributes
     * - sort
     * - collectionClass
     * - sizelimit
     * - timelimit
     *
     * @param string|Filter\AbstractFilter|array $filter
     * @param string|Dn|null $basedn
     * @param int $scope
     * @param array $attributes
     * @param string|null $sort
     * @param string|null $collectionClass
     * @param integer $timelimit
     * @param integer $pageSize
     * @return array
     * @throws Exception\LdapException
     */
    public function multiPageSearch(
        $filter, $basedn = null, $scope, array $attributes = array(), $sort = null,
        $collectionClass = null, $timelimit = 0, $pageSize = 10000
    )
    {
        if (is_array($filter)) {
            $options = array_change_key_case($filter, CASE_LOWER);
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'filter':
                    case 'basedn':
                    case 'scope':
                    case 'sort':
                        $$key = $value;
                        break;
                    case 'attributes':
                        if (is_array($value)) {
                            $attributes = $value;
                        }
                        break;
                    case 'collectionclass':
                        $collectionClass = $value;
                        break;
                    case 'sizelimit':
                    case 'timelimit':
                        $$key = (int)$value;
                        break;
                }
            }
        }
        if ($basedn === null) {
            $basedn = $this->getBaseDn();
        } elseif ($basedn instanceof Dn) {
            $basedn = $basedn->toString();
        }
        if ($filter instanceof Filter\AbstractFilter) {
            $filter = $filter->toString();
        }
        $resource = $this->getResource();
        ErrorHandler::start(E_WARNING);
        $cookie = '';
        $results = [];

        if (version_compare(PHP_VERSION, '7.3') >= 0) {
            $results = $this->ldapSearchPaged($resource, $basedn, $filter, $attributes, 0, $pageSize, $timelimit);
        } else {
            do {
                ldap_control_paged_result($resource, $pageSize, true, $cookie);

                $result = ldap_search($resource, $basedn, $filter,
                    $attributes
                );
                foreach (ldap_get_entries($resource, $result) as $item) {
                    if (!is_array($item))
                        continue;

                    array_push($results, (array)$item);
                }
                ldap_control_paged_result_response($resource, $result, $cookie);
            } while ($cookie);

        }
        ErrorHandler::stop();
        if (count($results) == 0) {
            throw new Exception\LdapException($this, 'searching: ' . $filter);
        }
        return $results;
    }


    private function ldapSearchPaged($resource, $basedn, $filter, $attributes, $attributesOnly, $sizelimit, $timelimit)
    {
        $results = [];
        $cookie = '';

        // define("LDAP_CONTROL_PAGEDRESULTS", "1.2.840.113556.1.4.319");

        do {
            $result = ldap_search($resource, $basedn, $filter, $attributes, $attributesOnly, 0, $timelimit, null,
                [['oid' => '1.2.840.113556.1.4.319', 'value' => ['size' => $sizelimit, 'cookie' => $cookie]]]
            );

            $errCode = $dn = $errMsg = $refs = null;
            ldap_parse_result($resource, $result, $errCode, $dn, $errMsg, $refs, $controls);

            foreach (ldap_get_entries($resource, $result) as $item) {
                if (!is_array($item)) {
                    continue;
                }

                array_push($results, (array)$item);
            }

            if (isset($controls['1.2.840.113556.1.4.319']['value']['cookie'])) {
                $cookie = $controls['1.2.840.113556.1.4.319']['value']['cookie'];
            } else {
                $cookie = '';
            }
        } while (!empty($cookie));

        return $results;
    }


}
