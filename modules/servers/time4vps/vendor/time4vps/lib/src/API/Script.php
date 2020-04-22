<?php

namespace Time4VPS\API;

use Time4VPS\Base\Endpoint;
use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use Time4VPS\Exceptions\Exception;

class Script extends Endpoint
{
    /**
     * @var int Script ID
     */
    protected $script_id;

    /**
     * @param null|int $script_id
     * @throws Exception
     */
    public function __construct($script_id = null)
    {
        parent::__construct('scripts');

        if ($script_id) {
            $this->script_id = (int) $script_id;
            if ($this->script_id <= 0) {
                throw new Exception("Script ID '{$script_id}' is invalid");
            }
        }
    }

    /**
     * Get available init scripts
     *
     * @return array
     * @throws APIException|AuthException
     */
    public function all()
    {
        return $this->get('/');
    }

    /**
     * Get script details
     *
     * @param null|string $field
     * @return array
     * @throws APIException|AuthException
     */
    public function details($field = null)
    {
        $this->mustHave('script_id');

        $return = $this->get("/{$this->script_id}");
        return $field ? $return[$field] : $return;
    }
}