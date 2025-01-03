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
 php artisan vendor:publish --tag=essentenant-migrations
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
 # DB_DATABASE=
 # DB_USERNAME=
 # DB_PASSWORD=

 TENANTS_CONNECTION=tenants
 TENANTS_HOST=mysql
 TENANTS_PORT=3306
 TENANTS_DATABASE=app_tenants
 TENANTS_USERNAME=root
 TENANTS_PASSWORD="password"
```

#### Migrate

Connection Tenants migrate

```
 php artisan migrate --database=tenants --path=database/migrations/tenants
```

#### Tenants migrate

```
 php artisan migrate:tenants
```

#### Tenants Seed

```
 php artisan db:tenants
```

## Authenticated Access

#### Provider configuration

copy in boostrap/providers.php

```
 Trueandfalse\essentail\Providers\EssentialAccessServiceProvider::class,
```

#### Vendor Published

```
 php artisan vendor:publish --tag=essenauth-migrations
 php artisan vendor:publish --tag=essenauth-models
 php artisan vendor:publish --tag=essenauth-seeders
```

#### Authentication Migrate, Seeder

```
 php artisan migrate
 php artisan db:seed --class=AuthDefaultSeeder
 php artisan db:seed
```

## Vue

### Configuration Vite components

```
resolve: {
        alias: {
            '@': '/resources/js',
            '@essen': path.resolve(__dirname, 'vendor/trueandfalse/essential/src/resources/js'),
        },
    },
```

### Configuration app.js components

Example inertial

```
resolve: name => {
        let page = null;
        if (name.startsWith('Essen::')) {
            const componentName = name.replace('Essen::', '');
            const pages = import.meta.glob('@essen/**/*.vue', { eager: true });
            page = pages[`/vendor/trueandfalse/essential/src/resources/js/Pages/${componentName}.vue`];
        } else {
            const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
            page = pages[`./Pages/${name}.vue`];
        }

        if (page.default.layout === undefined) {
            page.default.layout = Layout;
        }

        return page;
    }
```
