<?php

namespace Ndthuan\FshareLib\FunctionalTest;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Ndthuan\FshareLib\HtmlClient\Auth\CookieBasedAuthenticator;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\ReferralRequestDecorator;

class CookieBasedAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAuthenticate()
    {
        $cookieJar = new CookieJar();
        $client = new Client(['cookies' => $cookieJar]);
        $authenticator = new CookieBasedAuthenticator(
            FSHARE_TEST_EMAIL,
            FSHARE_TEST_PASSWORD,
            new ReferralRequestDecorator()
        );
        $authenticator->authenticate($client);

        static::assertTrue(is_string($cookieJar->getCookieValue('session_id')));
        static::assertTrue($cookieJar->getCookieValue('session_id') !== '');
    }
}
