# ezsession - Write & Read On-Demand with Easy JWT Access

`ezsession` is a PHP session management library focused on an on-demand write and read strategy to optimize session data handling. It efficiently manages sessions using in-memory cache (e.g., Redis), permanent storage (e.g., MySQL), and JWT tokens, minimizing unnecessary storage queries and providing easy access to JWT for custom data handling. This approach ensures that session data is only written when required, significantly reducing storage operations and improving overall performance.

## Features

- **Write-On-Demand**: Data is only written to storage when necessary, reducing the number of storage operations.
- **Flexible Backend Storage**: Uses Redis, memcached, APCu, ... for caching, SQLight, MySQL, Postgres, MongoDB, ... for persistent storage, and JWT for stateless session management.
- **Easy Integration**: Plug-and-play session management for PHP applications.
- **Scalable & Secure**: Optimizes read and write operations to ensure data persistence and secure session management.

## Requirements

- PHP 7.4 or higher
- Redis server (for caching backend)
- MySQL server (for persistent storage)

## Installation

You can install `ezsession` using Composer. Simply run:

```sh
composer require victory7/ezsession
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

### How It Works

`ezsession` handles session data efficiently by using a combination of JWT tokens, Redis, and MySQL to reduce unnecessary storage operations:

1. **Session Initialization**: On the first request, `ezsession` generates a session ID using a UUID and issues a JWT token signed with your application secret key. This token is returned as a session cookie (with the name defined in the configuration).

2. **Subsequent Requests**: For the rest of the requests, the session token is retrieved either via the same cookie or from the `Authorization` Bearer header. After validation, `ezsession` checks the `"stored"` flag in the JWT:
   - If the `"stored"` flag is `false`, it means there is no data in the cache or permanent storage, so the JWT value is returned to `$_SESSION` and is accessible via `$_SESSION['jwt']`.
   - If nothing has been written to the session, `ezsession` does not write anything to cache or database and does not perform any queries.

3. **Writing to Session**: When something is written to the session (e.g., `$_SESSION['name'] = 'John';`), `ezsession` stores this data in both Redis and MySQL, updates the `"stored"` flag to `true`, and generates a new JWT token. This updated token is then returned to the client, replacing the previous token automatically.

4. **Data Retrieval**: When the `"stored"` flag is `true`, `ezsession` first checks Redis for the data:
   - If the data is found in Redis, it is returned.
   - If not, `ezsession` queries MySQL, and if the data is found, it caches it in Redis for future requests.

5. **Unset Session Values**: If all session values are unset, the `"stored"` flag is set back to `false`, and requests are handled purely through the JWT without accessing cache or database.

### Working with JWT Data

A key feature of `ezsession` is the ability to add, modify, or delete custom data in the JWT token directly:

- Access the JWT data using `$_SESSION['jwt']`. For example, you can add a `user_id` to the JWT like this:

```php
$_SESSION['jwt']['user_id'] = 'aaBBcc1212';
```

- Modifying the JWT in this way forces `ezsession` to regenerate the token and send it back through subsequent requests, minimizing session storage queries.

### Example Usage

```php
// Adding custom data to JWT
$_SESSION['jwt']['role'] = 'admin';
```

## Contributing

Contributions are welcome! Feel free to fork this repository, make your changes, and submit a pull request. Let's make session management easier for everyone.

## License

`ezsession` is licensed under the MIT License. See [LICENSE](LICENSE) for more details.

## Support

If you encounter any issues or have questions, please open an issue on GitHub or contact me at [ali.poorbazargan@gmail.com](mailto:ali.poorbazargan@gmail.com).

## Future Improvements

- **Enhanced JWT Features**: Add more advanced JWT functionality, such as expiration handling.
- **Flexible Backends**: Support for additional databases and caching systems.
- **Improved Performance**: Further optimize the read/write strategies.

---

Thank you for using `ezsession`! We hope it makes your session management process more enjoyable.
