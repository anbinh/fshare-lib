<?php

namespace Ndthuan\FshareLib\HtmlClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Ndthuan\FshareLib\HtmlClient\Auth\AuthenticatorInterface;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\RequestDecoratorInterface;

class HtmlBasedFileFetcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestDecoratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticatorMock;

    /**
     * @var HtmlBasedFileFetcher
     */
    private $testObject;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->clientMock = $this->getMock(ClientInterface::class);
        $this->requestDecoratorMock = $this->getMock(RequestDecoratorInterface::class);
        $this->authenticatorMock = $this->getMock(AuthenticatorInterface::class);

        $this->testObject = new HtmlBasedFileFetcher(
            $this->clientMock,
            $this->authenticatorMock,
            $this->requestDecoratorMock
        );
    }

    /**
     * Testing...
     *
     * @expectedException \Ndthuan\FshareLib\HtmlClient\DownloadNotFoundException
     * @expectedExceptionMessage Download form not found
     */
    public function testFetchDownloadableUrlIfNoDownloadFormIsPresented()
    {
        $fileUrl = 'http://www.fshare.vn/file/DummyFile/';
        $pageRequest = new Request('GET', $fileUrl);

        $this->clientMock
            ->expects(static::once())
            ->method('send')
            ->with($pageRequest)
            ->willReturn(new Response(200, [], ''));
        $this->requestDecoratorMock->expects(static::once())->method('decorate')->willReturnArgument(0);
        $this->authenticatorMock->expects(static::once())->method('authenticate');

        $this->testObject->fetchDownloadableUrl($fileUrl);
    }

    /**
     * @expectedException \Ndthuan\FshareLib\HtmlClient\DownloadNotFoundException
     * @expectedExceptionMessage Download URL not found
     */
    public function testFetchDownloadableUrlIfDownloadFormIsPresentedButDownloadInfoIsNotAvailable()
    {
        $fileUrl = 'http://www.fshare.vn/file/DummyFile/';
        $downloadFormHtml = <<<XXX
            <form id="download-form">
            <input name="fs_csrf" value="xxx">
            <input id="DownloadForm_linkcode" value="yyy">
            </form>
XXX;
        $this->performDownloadInfoFetchingProcess(
            $fileUrl,
            $downloadFormHtml,
            '{}'
        );

        $this->testObject->fetchDownloadableUrl($fileUrl);
    }

    /**
     * @throws DownloadNotFoundException
     */
    public function testFetchDownloadableUrlWhenDownloadFormAndDownloadInfoAreAvailable()
    {
        $fileUrl = 'http://www.fshare.vn/file/DummyFile/';
        $downloadFormHtml = <<<XXX
<html>
    <body>
        <form id="download-form"><input name="fs_csrf" value="xxx"><input id="DownloadForm_linkcode" value="yyy"></form>
        <div class="file-info">
            <div class="margin-bottom-15"><i class="fa fa-file-o"></i> My Awesome File</div>
            <div class="row margin-bottom-15">blah blah</div>
        </div>
    </body>
</html>
XXX;

        $downloadableUrl = $this->performDownloadInfoFetchingProcess(
            $fileUrl,
            $downloadFormHtml,
            '{"url":"http://download.url"}'
        );
        static::assertEquals('http://download.url', $downloadableUrl->getUrl());
        static::assertEquals($fileUrl, $downloadableUrl->getFile()->getUrl());
        static::assertEquals('My Awesome File', $downloadableUrl->getFile()->getName());
    }

    /**
     * @param string $fileUrl
     * @param string $downloadFormHtml
     * @param string $downloadInfoJson
     * @return \Ndthuan\FshareLib\Api\DTO\DownloadableUrl
     */
    private function performDownloadInfoFetchingProcess($fileUrl, $downloadFormHtml, $downloadInfoJson)
    {
        $pageRequest = new Request('GET', $fileUrl);
        $downloadInfoRequest = new Request('POST', 'https://www.fshare.vn/download/get');
        $downloadInfoOptions = [
            'form_params' => [
                'fs_csrf' => 'xxx',
                'DownloadForm[pwd]' => '',
                'DownloadForm[linkcode]' => 'yyy',
                'ajax' => 'download-form',
            ]
        ];

        $this->clientMock
            ->expects(static::exactly(2))
            ->method('send')
            ->withConsecutive(
                [$pageRequest, []],
                [$downloadInfoRequest, $downloadInfoOptions]
            )
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], $downloadFormHtml),
                new Response(200, [], $downloadInfoJson)
            );

        $this->requestDecoratorMock->expects(static::exactly(2))->method('decorate')->willReturnArgument(0);
        $this->authenticatorMock->expects(static::once())->method('authenticate');

        return $this->testObject->fetchDownloadableUrl($fileUrl);
    }
}
