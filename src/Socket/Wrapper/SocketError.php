<?php

namespace antibiotics11\NetSocket\Socket\Wrapper;
use Exception, Throwable;

final class SocketError extends Exception {

  /**
   * @param string $socketErrorMessage the error message returned by the socket_strerror() call.
   * @param int    $socketErrorCode    the error code returned by the socket_last_error() call.
   * @param Throwable|null $previous
   */
  public function __construct(string $socketErrorMessage, int $socketErrorCode = 0, ?Throwable $previous = null) {
    parent::__construct($socketErrorMessage, $socketErrorCode, $previous);
  }

  public final function getSocketErrorMessage(): string {
    return $this->getMessage();
  }

}