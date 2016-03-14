<?php

namespace Ndthuan\FshareLib\HtmlClient\RequestDecorator;

use Psr\Http\Message\RequestInterface;

/**
 * Request decorator interface.
 */
interface RequestDecoratorInterface
{
    /**
     * Decorates an HTTP request.
     *
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    public function decorate(RequestInterface $request);
}
