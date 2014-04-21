<?php
namespace DigitalCanvas\NewRelic\Exception;

use DigitalCanvas\NewRelic\ExceptionInterface;

/**
 * Exception thrown if an error which can only be found on runtime occurs.
 *
 * @package NewRelic
 * @category Exception
 */
class RuntimeException extends \RuntimeException implements ExceptionInterface {

}
