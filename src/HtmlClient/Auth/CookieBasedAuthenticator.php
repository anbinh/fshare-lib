<?php

namespace Ndthuan\FshareLib\HtmlClient\Auth;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Psr7\Request;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\RequestDecoratorInterface;
use pQuery;
use pQuery\DomNode;

/**
 * Class CookieBasedAuthenticator.
 */
class CookieBasedAuthenticator implements AuthenticatorInterface
{
    /**
     * @var string
     */
    private $userEmail;

    /**
     * @var string
     */
    private $userPassword;

    /**
     * @var RequestDecoratorInterface
     */
    private $requestDecorator;

    /**
     * CookieBasedAuthenticator constructor.
     *
     * @param string $userEmail
     * @param string $userPassword
     * @param RequestDecoratorInterface $requestDecorator
     */
    public function __construct($userEmail, $userPassword, RequestDecoratorInterface $requestDecorator)
    {
        $this->userEmail = $userEmail;
        $this->userPassword = $userPassword;
        $this->requestDecorator = $requestDecorator;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(ClientInterface $httpClient)
    {
        if (!($httpClient->getConfig('cookies') instanceof CookieJarInterface)) {
            throw new UnsupportedClientException('This HTTP client does not have cookies configured');
        }

        $homePageDom = $this->requestHomePageDom($httpClient);

        if (!$this->isLoggedIn($homePageDom)) {
            if ($homePageDom->query('#_login-form')->count() < 1) {
                throw new LoginFormNotFoundException();
            }

            $csrfToken = $homePageDom->query('#_login-form [name=fs_csrf]')->val();
            $newHomePageDom = $this->doLogin($httpClient, $csrfToken);

            if (!$this->isLoggedIn($newHomePageDom)) {
                throw new FailedLoggingInException('User was not logged in');
            }
        }
    }

    /**
     * @param ClientInterface $httpClient
     *
     * @return DomNode
     */
    private function requestHomePageDom(ClientInterface $httpClient)
    {
        $request = $this->requestDecorator->decorate(new Request('GET', 'https://www.fshare.vn/'));
        $homePageHtml = $httpClient->send($request)->getBody()->getContents();

        return pQuery::parseStr($homePageHtml);
    }

    /**
     * @param DomNode $homePageDom
     *
     * @return bool
     */
    private function isLoggedIn(DomNode $homePageDom)
    {
        return $homePageDom->query('#_login-form')->count() < 1
            && $homePageDom->query('.dropdown-menu a[href*="logout"]')->count() > 0;
    }

    /**
     * @param ClientInterface $client
     * @param $csrfToken
     *
     * @return DomNode
     */
    private function doLogin(ClientInterface $client, $csrfToken)
    {
        $request = $this->requestDecorator->decorate(new Request('POST', 'https://www.fshare.vn/login'));
        $options = [
            'form_params' => [
                'fs_csrf' => $csrfToken,
                'LoginForm[email]' => $this->userEmail,
                'LoginForm[password]' => $this->userPassword,
                'LoginForm[checkloginpopup]' => 0,
                'LoginForm[rememberMe]' => 1,
                'yt0' => 'Đăng nhập',
            ]
        ];

        return pQuery::parseStr($client->send($request, $options)->getBody()->getContents());
    }
}
