<?php

namespace Time4VPS\API;

use Time4VPS\Base\Endpoint;
use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use Time4VPS\Exceptions\Exception;

class Order extends Endpoint
{
    /**
     * @var int Order ID
     */
    protected $order_id;

    /**
     * Order constructor.
     *
     * @param int|null $order_id
     * @throws Exception
     */
    public function __construct($order_id = null)
    {
        parent::__construct('order');

        if ($order_id) {
            $this->order_id = (int) $order_id;
            if ($this->order_id <= 0) {
                throw new Exception("Product ID '{$order_id}' is invalid");
            }
        }
    }

    /**
     * Order new product
     *
     * @param int $product_id Product ID
     * @param null|string $domain Domain name (use serverhost.name for whitelabel domain)
     * @param string $cycle Billing period symbol (m - monthly, q - quarterly, s - semiannually, a - annually, b - biennially)
     * @param array $custom Additional options data available for sop products
     * @return array
     * @throws APIException|AuthException
     */
    public function create($product_id, $domain = null, $cycle = 'm', $custom = [])
    {
        return $this->post("/{$product_id}", [
            'domain' => $domain,
            'cycle' => $cycle,
            'custom' => $custom
        ]);
    }
}