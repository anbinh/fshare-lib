<?php

namespace Ndthuan\FshareLib\HtmlClient\RequestDecorator;

use GuzzleHttp\Psr7\Request;

class ReferralRequestDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testDecorate()
    {
        $request = new Request('POST', 'http://somewhere.xyz');
        $decorator = new ReferralRequestDecorator();

        $decoratedRequest = $decorator->decorate($request);

        static::assertTrue($decoratedRequest->hasHeader('Referer'));
        static::assertEquals(['https://www.fshare.vn/'], $decoratedRequest->getHeader('Referer'));
    }
}
