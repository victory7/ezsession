<?php

$config = [
	'Ezsession' => [
		'mysql'    => [
		    'host'     => $_ENV['DB_HOSTNAME'],
		    'port'     => $_ENV['DB_PORT'],
		    'user'     => $_ENV['DB_USERNAME'],
		    'password' => $_ENV['DB_USERPASS'],
		    'database' => $_ENV['DB_MAIN'],
		    'table'    => 'users_sessions'
	    ],
	    'redis' => [
	        'host' => $_ENV['REDIS_HOST'],
	        'auth' => null,
	    ],
	    'jwt' => [
	        'secret' => $_ENV['JWT_SECRET']
	    ],
	    'cookie' => [
	    	'name' => 'SESSTOKEN',
	        'expires' => (3600 * 2)
	    ]
	]
];