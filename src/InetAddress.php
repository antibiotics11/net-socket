<?php

namespace antibiotics11\NetSocket;
use Stringable;
use Override;
use JetBrains\PhpStorm\{Immutable, Pure, ExpectedValues};
use const AF_INET, AF_INET6;
use const DNS_A, DNS_AAAA;
use const FILTER_VALIDATE_IP;
use const FILTER_FLAG_IPV4, FILTER_FLAG_IPV6;

#[Immutable]
final readonly class InetAddress implements Stringable {

  #[Pure]
  public static function isIPv4(string $address): bool {
    return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
  }

  #[Pure]
  public static function isIPv6(string $address): bool {
    return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
  }

  private function __construct(
    public string $address,
    #[ExpectedValues([AF_INET, AF_INET6])]
    public int    $addressFamily,
    public string $hostname = ""
  ) {}

  #[Override]
  public function __toString(): string {
    return $this->address;
  }

  public function equals(InetAddress $other): bool {
    return $this->address === $other->address;
  }

  /**
   * @param string $address
   * @return InetAddress|null
   */
  public static function getByAddress(string $address): ?self {
    if (self::isIPv4($address)) {
      return new self($address, AF_INET);
    } else if (self::isIPv6($address)) {
      return new self($address, AF_INET6);
    }
    return null;
  }

  /**
   * @param string $hostname
   * @return InetAddress|null
   */
  public static function getByHostname(string $hostname): ?self {
    return self::getAllByHostname($hostname)[0] ?? null;
  }

  /**
   * @param string $hostname
   * @return InetAddress[]
   */
  public static function getAllByHostname(string $hostname): array {

    $dnsA = @dns_get_record($hostname, DNS_A);
    $dnsA === false and $dnsA = [];

    $dnsAAAA = @dns_get_record($hostname, DNS_AAAA);
    $dnsAAAA === false and $dnsAAAA = [];

    $dnsRecords = array_merge($dnsA, $dnsAAAA);
    $inetAddresses = [];

    foreach ($dnsRecords as $record) {

      $address = $record["ip"] ?? $record["ipv6"] ?? null;
      if ($address !== null) {
        $addressFamily = AF_INET;
        self::isIPv6($address) and $addressFamily = AF_INET6;
        $inetAddresses[] = new self($address, $addressFamily, $hostname);
      }

    }

    return $inetAddresses;

  }

  public static function getByInput(string $input): ?self {
    return (self::isIPv4($input) || self::isIPv6($input)) ?
      self::getByAddress($input) : self::getByHostname($input);
  }

}