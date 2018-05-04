# Wildfire

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]][link-license]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Wildfire is a wrapper for [Query Builder Class](https://codeigniter.com/user_guide/database/query_builder.html) from the [Codeigniter](https://codeigniter.com) framework. This library was also heavily inspired by the [Eloquent ORM](https://laravel.com/docs/5.6/eloquent) from Laravel.

## Install

Via [Composer](https://getcomposer.org):

``` bash
$ composer require rougin/wildfire
```

## Usage

### Preparation

``` sql
-- Import this script to a SQLite database

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name TEXT NOT NULL,
    age INTEGER NOT NULL,
    gender TEXT NOT NULL,
    accepted INTEGER DEFAULT 0
);

INSERT INTO users (name, age, gender) VALUES ('Rougin', 20, 'male');
INSERT INTO users (name, age, gender) VALUES ('Royce', 18, 'male');
INSERT INTO users (name, age, gender) VALUES ('Angel', 19, 'female');
```

``` php
// application/config/config.php

$config['composer_autoload'] = TRUE; // or the specified path of "vendor/autoload.php";
```

``` php
// application/models/User.php

class User extends \Rougin\Wildfire\Model {}
```

``` php
// application/controllers/Welcome.php

// Loads the database connection 
$this->load->database();

// Enables the inflector helper. It is
// being used to determine the class or
// the model name to use based from the
// given table name from the Wildfire.
$this->load->helper('inflector');

// Loads the required model
$this->load->model('user');
```

### Using the `Wildfire` instance with `CI_DB`

``` php
// application/controllers/Welcome.php

use Rougin\Wildfire\Wildfire;

// Passes the existing \CI_DB instance
$wildfire = new Wildfire($this->db);

// Can be also extended from \CI_DB instance
$wildfire->like('name', 'Royce', 'both');

// Returns an array of User-based objects
$users = $wildfire->get('users')->result();
```

### Using the `Wildfire` instance with `CI_DB_result`

``` php
// application/controllers/Welcome.php

use Rougin\Wildfire\Wildfire;

$query = 'SELECT p.* FROM post p';

// Create raw SQL queries here...
$result = $this->db->query($query);

// ...or even the result of $this->db->get()
$result = $this->db->get('users');

// Pass the result as the argument
$wildfire = new Wildfire($result);

// Returns an array of User-based objects
$users = $wildfire->result('User');
```

### `Model` properties

#### Casting attributes to native types

``` php
// application/models/User.php

class User extends \Rougin\Wildfire\Model {

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = array('accepted' => 'boolean')

}
```

``` json
{
    "id": 1,
    "name": "Rougin",
    "age": "20",
    "gender": "male",
    "accepted": false,
}
```

Notice that the value of `accepted` was changed from string integer (`'0'`) into native boolean (`false`). If not specified (e.g `age` field), all values will be returned as string except the `id` field (which will be automatically casted as native integer) by default.

#### Hiding attributes for serialization

``` php
// application/models/User.php

class User extends \Rougin\Wildfire\Model {

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = array('gender');

}
```

``` json
{
    "id": 1,
    "name": "Rougin",
    "age": "20",
    "accepted": "0",
}
```

The `gender` field was not included in the result.

#### Visible attributes for serialization

``` php
// application/models/User.php

class User extends \Rougin\Wildfire\Model {

    /**
     * The attributes that should be visible for serialization.
     *
     * @var array
     */
    protected $visible = array('gender');

}
```

``` json
{
    "gender": "male"
}
```

As contrast to the `hidden` attribute, only the `gender` field was displayed in the result because it was the only field specified the in `visible` property of the `User` model.

## Migrating from `v0.4.0`

### Change the `CodeigniterModel` class to `Model` class

This also applies to `Wildfire` used as a `CI_Model` as well.

**Before**

``` php
// application/models/User.php

class User extends \Rougin\Wildfire\CodeigniterModel {}
```

**After**

``` php
// application/models/User.php

class User extends \Rougin\Wildfire\Model {}
```

### Change the arguments for `PaginateTrait::paginate`

**Before**

``` php
// application/controllers/Welcome.php

// PaginateTrait::paginate($perPage, $config = array())
list($result, $links) = $this->user->paginate(5, $config);
```

**After**

``` php
// application/controllers/Welcome.php

$total = $this->db->count_all_results('users');

// PaginateTrait::paginate($perPage, $total, $config = array())
list($offset, $links) = $this->user->paginate(5, $total, $config);
```

The total count must be passed in the second parameter.

### Change the arguments for `Wildfire::__construct`

**Before**

``` php
// application/controllers/Welcome.php

$query = $this->db->query('SELECT * FROM users');

// Wildfire::__construct($database = null, $query = null)
$wildfire = new Wildfire($this->db, $query);
```

**After**

``` php
// application/controllers/Welcome.php

// $this->db->query returns a CI_DB_result class
$query = $this->db->query('SELECT * FROM users');

// Wildfire::__construct($data)
$wildfire = new Wildfire($query);
```

If the data is a `CI_DB_result`, it should be passed on the first parameter.

### Change the method `Wildfire::asDropdown` to `Wildfire::dropdown`

**Before**

``` php
// application/controllers/Welcome.php

// Wildfire::asDropdown($description = 'description')
$dropdown = $wildfire->asDropdown('name');
```

**After**

``` php
// application/controllers/Welcome.php

// Wildfire::dropdown($column)
$dropdown = $wildfire->dropdown('name');
```

Also take note that there is no default value in the argument.

### Replace `$delimiters` with `$id` in `Wildfire::find`

**Before**

``` php
// application/controllers/Welcome.php

$delimiters = array('name' => 'Rougin');

// Wildfire::find($table, $delimiters = array())
$users = $wildfire->find('users', $delimiters);
```

**After**

``` php
// application/controllers/Welcome.php

$this->db->where('name', (string) 'Rougin');

$users = $wildfire->get('users')->result();
```

Use only `Wildfire::find` to return single row data.

``` php
// application/controllers/Welcome.php

// Wildfire::find($table, $id)
$user = $wildfire->find('users', 1);
```

### Remove `set_database` and `set_query` methods

**Before**

``` php
// application/controllers/Welcome.php

use Rougin\Wildfire\Wildfire;

$wildfire = new Wildfire;

$wildfire->set_database($this->db);

$query = $this->db->query('SELECT * FROM users');

$wildfire->set_query($query);
```

**After**

``` php
// application/controllers/Welcome.php

use Rougin\Wildfire\Wildfire;

$wildfire = new Wildfire($this->db);

// or

$query = $this->db->query('SELECT * FROM users');

$wildfire = new Wildfire($query);
```

The `Wildfire` parameter must be defined in either `CI_DB_query_builder` (`$this->db`) or `CB_DB_result` instances.

## Change Log

Please see [CHANGELOG][link-changelog] for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email rougingutib@gmail.com instead of using the issue tracker.

## Credits

- [Rougin Royce Gutib][link-author]
- [All contributors][link-contributors]

## License

The MIT License (MIT). Please see [LICENSE][link-license] for more information.

[ico-version]: https://img.shields.io/packagist/v/rougin/wildfire.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/rougin/wildfire/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/rougin/wildfire.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/rougin/wildfire.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/rougin/wildfire.svg?style=flat-square

[link-author]: https://github.com/rougin
[link-author]: https://rougin.github.io
[link-changelog]: https://github.com/rougin/wildfire/blob/master/CHANGELOG.md
[link-code-quality]: https://scrutinizer-ci.com/g/rougin/wildfire
[link-contributors]: https://github.com/rougin/wildfire/contributors
[link-downloads]: https://packagist.org/packages/rougin/wildfire
[link-license]: https://github.com/rougin/wildfire/blob/master/LICENSE.md
[link-packagist]: https://packagist.org/packages/rougin/wildfire
[link-scrutinizer]: https://scrutinizer-ci.com/g/rougin/wildfire/code-structure
[link-travis]: https://travis-ci.org/rougin/wildfire