# ezsession

`ezsession` is a PHP session management library that provides a powerful and flexible way to handle sessions using multiple backends like Redis, MySQL, and JWT tokens. It aims to make session handling easier, more secure, and highly scalable.

## Features

- **Multi-Backend Support**: Uses Redis for caching, MySQL for persistent storage, and JWT for stateless session management.
- **Custom Session Handler**: Implements `SessionHandlerInterface` for custom session handling.
- **Easy Integration**: Plug-and-play session management for PHP applications.
- **Scalable & Secure**: Optimizes read and write operations to ensure data persistence and secure session management.

## Requirements

- PHP 7.4 or higher
- Redis server (for caching backend)
- MySQL server (for persistent storage)

## Installation

You can install `ezsession` using Composer. Simply run:

```sh
composer require yourusername/ezsession
```

Then, include the autoloader in your script:

```php
require 'vendor/autoload.php';
```

## Configuration

### Redis & MySQL Setup
Before using `ezsession`, make sure you have access to a running Redis and MySQL server. Update your database credentials and Redis configuration accordingly.

### Usage

To use `ezsession` in your PHP application, initialize the session handler and start a session as follows:

```php
use Ezsession\Ezsession;

// Initialize Ezsession with your Redis and MySQL configuration
$sessionHandler = new Ezsession(
    [
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
        ],
        'mysql' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'ezsession_db',
        ],
        'jwt_secret' => 'your_secret_key_here',
    ]
);

session_set_save_handler($sessionHandler, true);
session_start();

// Example usage
$_SESSION['user_id'] = 123;
$_SESSION['username'] = 'john_doe';
```

### Read and Write Strategy
`ezsession` uses a combination of Redis and MySQL to optimize read and write performance:
- **Writes**: Critical data is written to both Redis and MySQL to ensure persistence.
- **Reads**: Data is primarily read from Redis for faster access, with fallback to MySQL if needed.
- **JWT**: For session identification, JWT tokens can be generated to enable stateless session management.

## Methods

### Create JWT Token
Generate a new JWT token for session identification:

```php
$token = $sessionHandler->createJWT(['user_id' => 123, 'username' => 'john_doe']);
```

### Save to Cache and MySQL
Save data directly to Redis or MySQL:

```php
$sessionHandler->saveToCache('user_id', 123);
$sessionHandler->saveToDatabase('username', 'john_doe');
```

### Retrieve Data
Retrieve data from Redis or MySQL:

```php
$userId = $sessionHandler->getFromCache('user_id');
$username = $sessionHandler->getFromDatabase('username');
```

### Decode JWT Token
Decode and validate a JWT token to retrieve session information:

```php
$data = $sessionHandler->decodeJWT($token);
```

## Contributing

Contributions are welcome! Feel free to fork this repository, make your changes, and submit a pull request. Let's make session management easier for everyone.

## License

`ezsession` is licensed under the MIT License. See [LICENSE](LICENSE) for more details.

## Support

If you encounter any issues or have questions, please open an issue on GitHub or contact me at [your.email@example.com](mailto:your.email@example.com).

## Future Improvements

- **Enhanced JWT Features**: Add more advanced JWT functionality, such as expiration handling.
- **Flexible Backends**: Support for additional databases and caching systems.
- **Improved Performance**: Further optimize the read/write strategies.

---

Thank you for using `ezsession`! We hope it makes your session management process more enjoyable.
