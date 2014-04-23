<?php
namespace DigitalCanvas\NewRelic;

use DigitalCanvas\NewRelic\Adapter\HttpClientInterface;
use DigitalCanvas\NewRelic\Request;
use DigitalCanvas\NewRelic\Response;
use DigitalCanvas\NewRelic\Adapter\Curl as CurlAdapter;

/**
 * New Relic Api
 *
 * @package NewRelic
 * @category Api
 */
class Api
{

    const VERSION = 'v2';

    const URL = 'https://api.newrelic.com';

    /**
     * API base url
     *
     * @var string $url
     */
    private $url;

    /**
     * New Relic API key
     *
     * @var string $api_key
     */
    private $api_key;


    /**
     * HTTP Client Adapter
     *
     * @var HttpClientInterface $adapter
     */
    protected $adapter = null;

    /**
     * Class constructor
     *
     * @param string $api_key New Relic API key
     * @param HttpClientInterface $adapter
     */
    public function __construct($api_key = null, HttpClientInterface $adapter = null)
    {
        $this->setApiKey($api_key);
        $this->setHTTPClientAdapter($adapter);
        $this->url = self::URL . "/" . self::VERSION . "/";
    }

    /**
     * Returns list of servers
     * @return Response
     */
    public function getServerList()
    {
        $path = "servers.json";
        $request = $this->buildRequest($path);
        return $this->getHTTPClientAdapter()->sendRequest($request);
    }

    /**
     * Returns details for a single server
     * @param int $server_id
     * @return Response
     */
    public function getServerDetails($server_id)
    {
        $server_id = (int)$server_id;
        $path = "servers/{$server_id}.json";
        $request = $this->buildRequest($path);
        return $this->getHTTPClientAdapter()->sendRequest($request);
    }

    /**
     * Returns list of server matching a given name
     * @param string $server_name
     * @return Response
     */
    public function findServerByName($server_name)
    {
        $path = "servers.json";
        $params = array(
          'filter' => array(
            'name' => $server_name
          )
        );
        $request = $this->buildRequest($path);
        return $this->getHTTPClientAdapter()->sendRequest($request);
    }

    /**
     * Returns list of alert policies
     * @param string $type
     * @param string $name
     * @param string $id
     * @param boolean $enabled
     * @return Response
     */
    public function listAlertPolicies($type = null, $name = null, $id = null, $enabled = null)
    {
        $path = "alert_policies.json";
        $enabled = !is_null($enabled) ? (($enabled) ? 'true' : 'false') : null;
        $params = array(
          'filter' => array(
            'ids' => $id,
            'type' => $type,
            'name' => $name,
            'enabled' => $enabled
          )
        );
        $request = $this->buildRequest($path, $params);
        return $this->getHTTPClientAdapter()->sendRequest($request);
    }

    /**
     * Returns details for a single alerty policy
     * @param int $policy_id
     * @return Response
     */
    public function getAlertPolicyDetails($policy_id)
    {
        $policy_id = (int)$policy_id;
        $path = "alert_policies/{$policy_id}.json";
        $request = $this->buildRequest($path);
        return $this->getHTTPClientAdapter()->sendRequest($request);
    }


    /**
     * Finds alert policies associated with a given server
     * @param int $server_id
     * @return Response
     */
    public function findAlertPoliciesByServer($server_id)
    {
        $response = $this->listAlertPolicies('server', null, null, true);
        $body = $response->getBody();
        foreach ($body['alert_policies'] as $key => $policy) {
            if (!in_array($server_id, $policy['links']['servers'])) {
                // Remove policies not associated with the server
                unset($body['alert_policies'][$key]);
            }
        }
        // Reset response body
        $response->setBody($body);
        return $response;
    }

    /**
     * Gets the value of api_key.
     *
     * @param string $api_key
     *
     * @return Api
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        return $this;
    }

    /**
     * Sets the HTTP Client Adapter
     *
     * If not provided Curl adapter will be used
     *
     * @param HttpClientInterface $adapter
     * @return Api
     */
    public function setHTTPClientAdapter(HttpClientInterface $adapter = null)
    {
        if (is_null($adapter)) {
            $adapter = new CurlAdapter();
        }
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Returns current HTTP Client Adapter
     *
     * @return HttpClientInterface
     */
    public function getHTTPClientAdapter()
    {
        if (is_null($this->adapter)) {
            $this->setHTTPClientAdapter(null);
        }
        return $this->adapter;
    }

    /**
     * Builds the Request object
     *
     * @param type $path
     * @param array $params
     * @param string $method
     * @return Request
     */
    protected function buildRequest($path, array $params = array(), $method = "GET")
    {
        $uri = $this->url . $path;
        $headers = array(
          'X-Api-Key' => $this->api_key,
        );
        $request = new Request();
        $request->setUri($uri);
        $request->setMethod($method);
        $request->setParams($params);
        $request->setHeaders($headers);
        return $request;
    }


}
