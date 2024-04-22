# net-socket

Utility classes for PHP socket programming.

## Classes

- **NetSocket\InetAddress:** represents an ip address, domain name, and address family.
- **NetSocket\Endpoint:** represents a network endpoint with InetAddress and port number pairs.
- **NetSocket\Socket\Wrapper\Socket:** wraps PHP Socket object and socket functions.
- **NetSocket\Socket\Wrapper\SocketError:** wraps PHP socket errors.


## Usage

### TCP server

```php
use antibiotics11\NetSocket\{InetAddress, Endpoint};
use antibiotics11\NetSocket\Socket\Wrapper\{Socket, SocketError};

// Define the server endpoint
$serverEndpoint = new Endpoint(
  inetAddress: InetAddress::getByAddress("127.0.0.1"),
  port:        12345
);

try {

  // Create a server socket
  $serverSocket = Socket::create(
    domain: Socket::DOMAIN_INET,
    type:   Socket::TYPE_STREAM,
    level:  Socket::LEVEL_TCP
  );

  // Bind the server socket to the server endpoint
  $serverSocket->bindTo(endpoint: $serverEndpoint);

  // Start listening for incoming connections
  $serverSocket->listen();

  // Accept an incoming connection
  $clientSocket = $serverSocket->accept();

  // Read data from the client
  $receivedData = $clientSocket->read();

  // Get the remote endpoint of the client
  $clientEndpoint = $clientSocket->getRemoteEndpoint();

  printf("Received \"%s\" from %s\r\n", $receivedData, $clientEndpoint);

  // Shut down the client socket
  $clientSocket->shutdown();

  // Close the server socket
  $serverSocket->close();

} catch (SocketError $e) {
  printf("Socket Error: %s\r\n", $e->getSocketErrorMessage());
}

```

### TCP client

```php
use antibiotics11\NetSocket\{InetAddress, Endpoint};
use antibiotics11\NetSocket\Socket\Wrapper\{Socket, SocketError};

// Define the server endpoint to connect to
$serverEndpoint = new Endpoint(
  inetAddress: InetAddress::getByAddress("127.0.0.1"),
  port:        12345
);

try {

  // Create a client socket
  $clientSocket = Socket::create(
    domain: Socket::DOMAIN_INET,
    type:   Socket::TYPE_STREAM,
    level:  Socket::LEVEL_TCP
  );

  // Connect the client socket to the server endpoint
  $clientSocket->connectTo(endpoint: $serverEndpoint);

  // Send data to the server
  $clientSocket->write(data: "Hello, Server!");

  // Close the client socket
  $clientSocket->close();

} catch (SocketError $e) {
  printf("Socket Error: %s\r\n", $e->getSocketErrorMessage());
}
```


## Requirements

- PHP >= 8.3.0
- <a href="https://github.com/jetbrains/phpstorm-attributes">jetbrains/phpstorm-attributes</a> >= 1.0

## Installation

```shell
composer require antibiotics11/net-socket:dev-main
```
