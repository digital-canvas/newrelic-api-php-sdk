<?php
namespace DigitalCanvas\NewRelic\Exception;

use DigitalCanvas\NewRelic\ExceptionInterface;

/**
 * Exception thrown if an argument does not match with the expected value.
 *
 * @package NewRelic
 * @category Exception
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface {

}
