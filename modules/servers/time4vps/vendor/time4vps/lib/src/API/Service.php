<?php

namespace Time4VPS\API;

use Time4VPS\Base\Endpoint;
use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use Time4VPS\Exceptions\Exception;

class Service extends Endpoint
{
    /**
     * @var int Service ID
     */
    protected $service_id;

    /**
     * Service constructor.
     * @param null $service_id
     * @throws Exception
     */
    public function __construct($service_id = null)
    {
        parent::__construct('service');
        if ($service_id) {
            $this->service_id = (int) $service_id;
            if ($this->service_id <= 0) {
                throw new Exception("Service ID '{$service_id}' is invalid");
            }
        }
    }

    /**
     * Get account details
     *
     * @return array
     * @throws APIException|AuthException
     */
    public function details()
    {
        $this->mustHave('service_id');
        $details = $this->get("/{$this->service_id}");
        return array_shift($details);
    }

    /**
     * Get all services
     *
     * @return array
     * @throws APIException|AuthException
     */
    public function all()
    {
        return $this->get('/');
    }

    /**
     * Get service ID from order number
     *
     * @param $order_num
     * @return int
     * @throws APIException|AuthException
     */
    public function fromOrder($order_num)
    {
        $response = $this->get("/order/{$order_num}");

        return (int) $response['account_id'];
    }

    /**
     * Cancel / terminate account
     *
     * @param string $reason Termination reason
     * @param bool $immediate Immediate termination
     * @return array
     * @throws APIException|AuthException
     */
    public function cancel($reason, $immediate = false)
    {
        $this->mustHave('service_id');

        return $this->post("/{$this->service_id}/cancel", [
            'immediate' => $immediate,
            'reason' => $reason
        ]);
    }

    /**
     * Get available upgrades
     *
     * @return array
     * @throws APIException|AuthException
     */
    public function upgrades()
    {
        $this->mustHave('service_id');

        return $this->get("/{$this->service_id}/upgrade");
    }

    /**
     * List addons 
     *
     * @return array
     * @throws APIException|AuthException
     */
    public function addons()
    {
        $this->mustHave('service_id');

        return $this->get("/{$this->service_id}/addon");
    }

    /**
     * Upgrade service
     *
     * @param array $upgrades
     * @param string $cycle
     * @return array
     * @throws APIException|AuthException
     */
    public function orderUpgrade($upgrades, $cycle = null)
    {
        $this->mustHave('service_id');

        return $this->post("/{$this->service_id}/upgrade", array_merge($upgrades, [
            'cycle' => $cycle,
            'send' => true
        ]));
    }

    /**
     * Add addon to service
     *
     * @param $addon_id
     * @return array
     * @throws APIException|AuthException
     */
    public function orderAddon($addon_id)
    {
        $this->mustHave('service_id');

        return $this->post("/{$this->service_id}/addon", [
            'addon_id' => $addon_id
        ]);
    }
}