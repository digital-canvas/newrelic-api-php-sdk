<?php
namespace DigitalCanvas\NewRelic\Adapter;

use DigitalCanvas\NewRelic\Request;
use DigitalCanvas\NewRelic\Response;

/**
 * Http Client Adapter interface
 *
 * @package NewRelic
 * @category Adapter
 */
interface HttpClientInterface {

    /**
     * Sends request and returns response
     *
     * @param Request $request The request to send
     *
     * @return Response Te received response
     */
    public function sendRequest(Request $request);
}
