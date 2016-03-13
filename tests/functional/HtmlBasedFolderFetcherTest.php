<?php

namespace Ndthuan\FshareLib\FunctionalTest;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Ndthuan\FshareLib\HtmlClient\HtmlBasedFolderFetcher;
use Ndthuan\FshareLib\HtmlClient\RequestDecorator\ReferralRequestDecorator;

class HtmlBasedFolderFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchFolderInfo()
    {
        $folderFetcher = new HtmlBasedFolderFetcher(
            new Client(['cookies' => new CookieJar()]),
            new ReferralRequestDecorator()
        );

        $folderInfo = $folderFetcher->fetchFolderInfo(FSHARE_TEST_FOLDER_URL);
        $files = $folderInfo->getFiles();
        $maxFileIndex = count($files) - 1;

        static::assertEquals(FSHARE_TEST_FOLDER_EXPECTED_NAME, $folderInfo->getName());
        static::assertEquals(FSHARE_TEST_FOLDER_EXPECTED_NUMBER_OF_FILES, count($files));
        static::assertEquals(FSHARE_TEST_FOLDER_EXPECTED_FIRST_FILE_URL, $files[0]->getUrl());
        static::assertEquals(FSHARE_TEST_FOLDER_EXPECTED_FIRST_FILE_NAME, $files[0]->getName());
        static::assertEquals(FSHARE_TEST_FOLDER_EXPECTED_LAST_FILE_URL, $files[$maxFileIndex]->getUrl());
        static::assertEquals(FSHARE_TEST_FOLDER_EXPECTED_LAST_FILE_NAME, $files[$maxFileIndex]->getName());
    }
}
