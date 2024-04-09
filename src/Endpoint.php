<?php

namespace antibiotics11\NetSocket;
use Stringable;
use Override;
use InvalidArgumentException;
use JetBrains\PhpStorm\Immutable;
use function preg_match_all;

#[Immutable]
final readonly class Endpoint implements Stringable {

  /**
   * @param InetAddress $inetAddress
   * @param int         $port
   * @throws InvalidArgumentException if the port number is invalid.
   */
  public function __construct(
    public InetAddress $inetAddress,
    public int         $port
  ) {
    if ($this->port < 1 || $this->port > 65535) {
      throw new InvalidArgumentException("Invalid port number: " . $this->port);
    }
  }

  /**
   * Check if this endpoint is equal to another endpoint.
   *
   * @param Endpoint $other
   * @return bool
   */
  public function equals(Endpoint $other): bool {
    return
      $this->inetAddress->equals($other->inetAddress) &&
      $this->port === $other->port;
  }

  /**
   * Create an Endpoint object from a string representation.
   *
   * @param string $endpoint
   * @return self
   * @throws InvalidArgumentException if the string format is invalid or if the address is invalid.
   */
  public static function fromString(string $endpoint): self {

    if (preg_match_all("/^\[?([\w\-.:]+)]?:(\d+)$/", $endpoint, $matches)) {
      $address = $matches[1][0] ?? "";
      $port = $matches[2][0] ?? -1;

      $inetAddress = InetAddress::getByInput($address);
      if ($inetAddress === null) {
        throw new InvalidArgumentException("Invalid address: " . $address);
      }

      return new self($inetAddress, $port);
    }

    throw new InvalidArgumentException("Invalid network endpoint: " . $endpoint);

  }

  #[Override]
  public function __toString(): string {
    return $this->inetAddress . ":" . $this->port;
  }

}