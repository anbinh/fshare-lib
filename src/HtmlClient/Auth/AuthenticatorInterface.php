<?php

namespace Ndthuan\FshareLib\HtmlClient\Auth;

use GuzzleHttp\ClientInterface;

/**
 * Authenticator interface.
 */
interface AuthenticatorInterface
{
    /**
     * @param ClientInterface $httpClient
     */
    public function authenticate(ClientInterface $httpClient);
}
