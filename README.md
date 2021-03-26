A database admin dashboard based on the Jaxon ajax library and Adminer
======================================================================

This package inserts a database admin dashboard into an existing PHP application.
Thanks to the [Jaxon library](https://www.jaxon-php.org), it installs and runs in a page of the application, which can be loaded with an HTTP or an Ajax request.
All its operations are performed with Ajax requests.

It is based on [Adminer](https://www.adminer.org/en/), so it will provide the same features.
For example, it will be able to manage MySQL, PostgreSQL, Sqlite, MsSQL, MongoDb and Oracle databases.

Features
--------

- [x] Connect to a MySQL database.
- [x] Show basic info about the MySQL database server.
- [x] Show the list of tables in the connected database.
- [x] Show detailed info about the MySQL database server.
- [x] Show detailed info about the connected database.
- [x] Connect to PostgreSQL databases.
- [ ] Connect to other database types.
- [ ] Execute requests and display results.
- [ ] Add others UI frameworks than Bootstrap, and let the user choose his preferred one.
- [ ] Improve the Adminer code integration.
- [ ] Add tests

Documentation
-------------

Install the jaxon library so it bootstraps from a config file and handles ajax requests. Here's the [documentation](https://www.jaxon-php.org/docs/v3x/advanced/bootstrap.html).

Install this package with Composer. If a [Jaxon plugin](https://www.jaxon-php.org/docs/v3x/plugins/frameworks.html) exists for your framework, you can also install it. It will automate the previous step.

Declare the package and the database servers in the `app` section of the [Jaxon configuration file](https://www.jaxon-php.org/docs/v3x/advanced/bootstrap.html).

```php
    'app' => [
        // Other config options
        // ...
        'packages' => [
            Lagdo\Adminer\Package::class => [
                'servers' => [
                    'first_server' => [
                        'name' => '',     // The name to be displayed in the dashboard UI
                        'driver' => '',   // mysql, pgsql, sqlite, mongo, oracle, mssql or elastic.
                        'host' => '',     // The database host name or address.
                        'port' => 0,      // The database port
                        'username' => '', // The database user credentials
                        'password' => '', // The database user credentials
                    ],
                    'second_server' => [
                        'name' => '',     // The name to be displayed in the dashboard UI
                        'driver' => '',   // mysql, pgsql, sqlite, mongo, oracle, mssql or elastic.
                        'host' => '',     // The database host name or address.
                        'port' => 0,      // The database port
                        'username' => '', // The database user credentials
                        'password' => '', // The database user credentials
                    ],
                ],
            ],
        ],
    ],
```

Insert the CSS and javascript codes in the HTML pages of your application using calls to `jaxon()->getCss()` and `jaxon()->getScript(true)`.

In the page that displays the dashboard, insert the HTML code returned by the call to `jaxon()->package(\Lagdo\Adminer\Package::class)->getHtml()`. Two cases are then possible.

- If the dashboard is displayed on a dedicated page, make a call to `jaxon()->package(\Lagdo\Adminer\Package::class)->ready()` in your PHP code when loading the page.

- If the dashboard is loaded with an Ajax request in a page already displayed, execute the javascript code returned the call to `jaxon()->package(\Lagdo\Adminer\Package::class)->getReadyScript()` after the page is loaded.

Notes
-----

The HTML code of the package uses the [Bootstrap 3](https://getbootstrap.com/) CSS framework, qui which must also be included in the page.
It is entirely contained in a `<div class="row">` tag.

Support for other UI frameworks will be added in future releases.

Contribute
----------

- Issue Tracker: github.com/lagdo/jaxon-adminer/issues
- Source Code: github.com/lagdo/jaxon-adminer

License
-------

The project is licensed under the Apache license.
