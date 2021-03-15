<?php

use Codeception\Util\HttpCode;

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
}
