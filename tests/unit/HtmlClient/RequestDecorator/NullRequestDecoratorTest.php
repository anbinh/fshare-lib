<?php

namespace Ndthuan\FshareLib\HtmlClient\RequestDecorator;

use GuzzleHttp\Psr7\Request;

class NullRequestDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testDecorate()
    {
        $request = new Request('POST', 'http://somewhere.xyz');

        $decorator = new NullRequestDecorator();

        static::assertSame($request, $decorator->decorate($request));
    }
}
