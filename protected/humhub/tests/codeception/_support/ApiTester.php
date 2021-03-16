<?php

use Codeception\Util\HttpCode;
use humhub\modules\rest\definitions\UserDefinitions;
use humhub\modules\user\models\User;
use yii\data\Pagination;
use yii\web\Link;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function amAdmin()
    {
        $this->amUser('Admin', 'test');
    }

    public function amUser1()
    {
        $this->amUser('User1', '123qwe');
    }

    public function amUser2()
    {
        $this->amUser('User2', '123qwe');
    }

    public function amUser3()
    {
        $this->amUser('User3', '123qwe');
    }

    public function amUser($user = null, $password = null)
    {
        $this->amHttpAuthenticated($user, $password);
    }

    public function seeSuccessResponseContainsJson($json = [])
    {
        $this->seeResponseCodeIs(HttpCode::OK);
        $this->seeResponseIsJson();
        $this->seeResponseContainsJson($json);
    }

    public function seeForbiddenResponseContainsJson($json = [])
    {
        $this->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->seeResponseIsJson();
        $this->seeResponseContainsJson($json);
    }

    public function seeBadResponseContainsJson($json = [])
    {
        $this->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->seeResponseIsJson();
        $this->seeResponseContainsJson($json);
    }

    /**
     * Send GET API Request and check a response contains the results
     *
     * @param string $url
     * @param array $jsonResults
     * @param array $paginationParams
     */
    public function seePaginationGetResponse($url, $jsonResults = [], $paginationParams = [])
    {
        $this->sendGet($url);
        $this->seePaginationResponseContainsJson($url, $jsonResults, $paginationParams);
    }

    /**
     * Send POST API Request and check a response contains the results
     *
     * @param string $url
     * @param array $postData
     * @param array $jsonResults
     * @param array $paginationParams
     */
    public function seePaginationPostResponse($url, $postData, $jsonResults = [], $paginationParams = [])
    {
        $this->sendPost($url, $postData);
        $this->seePaginationResponseContainsJson($url, $jsonResults, $paginationParams);
    }

    /**
     * Send PUT API Request and check a response contains the results
     *
     * @param string $url
     * @param array $putData
     * @param array $jsonResults
     * @param array $paginationParams
     */
    public function seePaginationPutResponse($url, $putData, $jsonResults = [], $paginationParams = [])
    {
        $this->sendPut($url, $putData);
        $this->seePaginationResponseContainsJson($url, $jsonResults, $paginationParams);
    }

    /**
     * Send DELETE API Request and check a response contains the results
     *
     * @param string $url
     * @param array $jsonResults
     * @param array $paginationParams
     */
    public function seePaginationDeleteResponse($url, $jsonResults = [], $paginationParams = [])
    {
        $this->sendDelete($url);
        $this->seePaginationResponseContainsJson($url, $jsonResults, $paginationParams);
    }

    /**
     * Send GET API Request and check a response contains the results
     *
     * @param string $url
     * @param array $jsonResults
     * @param array $paginationParams Possible keys: 'total', 'page', 'pages'
     */
    public function seePaginationResponseContainsJson($url, $jsonResults = [], $paginationParams = [])
    {
        $json = array_merge([
            'total' => count($jsonResults),
            'page' => 1,
            'pages' => 1,
        ], $paginationParams);

        $json['links'] = $this->getPaginationUrls($url, $json);
        $json['results'] = $jsonResults;

        $this->seeSuccessResponseContainsJson($json);
    }

    /**
     * Get pagination URLs, Used to check JSON response
     *
     * @param string $url
     * @param array $params Possible keys: 'page', 'pages'
     * @return string[]
     */
    protected function getPaginationUrls($url, $params)
    {
        $links = [Link::REL_SELF => $this->getPaginationUrl($url, $params['page'])];

        if ($params['pages'] > 0) {
            $links[Pagination::LINK_FIRST] = $this->getPaginationUrl($url, 0);
            $links[Pagination::LINK_LAST] = $this->getPaginationUrl($url, $params['pages'] - 1);
            if ($params['page'] > 1) {
                $links[Pagination::LINK_PREV] = $this->getPaginationUrl($url, $params['page'] - 1);
            }
            if ($params['page'] < $params['pages'] - 1) {
                $links[Pagination::LINK_NEXT] = $this->getPaginationUrl($url, $params['page'] + 1);
            }
        }

        return $links;
    }

    /**
     * @param string $url
     * @param int $page
     * @return string
     */
    protected function getPaginationUrl($url, $page = 1)
    {
        return '/api/v1/' . trim($url, '/') . '?page=' . (empty($page) ? 1 : $page) . '&per-page=100';
    }

    /**
     * Get users definitions by usernames or ids
     *
     * @param array $users Usernames or Ids
     * @return array
     */
    public function seeUserDefinitions($users)
    {
        $userDefinitions = [];

        foreach ($users as $userIdOrUsername) {
            $user = User::find()
                ->where(['id' => $userIdOrUsername])
                ->orWhere(['username' => $userIdOrUsername])
                ->one();
            if ($user) {
                $userDefinitions[] = UserDefinitions::getUser($user);
            }
        }

        $this->seeSuccessResponseContainsJson($userDefinitions);
    }
}
