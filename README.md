# net-socket

Utility classes for socket programming.

## Classes

- **NetSocket\InetAddress:** represents the ip address, domain name, and address family.
- **NetSocket\Endpoint:** represents a network endpoint with InetAddress and port number pairs.
- **NetSocket\Socket\Wrapper\Socket:** wraps PHP Socket object and socket functions.
- **NetSocket\Socket\Wrapper\Exception\SocketError:** wraps PHP socket errors.

## Requirements

- PHP >= 8.3.0
- <a href="https://github.com/jetbrains/phpstorm-attributes">jetbrains/phpstorm-attributes</a> >= 1.0