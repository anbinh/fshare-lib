<?php

namespace Ndthuan\FshareLib\HtmlClient\RequestDecorator;

use Psr\Http\Message\RequestInterface;

/**
 * Request decorator that decorates nothing.
 */
class NullRequestDecorator implements RequestDecoratorInterface
{
    /**
     * @inheritDoc
     */
    public function decorate(RequestInterface $request)
    {
        return $request;
    }
}
