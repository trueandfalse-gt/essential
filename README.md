---

# Essential

### Installation

```
composer require trueandfalse/essential
```

## Multitenancy

#### Provider configuration

copy in boostrap/providers.php

```
Trueandfalse\essentail\Providers\EssentialTenantServiceProvider::class,
```

#### Vendor Published

```
php artisan vendor:publish
```

#### Database Configuration

add config/database.php

```
'tenants' => [
    'driver'         => 'mysql',
    'url'            => env('DATABASE_URL_TENANTS'),
    'host'           => env('TENANTS_HOST', '127.0.0.1'),
    'port'           => env('TENANTS_PORT', '3306'),
    'database'       => env('TENANTS_DATABASE', 'forge'),
    'username'       => env('TENANTS_USERNAME', 'forge'),
    'password'       => env('TENANTS_PASSWORD', ''),
    'unix_socket'    => env('TENANTS_SOCKET', ''),
    'charset'        => 'utf8mb4',
    'collation'      => 'utf8mb4_unicode_ci',
    'prefix'         => '',
    'prefix_indexes' => true,
    'strict'         => true,
    'engine'         => null,
    'options'        => extension_loaded('pdo_mysql') ? array_filter([PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),]) : [],]
```

#### use in .env

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

TENANTS_CONNECTION=tenants
TENANTS_HOST=mysql
TENANTS_PORT=3306
TENANTS_DATABASE=app_tenants
TENANTS_USERNAME=root
TENANTS_PASSWORD="password"
```

#### Migrate

```
 php artisan migrate --database=tenants --path=database/migrations/tenants
```
