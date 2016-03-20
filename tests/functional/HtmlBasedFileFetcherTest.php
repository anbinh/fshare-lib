<?php

namespace Ndthuan\FshareLib\FunctionalTest;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Ndthuan\FshareLib\HtmlClient\Auth\CookieBasedAuthenticator;
use Ndthuan\FshareLib\HtmlClient\HtmlBasedFileFetcher;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\ReferralRequestDecorator;

class HtmlBasedFileFetcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HtmlBasedFileFetcher
     */
    private $fileFetcher;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $client = new Client(['cookies' => new CookieJar()]);
        $requestDecorator = new ReferralRequestDecorator();

        $this->fileFetcher = new HtmlBasedFileFetcher(
            $client,
            new CookieBasedAuthenticator(
                FSHARE_TEST_EMAIL,
                FSHARE_TEST_PASSWORD,
                $requestDecorator
            ),
            $requestDecorator
        );
    }


    public function testFetchDownloadableUrlIfFileUrlIsValidShouldReturnDownloadableUrl()
    {
        $downloadableUrl = $this->fileFetcher->fetchDownloadableUrl(FSHARE_TEST_FILE_URL);

        static::assertRegExp('#^https?://#', $downloadableUrl->getUrl());
        static::assertEquals(FSHARE_TEST_FILE_EXPECTED_NAME, $downloadableUrl->getFile()->getName());
    }

    /**
     * @expectedException \Ndthuan\FshareLib\HtmlClient\DownloadFormNotFoundException
     * @expectedExceptionMessage Download form not found
     */
    public function testFetchDownloadableUrlIfFileUrlIsInvalidShouldThrowException()
    {
        $this->fileFetcher->fetchDownloadableUrl('http://www.fshare.vn/');
    }

    public function testFetchFileInfo()
    {
        $file = $this->fileFetcher->fetchFileInfo(FSHARE_TEST_FILE_URL);

        static::assertEquals(FSHARE_TEST_FILE_URL, $file->getUrl());
        static::assertEquals(FSHARE_TEST_FILE_EXPECTED_NAME, $file->getName());
    }
}
