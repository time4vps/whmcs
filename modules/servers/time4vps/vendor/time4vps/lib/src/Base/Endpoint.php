<?php

namespace Time4VPS\Base;

use Time4VPS\Exceptions\APIException;
use Time4VPS\Exceptions\AuthException;
use Time4VPS\Exceptions\Exception;

class Endpoint
{
    /**
     * @var string API Endpoint Path
     */
    protected $endpoint;

    /**
     * @var string Base API Url
     */
    private static $base_url;

    /**
     * @var string API Username
     */
    private static $api_username;

    /**
     * @var string API Password
     */
    private static $api_password;

    /**
     * @var callable Debug function
     */
    private static $debug_function;

    /**
     * Endpoint constructor.
     * @param $endpoint
     * @throws Exception
     */
    protected function __construct($endpoint)
    {
        if (!isset(self::$base_url)) {
            throw new Exception('API Endpoint Error: Base URL is not set');
        }

        if (!isset(self::$api_username) || !isset(self::$api_password)) {
            throw new Exception('API Endpoint Error: Credentials are not set');
        }

        $this->endpoint = trim($endpoint, '/');
    }

    /**
     * Base API Url
     *
     * @param $url
     */
    public static function BaseURL($url)
    {
        self::$base_url = $url;
    }

    /**
     * Set auth details
     *
     * @param $username
     * @param $password
     */
    public static function Auth($username, $password)
    {
        self::$api_username = $username;
        self::$api_password = $password;
    }

    /**
     * Set debug function
     *
     * @param $function
     */
    public static function DebugFunction($function)
    {
        self::$debug_function = $function;
    }

    /**
     * @param string $path API relative path
     * @return array
     * @throws APIException|AuthException
     */
    public function get($path = "")
    {
        return $this->request('GET', $path);
    }

    /**
     * @param string $path API relative path
     * @param array $data Post Data
     * @return array
     * @throws APIException|AuthException
     */
    public function post($path = "", $data = [])
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * GET method
     *
     * @param string $method GET, POST, PUT, DELETE
     * @param string $path API relative path
     * @param array $data Post Data
     * @param callable $logFunction For debug purposes
     * @return array
     * @throws APIException|AuthException
     */
    public function request($method, $path, $data = null, $logFunction = null)
    {
        $url = self::$base_url . "{$this->endpoint}{$path}";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "utf-8",
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERPWD => self::$api_username . ':' . self::$api_password,
            CURLOPT_POSTFIELDS => $data ? json_encode($data) : null,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if (is_callable(self::$debug_function)) {
            $args = func_get_args();
            $args[1] = $url;
            call_user_func(self::$debug_function, $args, $data, $response);
        }

        if ($error) {
            throw new APIException("Request error: {$error}");
        }

        $response = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new APIException("Received invalid response from API");
        }

        if (array_key_exists('error', $response)) {
            $error = array_shift($response['error']);

            if ($error === 'wronglogin') {
                throw new AuthException('Invalid username / password combination');
            }

            if (!is_string($error)) {
                array_unshift($response['error'], $error);
                $error = json_encode($response['error']);
            }

            throw new APIException($error);
        }

        return $response;
    }

    /**
     * Check some fields before doing API requests
     *
     * @param $field
     * @throws APIException
     */
    protected function mustHave($field)
    {
        if (!$this->$field) {
            throw new APIException("{$field} is not set");
        }
    }
}