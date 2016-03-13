<?php

namespace Ndthuan\FshareLib\HtmlClient\RequestDecorator;

use Psr\Http\Message\RequestInterface;

/**
 * Adds referrer to HTTP request.
 */
class ReferralRequestDecorator implements RequestDecoratorInterface
{
    /**
     * @inheritDoc
     */
    public function decorate(RequestInterface $request)
    {
        return $request->withHeader('Referer', 'https://www.fshare.vn/');
    }
}
