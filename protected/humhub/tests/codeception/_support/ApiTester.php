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
class ApiTester extends BaseTester
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

    public function seeCodeResponseContainsJson($code, $json = [])
    {
        $this->seeResponseCodeIs($code);
        $this->seeResponseIsJson();
        $this->seeResponseContainsJson($json);
    }

    public function seeSuccessResponseContainsJson($json = [])
    {
        $this->seeCodeResponseContainsJson(HttpCode::OK, $json);
    }

    public function seeForbiddenResponseContainsJson($json = [])
    {
        $this->seeCodeResponseContainsJson(HttpCode::FORBIDDEN, $json);
    }

    public function seeBadResponseContainsJson($json = [])
    {
        $this->seeCodeResponseContainsJson(HttpCode::BAD_REQUEST, $json);
    }

    public function seeNotFoundResponseContainsJson($json = [])
    {
        $this->seeCodeResponseContainsJson(HttpCode::NOT_FOUND, $json);
    }

    public function seeServerErrorResponseContainsJson($json = [])
    {
        $this->seeCodeResponseContainsJson(HttpCode::INTERNAL_SERVER_ERROR, $json);
    }

    public function seeSuccessMessage($message)
    {
        $this->seeCodeResponseContainsJson(HttpCode::OK, ['message' => $message]);
    }

    public function seeForbiddenMessage($message)
    {
        $this->seeCodeResponseContainsJson(HttpCode::FORBIDDEN, ['message' => $message]);
    }

    public function seeBadMessage($message)
    {
        $this->seeCodeResponseContainsJson(HttpCode::BAD_REQUEST, ['message' => $message]);
    }

    public function seeNotFoundMessage($message)
    {
        $this->seeCodeResponseContainsJson(HttpCode::NOT_FOUND, ['message' => $message]);
    }

    public function seeServerErrorMessage($message)
    {
        $this->seeCodeResponseContainsJson(HttpCode::INTERNAL_SERVER_ERROR, ['message' => $message]);
    }

    /**
     * Send GET API Request and check a response contains the results
     *
     * @param string $url
     * @param array $jsonResults
     * @param array $paginationParams
     * @param array $urlParams
     */
    public function seePaginationGetResponse($url, $jsonResults = [], $paginationParams = [], $urlParams = [])
    {
        $encodedUrlParams = [];
        foreach ($urlParams as $paramKey => $paramValue) {
            $encodedUrlParams[] = $paramKey . '=' . urlencode($paramValue);
        }

        if (!empty($encodedUrlParams)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . implode('&', $encodedUrlParams);
        }

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
        $jsonResultsCount = count($jsonResults);

        $json = array_merge([
            'total' => $jsonResultsCount,
            'page' => 1,
            'pages' => $jsonResultsCount ? 1 : 0,
            'perPage' => 100,
        ], $paginationParams);

        $json['links'] = $this->getPaginationUrls($url, $json);
        $json['results'] = $jsonResults;

        unset($json['perPage']);
        $this->seeSuccessResponseContainsJson($json);
    }

    /**
     * Get pagination URLs, Used to check JSON response
     *
     * @param string $url
     * @param array $params Possible keys: 'page', 'pages', 'perPage'
     * @return string[]
     */
    protected function getPaginationUrls($url, $params)
    {
        $links = [Link::REL_SELF => $this->getPaginationUrl($url, $params['page'], $params['perPage'])];

        if ($params['pages'] > 0) {
            $links[Pagination::LINK_FIRST] = $this->getPaginationUrl($url, 0, $params['perPage']);
            $links[Pagination::LINK_LAST] = $this->getPaginationUrl($url, $params['pages'] - 1, $params['perPage']);
            if ($params['page'] > 1) {
                $links[Pagination::LINK_PREV] = $this->getPaginationUrl($url, $params['page'] - 1, $params['perPage']);
            }
            if ($params['page'] < $params['pages'] - 1) {
                $links[Pagination::LINK_NEXT] = $this->getPaginationUrl($url, $params['page'] + 1, $params['perPage']);
            }
        }

        return $links;
    }

    /**
     * @param string $url
     * @param int $page
     * @param int $perPage
     * @return string
     */
    protected function getPaginationUrl($url, $page = 1, $perPage = 100)
    {
        return '/api/v1/' . trim($url, '/') . (strpos($url, '?') === false ? '?' : '&') . 'page=' . (empty($page) ? 1 : $page) . '&per-page=' . $perPage;
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
