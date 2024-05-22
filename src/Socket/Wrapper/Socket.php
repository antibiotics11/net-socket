<?php

namespace antibiotics11\NetSocket\Socket\Wrapper;
use Socket as RawSocket;
use Throwable;
use antibiotics11\NetSocket\{Endpoint, InetAddress};
use JetBrains\PhpStorm\ExpectedValues;
use function socket_accept;
use function socket_bind;
use function socket_connect;
use function socket_create;
use function socket_listen;
use function socket_read;
use function socket_write;
use function socket_select;
use function socket_recv;
use function socket_recvfrom;
use function socket_send;
use function socket_sendto;
use function socket_close;
use function socket_shutdown;
use function socket_atmark;
use function socket_set_block;
use function socket_set_nonblock;
use function socket_set_option;
use function socket_get_option;
use function socket_getsockname;
use function socket_getpeername;
use function socket_last_error;
use function socket_strerror;
use function socket_clear_error;
use function strlen;
use function array_keys;
use function array_intersect_key;
use function array_flip;

class Socket {

  /**
   * @param RawSocket $rawSocket      a socket resource.
   * @param bool      $isSocketClosed whether the socket is closed.
   */
  public function __construct(
    protected RawSocket $rawSocket,
    protected bool      $isSocketClosed  = false
  ) {}

  public function __destruct() {
    // close the socket if it's not closed.
    $this->isSocketClosed or $this->close();
  }

