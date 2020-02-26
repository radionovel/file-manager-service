<?php


namespace Radionovel\FileManagerService\Exceptions;

use Exception;
use Throwable;

/**
 * Class CantDeleteException
 * @package FileManager\Exceptions
 */
class CantDeleteException extends Exception
{
    /**
     * InvalidPathException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
