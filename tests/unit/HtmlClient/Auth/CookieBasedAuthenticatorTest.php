<?php

namespace Ndthuan\FshareLib\HtmlClient\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\RequestDecoratorInterface;

class CookieBasedAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestDecoratorMock;

    /**
     * @var CookieBasedAuthenticator
     */
    private $testObject;

    /**
     * @var string
     */
    private $email = 'my@email';

    /**
     * @var string
     */
    private $password = 'myPassword';

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->requestDecoratorMock = $this->getMock(RequestDecoratorInterface::class);
        $this->testObject = new CookieBasedAuthenticator(
            $this->email,
            $this->password,
            $this->requestDecoratorMock
        );
    }

    /**
     * @expectedException \Ndthuan\FshareLib\HtmlClient\Auth\UnsupportedClientException
     * @expectedExceptionMessage This HTTP client does not have cookies configured
     *
     * @dataProvider dataProviderHttpClientsWithoutCookies
     *
     * @param ClientInterface $client
     */
    public function testAuthenticateWhenClientDoesNotSupportCookies(ClientInterface $client)
    {
        $this->testObject->authenticate($client);
    }

    public function testAuthenticateWithValidClientAndUserAlreadyLoggedIn()
    {
        $homePageRequest = new Request('GET', 'https://www.fshare.vn/');

        $this->requestDecoratorMock
        ->expects(static::once())
            ->method('decorate')
            ->willReturnArgument(0);

        $clientMock = $this->getMock(ClientInterface::class);
        $clientMock->expects(static::once())
            ->method('send')
            ->with($homePageRequest)
            ->willReturn(new Response(200, [], '<body><div class="dropdown-menu"><a href="/logout"></a></div></body>'));
        $clientMock->expects(static::once())
            ->method('getConfig')
            ->with('cookies')
            ->willReturn(new CookieJar());

        $this->testObject->authenticate($clientMock);
    }

    /**
     * @expectedException \Ndthuan\FshareLib\HtmlClient\Auth\LoginFormNotFoundException
     */
    public function testAuthenticateWithValidClientAndUserNotLoggedInYetButLoginFormNotPresented()
    {
        $homePageRequest = new Request('GET', 'https://www.fshare.vn/');

        $clientMock = $this->getMock(ClientInterface::class);
        $clientMock->expects(static::once())
            ->method('send')
            ->with($homePageRequest)
            ->willReturn(new Response(200, [], '<body></body>'));
        $clientMock->expects(static::once())
            ->method('getConfig')
            ->with('cookies')
            ->willReturn(new CookieJar());

        $this->requestDecoratorMock
            ->expects(static::once())
            ->method('decorate')
            ->willReturnArgument(0);

        $this->testObject->authenticate($clientMock);
    }

    /**
     * @expectedException \Ndthuan\FshareLib\HtmlClient\Auth\FailedLoggingInException
     */
    public function testAuthenticateIfUserFailedToLogIn()
    {
        $clientMock = $this->getMock(ClientInterface::class);
        $this->performFullLoginProcess($clientMock, '');
        $this->testObject->authenticate($clientMock);
    }

    public function testAuthenticateIfUserSuccessfulToLogIn()
    {
        $clientMock = $this->getMock(ClientInterface::class);
        $this->performFullLoginProcess($clientMock, '<div class="dropdown-menu"><a href="/logout"></a></div>');
        $this->testObject->authenticate($clientMock);
    }

    /**
     * @return array[]
     */
    public function dataProviderHttpClientsWithoutCookies()
    {
        return [
            [new Client()],
            [new Client(['cookies' => false])],
        ];
    }

    /**
     * @param ClientInterface $clientMock
     * @param string $loginResponseHtml
     */
    private function performFullLoginProcess(ClientInterface $clientMock, $loginResponseHtml)
    {
        $homePageRequest = new Request('GET', 'https://www.fshare.vn/');
        $loginRequest = new Request('POST', 'https://www.fshare.vn/login');
        $loginOptions = [
            'form_params' => [
                'fs_csrf' => 'xxx',
                'LoginForm[email]' => $this->email,
                'LoginForm[password]' => $this->password,
                'LoginForm[checkloginpopup]' => 0,
                'LoginForm[rememberMe]' => 1,
                'yt0' => 'Đăng nhập',
            ]
        ];

        $clientMock->expects(static::exactly(2))
            ->method('send')
            ->withConsecutive(
                [$homePageRequest, []],
                [$loginRequest, $loginOptions]
            )
            ->willReturnOnConsecutiveCalls(
                new Response(
                    200,
                    [],
                    '<body><form id="_login-form"><input name="fs_csrf" value="xxx"></form></body>'
                ),
                new Response(200, [], $loginResponseHtml)
            );

        $clientMock->expects(static::once())
            ->method('getConfig')
            ->with('cookies')
            ->willReturn(new CookieJar());

        $this->requestDecoratorMock
            ->expects(static::exactly(2))
            ->method('decorate')
            ->willReturnArgument(0);
    }
}