  /**
   * Accept a connection on the socket.
   *
   * @return Socket
   * @throws SocketError if the socket_accept() call fails.
   */
  public function accept(): self {
    if (false !== $acceptedSocket = @socket_accept($this->rawSocket)) {
      return new self($acceptedSocket);
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Bind an endpoint to the socket.
   *
   * @param Endpoint $endpoint
   * @return void
   * @throws SocketError if the socket_bind() call fails.
   */
  public function bindTo(Endpoint $endpoint): void {
    if (!@socket_bind($this->rawSocket, $endpoint->inetAddress, $endpoint->port)) {
      $this->throwErrorFromLastOperation();
    }
  }

  /**
   * Initialize a connection on the socket.
   *
   * @param Endpoint $endpoint
   * @return void
   * @throws SocketError if the socket_connect() call fails.
   */
  public function connectTo(Endpoint $endpoint): void {
    if (!@socket_connect($this->rawSocket, $endpoint->inetAddress, $endpoint->port)) {
      $this->throwErrorFromLastOperation();
    }
  }

  /**
   * Create a socket.
   *
   * @param int $domain
   * @param int $type
   * @param int $level
   * @return Socket
   * @throws SocketError if the socket_create() call fails.
   */
  public static function create(
    #[ExpectedValues([ self::DOMAIN_UNIX, self::DOMAIN_INET, self::DOMAIN_INET6 ])]
    int $domain,
    #[ExpectedValues([ self::TYPE_STREAM, self::TYPE_DATAGRAM, self::TYPE_RAW, self::TYPE_RDM, self::TYPE_SEQPACKET ])]
    int $type,
    #[ExpectedValues([ self::LEVEL_SOCKET, self::LEVEL_TCP, self::LEVEL_UDP ])]
    int $level
  ): self {
    if (false !== $createdSocket = @socket_create($domain, $type, $level)) {
      return new self($createdSocket);
    }
    $errorCode = socket_last_error();
    throw new SocketError(socket_strerror($errorCode), $errorCode);
  }

  /**
   * Listen for a connection on the socket.
   *
   * @param int $backlogQueueSize
   * @return void
   * @throws SocketError if the socket_listen() call fails.
   */
  public function listen(int $backlogQueueSize = 0): void {
    if (!@socket_listen($this->rawSocket, $backlogQueueSize)) {
      $this->throwErrorFromLastOperation();
    }
  }

  /**
   * Read a maximum of length bytes from the socket.
   *
   * @param int $length
   * @param int $mode
   * @return string
   * @throws SocketError if the socket_read() call fails.
   */
  public function read(
    int $length = 65535,
    #[ExpectedValues([ self::READ_BINARY, self::READ_NORMAL ])]
    int $mode = self::READ_BINARY
  ): string {
    if (false !== $readData = @socket_read($this->rawSocket, $length, $mode)) {
      return $readData;
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Write to the socket.
   *
   * @param string $data
   * @param int|null $length
   * @return int
   * @throws SocketError if the socket_write() call fails.
   */
  public function write(string $data, ?int $length = null): int {
    $length ??= strlen($data);
    if (false !== $writtenBytes = @socket_write($this->rawSocket, $data, $length)) {
      return $writtenBytes;
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Receive data from the socket.
   *
   * @param int $length
   * @param int $flags
   * @return string
   * @throws SocketError if the socket_recv() call fails.
   */
  public function receive(int $length = 65535, int $flags = 0): string {
    if (false !== @socket_recv($this->rawSocket, $receivedData, $length, $flags)) {
      return $receivedData;
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Receive data from the socket whether it is connection-oriented or not.
   *
   * @param Endpoint|null $endpoint
   * @param int $length
   * @param int $flags
   * @return string
   * @throws SocketError if the socket_recvfrom() call fails.
   */
  public function receiveFrom(Endpoint &$endpoint = null, int $length = 65535, int $flags = 0): string {
    if (false !==
      @socket_recvfrom($this->rawSocket, $data, $length, $flags, $peerAddress, $peerPort)
    ) {
      $endpoint = new Endpoint(InetAddress::getByAddress($peerAddress), $peerPort);
      return $data;
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Send data to the socket.
   *
   * @param string $data
   * @param int|null $length
   * @param int $flags
   * @return int
   * @throws SocketError if the socket_send() call fails.
   */
  public function send(string $data, ?int $length = null, int $flags = 0): int {
    $length ??= strlen($data);
    if (false !== $sentBytes = @socket_send($this->rawSocket, $data, $length, $flags)) {
      return $sentBytes;
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Send a message to the socket whether it is connected or not.
   *
   * @param Endpoint $endpoint
   * @param string $data
   * @param int|null $length
   * @param int $flags
   * @return int
   * @throws SocketError if the socket_sendto() call fails.
   */
  public function sendTo(Endpoint $endpoint, string $data, ?int $length = null, int $flags = 0): int {
    $length ??= strlen($data);
    if (false !== $sentBytes =
        @socket_sendto($this->rawSocket, $data, $length, $flags, $endpoint->inetAddress, $endpoint->port)
    ) {
      return $sentBytes;
    }
    $this->throwErrorFromLastOperation();

  }

  /**
   * @param array|null $readSockets   an array of sockets to check for reading.
   * @param array|null $writeSockets  an array of sockets to check for writing.
   * @param array|null $exceptSockets an array of sockets to check for errors.
   * @param float      $timeout       the timeout in seconds.
   * @return int the number of sockets ready for reading, writing or having an error.
   * @throws SocketError if the socket_select() call fails.
   */
  public static function select(
    ?array &$readSockets   = null,
    ?array &$writeSockets  = null,
    ?array &$exceptSockets = null,
    float  $timeout        = 1.0
  ): int {

    // prepare arrays of socket resources
    $getRawSockets = static function (?array $sockets): ?array {
      if ($sockets === null) {
        return null;
      }
      $rawSockets = [];
      foreach ($sockets as $socket) {
        $rawSockets[] = $socket->getRawSocket();
      }
      return $rawSockets;
    };

    // Update the original socket arrays with the selected sockets
    $updateSockets = static function (?array &$originalSockets, ?array $selectedSockets): void {
      if ($originalSockets !== null) {
        $originalSockets = array_intersect_key(
          $originalSockets,
          array_flip(array_keys($selectedSockets))
        );
      }
    };

    $read   = $getRawSockets($readSockets);
    $write  = $getRawSockets($writeSockets);
    $except = $getRawSockets($exceptSockets);

    $seconds      = (int)$timeout;
    $microSeconds = (int)(($timeout - $seconds) * 1000000);

    try {
      $result = socket_select($read, $write, $except, $seconds, $microSeconds);

      $updateSockets($readSockets,   $read);
      $updateSockets($writeSockets,  $write);
      $updateSockets($exceptSockets, $except);

      return $result;
    } catch (Throwable $e) {
      throw new SocketError($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Close the socket instance.
   *
   * @return void
   */
  public function close(): void {
    $this->clearError();
    socket_close($this->rawSocket);
    $this->isSocketClosed = true;
  }

  /**
   * Shut down the socket for receiving, sending, or both.
   *
   * @param int $mode
   * @return bool
   */
  public function shutdown(
    #[ExpectedValues([ self::SHUTDOWN_READ, self::SHUTDOWN_WRITE, self::SHUTDOWN_ALL ])]
    int $mode = self::SHUTDOWN_ALL
  ): bool {
    return socket_shutdown($this->rawSocket, $mode);
  }

  public function setBlocking(): bool {
    return socket_set_block($this->rawSocket);
  }

  public function setNonBlocking(): bool {
    return socket_set_nonblock($this->rawSocket);
  }

  /**
   * Set socket options for this socket.
   *
   * @param int $level
   * @param int $option
   * @param mixed $value
   * @return void
   * @throws SocketError
   */
  public function setOption(
    #[ExpectedValues([ self::LEVEL_SOCKET, self::LEVEL_TCP, self::LEVEL_UDP ])]
    int $level,
    int $option,
    mixed $value
  ): void {
    if (!@socket_set_option($this->rawSocket, $level, $option, $value)) {
      $this->throwErrorFromLastOperation();
    }
  }

  /**
   * Get socket option for this socket.
   *
   * @param int $level
   * @param int $option
   * @return int|array
   * @throws SocketError
   */
  public function getOption(
    #[ExpectedValues([ self::LEVEL_SOCKET, self::LEVEL_TCP, self::LEVEL_UDP ])]
    int $level,
    int $option
  ): int|array {
    if (false !== $option = @socket_get_option($this->rawSocket, $level, $option)) {
      return $option;
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Get the local endpoint of the socket.
   *
   * @return Endpoint
   * @throws SocketError if the socket_getsockname() call fails.
   */
  public function getLocalEndpoint(): Endpoint {
    if (@socket_getsockname($this->rawSocket, $localAddress, $localPort)) {
      return new Endpoint(InetAddress::getByAddress($localAddress), $localPort);
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Get the remote endpoint of the socket.
   *
   * @return Endpoint
   * @throws SocketError if the socket_getpeername() call fails.
   */
  public function getRemoteEndpoint(): Endpoint {
    if (@socket_getpeername($this->rawSocket, $peerAddress, $peerPort)) {
      return new Endpoint(InetAddress::getByAddress($peerAddress), $peerPort);
    }
    $this->throwErrorFromLastOperation();
  }

  /**
   * Get the socket resource.
   *
   * @return RawSocket
   */
  public function getRawSocket(): RawSocket {
    return $this->rawSocket;
  }

  /**
   * Check if the socket is closed.
   *
   * @return bool
   */
  public function isClosed(): bool {
    return $this->isSocketClosed;
  }

  /**
   * Check if the socket is at out-of-band (OOB) mark.
   *
   * @return bool
   */
  public function isAtOOBMark(): bool {
    return socket_atmark($this->rawSocket);
  }

  /**
   * Get the last error code associated with the socket.
   *
   * @return int
   */
  public function getLastError(): int {
    return socket_last_error($this->rawSocket);
  }

  /**
   * Get the error message associated with a given error code.
   *
   * @param int $lastError
   * @return string
   */
  public function getErrorMessage(int $lastError): string {
    return socket_strerror($lastError);
  }

  /**
   * Clear the error status on the socket.
   *
   * @return void
   */
  public function clearError(): void {
    socket_clear_error($this->rawSocket);
  }

  /**
   * @return void
   * @throws SocketError if the last socket operation fails.
   */
  protected function throwErrorFromLastOperation(): void {
    $lastError = $this->getLastError();
    throw new SocketError($this->getErrorMessage($lastError), $lastError);
  }

  public const int DOMAIN_UNIX    = 1;  // AF_UNIX
  public const int DOMAIN_INET    = 2;  // AF_INET
  public const int DOMAIN_INET6   = 10; // AF_INET6

  public const int TYPE_STREAM    = 1;  // SOCK_STREAM
  public const int TYPE_DATAGRAM  = 2;  // SOCK_DGRAM
  public const int TYPE_RAW       = 3;  // SOCK_RAW
  public const int TYPE_RDM       = 4;  // SOCK_RDM
  public const int TYPE_SEQPACKET = 5;  // SOCK_SEQPACKET

  public const int LEVEL_SOCKET   = 1;  // SOL_SOCKET
  public const int LEVEL_TCP      = 6;  // SOL_TCP
  public const int LEVEL_UDP      = 17; // SOL_UDP

  public const int READ_NORMAL    = 1;  // PHP_NORMAL_READ
  public const int READ_BINARY    = 2;  // PHP_BINARY_READ

  public const int SHUTDOWN_READ  = 0;
  public const int SHUTDOWN_WRITE = 1;
  public const int SHUTDOWN_ALL   = 2;

}