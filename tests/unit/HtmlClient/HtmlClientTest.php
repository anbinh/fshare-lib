<?php

namespace Ndthuan\FshareLib\HtmlClient;

use Ndthuan\FshareLib\Api\DTO\FshareFile;
use Ndthuan\FshareLib\Api\DTO\FshareFolder;
use Ndthuan\FshareLib\Api\FileFetcherInterface;
use Ndthuan\FshareLib\Api\FolderFetcherInterface;

class HtmlClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $folderFetcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFetcher;

    /**
     * @var HtmlClient
     */
    private $testObject;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->folderFetcher = $this->getMock(FolderFetcherInterface::class);
        $this->fileFetcher = $this->getMock(FileFetcherInterface::class);

        $this->testObject = new HtmlClient($this->folderFetcher, $this->fileFetcher);
    }

    public function testFetchDownloadableUrlsFromFileUrl()
    {
        $this->fileFetcher
            ->expects(static::once())
            ->method('fetchDownloadableUrl')
            ->wilLReturn('download.url');

        $this->folderFetcher
            ->expects(static::never())
            ->method('fetchFolderInfo');

        $downloadableUrls = $this->testObject->fetchDownloadableUrls('http://some.url');

        static::assertEquals(1, count($downloadableUrls));
        static::assertEquals('download.url', $downloadableUrls[0]);
    }

    public function testFetchDownloadableUrlsFromFolderUrl()
    {
        $folderUrl = 'http://www.fshare.vn/folder/xxx';

        $this->fileFetcher
            ->expects(static::exactly(2))
            ->method('fetchDownloadableUrl')
            ->withConsecutive(['file.url1'], ['file.url2'])
            ->willReturnOnConsecutiveCalls('download.url1', 'download.url2');

        $this->folderFetcher
            ->expects(static::once())
            ->method('fetchFolderInfo')
            ->willReturn(
                new FshareFolder(
                    $folderUrl,
                    'Awesome Folder',
                    [
                        new FshareFile('file.url1', 'filename1'),
                        new FshareFile('file.url2', 'filename2'),
                    ]
                )
            );

        $downloadableUrls = $this->testObject->fetchDownloadableUrls($folderUrl);

        static::assertEquals(2, count($downloadableUrls));
        static::assertEquals('download.url1', $downloadableUrls[0]);
        static::assertEquals('download.url2', $downloadableUrls[1]);
    }

    public function testFetchFolderInfo()
    {
        $this->folderFetcher
            ->expects(static::once())
            ->method('fetchFolderInfo');

        $this->testObject->fetchFolderInfo('dummy.url');
    }
}
