<?php
namespace DigitalCanvas\NewRelic\Exception;

use DigitalCanvas\NewRelic\ExceptionInterface;

/**
 * Exception thrown if a value does not adhere to a defined valid data domain.
 *
 * @package NewRelic
 * @category Exception
 */
class DomainException extends \DomainException implements ExceptionInterface {

}
