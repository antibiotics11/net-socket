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
    #[ExpectedValues([ self::ADDRESS_FAMILY_IPV4, self::ADDRESS_FAMILY_IPV6 ])]
    public int    $addressFamily,
    public string $hostname = ""
  ) {}

  #[Override]
  public function __toString(): string {
    return $this->address;
  }

  /**
   * Check if this InetAddress object is equal to another InetAddress object.
   *
   * @param InetAddress $other the InetAddress object to compare with.
   * @return bool
   */
  public function equals(InetAddress $other): bool {
    return $this->address       === $other->address       &&
           $this->addressFamily === $other->addressFamily &&
           $this->hostname      === $other->hostname;
  }

  /**
   * Create an InetAddress object using the given IP address.
   *
   * @param string $address
   * @return InetAddress|null the InetAddress object, or null if the address is invalid.
   */
  public static function getByAddress(string $address): ?self {
    if (self::isIPv4($address)) {
      return new self($address, self::ADDRESS_FAMILY_IPV4);
    } else if (self::isIPv6($address)) {
      return new self($address, self::ADDRESS_FAMILY_IPV6);
    }
    return null;
  }

  /**
   * Retrieve an InetAddress object using the given hostname.
   *
   * @param string $hostname
   * @return InetAddress|null the InetAddress object, or null if the hostname is invalid or no IP address is found.
   */
  public static function getByHostname(string $hostname): ?self {
    return self::getAllByHostname($hostname)[0] ?? null;
  }

  /**
   * Retrieve all InetAddress objects associated with the given hostname.
   *
   * @param string $hostname
   * @return InetAddress[] an array of InetAddress objects representing all IP addresses associated with the hostname.
   */
  public static function getAllByHostname(string $hostname): array {

    // Retrieve DNS records associated with the hostname.
    $dnsA    = @dns_get_record($hostname, DNS_A);
    $dnsAAAA = @dns_get_record($hostname, DNS_AAAA);

    $dnsA    === false and $dnsA = [];
    $dnsAAAA === false and $dnsAAAA = [];

    $dnsRecords = array_merge($dnsA, $dnsAAAA);
    $inetAddresses = [];

    // Create InetAddress objects for each IP address.
    foreach ($dnsRecords as $record) {

      $address = $record["ip"] ?? $record["ipv6"] ?? null;
      if ($address !== null) {
        $addressFamily = self::ADDRESS_FAMILY_IPV4;
        self::isIPv6($address) and $addressFamily = self::ADDRESS_FAMILY_IPV6;
        $inetAddresses[] = new self($address, $addressFamily, $hostname);
      }

    }

    return $inetAddresses;

  }

  /**
   * Retrieve an InetAddress object using the given string, which can be either an IP address or a hostname.
   *
   * @param string $input
   * @return self|null
   */
  public static function getByInput(string $input): ?self {
    return (self::isIPv4($input) || self::isIPv6($input)) ?
      self::getByAddress($input) : self::getByHostname($input);
  }

  public const int ADDRESS_FAMILY_IPV4 = AF_INET;
  public const int ADDRESS_FAMILY_IPV6 = AF_INET6;

}