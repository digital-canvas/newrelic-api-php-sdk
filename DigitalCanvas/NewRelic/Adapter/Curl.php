<?php
namespace DigitalCanvas\NewRelic\Adapter;

use DigitalCanvas\NewRelic\Request;
use DigitalCanvas\NewRelic\Response;
use DigitalCanvas\NewRelic\Exception\RuntimeException;
use DigitalCanvas\NewRelic\Exception\UnexpectedValueException;

/**
 * Sends Requests using the curl extension
 *
 * @package NewRelic
 * @category Adapter
 * @see http://php.net/manual/en/book.curl.php
 */
class Curl implements HttpClientInterface {

  /**
   *
   * @var string $response
   */
  public $response;

  /**
   * Returns the response string
   * @return string
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * Sends a request and returns a response
   *
   * @param Request $request
   * @return Response
   */
  public function sendRequest(Request $request) {
    if(!extension_loaded('curl')){
      throw new RuntimeException("cURL extension not found.");
    }
    $ch = curl_init();
    if ($request->getMethod() == 'GET') {
      $uri = $request->getUri() . '?' . http_build_query($request->getParams());
    } else {
      $uri = $request->getUri();
    }
    curl_setopt($ch, CURLOPT_URL, $uri);
    $headers = array();
    foreach($request->getHeaders() as $key => $value){
      $headers[] = $key . ": " . $value;
    }
    if ($request->getMethod() == 'GET') {
      curl_setopt($ch, CURLOPT_HTTPGET, 1);
    } elseif($request->getMethod() == 'POST') {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request->getParams()));
    } elseif($request->getMethod() == 'PUT') {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      $body = json_encode($request->getParams());
      $headers[] = 'Content-Length: ' . strlen($body);
      $headers[] = 'Content-Type: application/json';
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . "cacert.pem");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $this->response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($this->response, 0, $header_size);
    $body = substr($this->response, $header_size);
    $contenttype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $status = $this->getStatusMessage($headers);
    curl_close($ch);
    if ($contenttype != 'application/json') {
      throw new UnexpectedValueException("Unknown response format.");
    }
    $body = json_decode($body, true);
    $response = new Response();
    $response->setRawResponse($this->response);
    $response->setBody($body);
    $response->setHeaders($this->http_parse_headers($headers));
    $response->setStatus($status, $code);
    return $response;
  }

  /**
   * Parses headers into array
   * @param string $header
   * @return array
   */
  private function http_parse_headers($header) {
    $retVal = array();
    $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
    foreach ($fields as $field) {
      if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
        $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
        if (isset($retVal[$match[1]])) {
          if (!is_array($retVal[$match[1]])) {
            $retVal[$match[1]] = array($retVal[$match[1]]);
          }
          $retVal[$match[1]][] = $match[2];
        } else {
          $retVal[$match[1]] = trim($match[2]);
        }
      }
    }
    return $retVal;
  }

  /**
   * Gets status message from response
   * @param string $headers Raw header string
   * @return string
   */
  private function getStatusMessage($headers) {
    $array = explode("\r\n", $headers);
    $status = array_shift($array);
    if (preg_match("/^HTTP\\/[\\d\\.]+\\s\\d+\\s(.+)/i", $status, $matches)) {
      $status = $matches[1];
    }
    return $status;
  }

}
