# net-socket

Utility classes for PHP socket programming.

```php
use antibiotics11\NetSocket\{InetAddress, Endpoint};
use antibiotics11\NetSocket\Socket\Wrapper\{Socket, SocketError};

$serverEndpoint = new Endpoint(
  inetAddress: InetAddress::getByAddress("127.0.0.1"),
  port:        12345
);

try {

  $clientSocket = Socket::create(
    domain: Socket::DOMAIN_INET,
    type:   Socket::TYPE_STREAM,
    level:  Socket::LEVEL_TCP
  );
  $clientSocket->connectTo(endpoint: $serverEndpoint);
  $clientSocket->write(data: "Hello, Server!");
  $clientSocket->close();

} catch (SocketError $e) {
  printf("Socket Error: %s\r\n", $e->getSocketErrorMessage());
}
```

## Classes

- **NetSocket\InetAddress:** represents an ip address, domain name, and address family.
- **NetSocket\Endpoint:** represents a network endpoint with InetAddress and port number pairs.
- **NetSocket\Socket\Wrapper\Socket:** wraps PHP Socket object and socket functions.
- **NetSocket\Socket\Wrapper\SocketError:** wraps PHP socket errors.

## Requirements

- PHP >= 8.3.0
- <a href="https://github.com/jetbrains/phpstorm-attributes">jetbrains/phpstorm-attributes</a> >= 1.0

## Installation

```shell
composer require antibiotics11/net-socket:dev-main
```
