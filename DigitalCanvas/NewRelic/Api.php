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
class Api {

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

    public function getServerList()
    {
      $path = "servers.json";
      $request = $this->buildRequest($path);
      return $this->getHTTPClientAdapter()->sendRequest($request);
    }

    public function getServerDetails($server_id)
    {
      $path = "servers/{$server_id}.json";
      $request = $this->buildRequest($path);
      return $this->getHTTPClientAdapter()->sendRequest($request);
    }

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
    if(is_null($this->adapter)){
      $this->setHTTPClientAdapter(null);
    }
    return $this->adapter;
  }

  /**
   * Builds the Request object
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
