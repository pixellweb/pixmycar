<?php


namespace Citadelle\PixMyCar\app;


class PixMyCarException extends \Exception
{
    /**
     * PixMyCarException constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        \Log::channel('pixmycar')->alert($message.($previous ? ' => '.$previous->getMessage() : ''));
    }
}
