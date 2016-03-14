<?php

namespace Ndthuan\FshareLib\HtmlClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Ndthuan\FshareLib\Api\DTO\FshareFile;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\RequestDecoratorInterface;

class HtmlBasedFolderFetcherTest extends \PHPUnit_Framework_TestCase
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
     * @var HtmlBasedFolderFetcher
     */
    private $testObject;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->clientMock = $this->getMock(ClientInterface::class);
        $this->requestDecoratorMock = $this->getMock(RequestDecoratorInterface::class);

        $this->testObject = new HtmlBasedFolderFetcher($this->clientMock, $this->requestDecoratorMock);
    }

    /**
     * @expectedException \Ndthuan\FshareLib\HtmlClient\FolderNotFoundException
     */
    public function testFetchFolderInfoIfFolderElementNotAvailable()
    {
        $folderUrl = 'http://dummy.url';
        $request = new Request('GET', $folderUrl);

        $this->clientMock
            ->expects(static::once())
            ->method('send')
            ->with($request)
            ->willReturn(new Response(200, [], ''));

        $this->requestDecoratorMock->expects(static::once())->method('decorate')->willReturnArgument(0);

        $this->testObject->fetchFolderInfo($folderUrl);
    }

    /**
     *
     * @dataProvider dataProviderTestFetchFolderInfoIfFolderElementAvailable
     *
     * @param string $pageHtml
     * @param string $folderName
     * @param FshareFile[] $files
     */
    public function testFetchFolderInfoIfFolderElementAvailable($pageHtml, $folderName, $files)
    {
        $folderUrl = 'http://dummy.url';
        $request = new Request('GET', $folderUrl);

        $this->clientMock
            ->expects(static::once())
            ->method('send')
            ->with($request)
            ->willReturn(new Response(200, [], $pageHtml));

        $this->requestDecoratorMock->expects(static::once())->method('decorate')->willReturnArgument(0);

        $folderInfo = $this->testObject->fetchFolderInfo($folderUrl);
        static::assertEquals($folderUrl, $folderInfo->getUrl());
        static::assertEquals($folderName, $folderInfo->getName());
        static::assertEquals($files, $folderInfo->getFiles());
    }

    public function dataProviderTestFetchFolderInfoIfFolderElementAvailable()
    {
        return [
            [
                '<div id="dlnav"><div id="path" data-path="/My Awesome Folder"></div></div>',
                'My Awesome Folder',
                []
            ],
            [
                '<div id="dlnav">
                    <div id="path" data-path="/My Awesome Folder"></div>
                </div>
                <div id="filelist">
                    <div class="file_name">
                        <a href="http://awesome.url" title="XXX"></a>
                    </div>
                </div>',
                'My Awesome Folder',
                [
                    new FshareFile('http://awesome.url', 'XXX')
                ]
            ],
        ];
    }
}
