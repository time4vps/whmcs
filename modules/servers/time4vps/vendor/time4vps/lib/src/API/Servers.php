<?php

namespace Time4VPS\API;

use Time4VPS\Base\Endpoint;
use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use Time4VPS\Exceptions\Exception;

class Servers extends Endpoint
{
    /**
     * Servers constructor.
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct('server');
    }

    /**
     * Get all servers
     * @return array Available servers array
     * @throws APIException|AuthException
     */
    public function all()
    {
        return $this->get();
    }

}