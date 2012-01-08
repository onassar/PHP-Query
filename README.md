PHP Query
===

PHP-Query contains a single, instantiable, class, which provides a PHP ORM like
API for plain and straightforward query generation.  
Queries are not executed by the class or ORM, but rather return the SQL
statement that ought to be run against a database resource.  
While it is modelled after the
[ActiveRecord](http://en.wikipedia.org/wiki/Active_record_pattern)
pattern, it follows plain language that I felt would make code easier to read
and maintain.

### API Samples

``` php
<?php

    // select a username's details
    $query = (new Query());
    $query->select('*');
    $query->from('users');
    $query->where('username', 'onassar');
    $parsed = $query->parse();

    // update a user record
    $query = (new Query());
    $query->update('fname', 'Oliver');
    $query->table('users');
    $query->where('username', 'onassar');
    $parsed = $query->parse();

    // insert a user record
    $query = (new Query());
    $query->insert(array(
        'fname', 'Oliver',
        'lname', 'Nassar',
        'username', 'onassar'
    ));
    $query->into('users');
    $parsed = $query->parse();
```

### Performance
Worth mentioning is that this approach to database-access isn&#039;t ideal for
speed and memory. You&#039;re adding an extra, &quot;unrequired&quot;, step for
your application.

Any potential speed or memory hits that you may take, however, could (and
should) be circumvented by using a proper caching engine, class
(see [PHP-MemcachedCache](https://github.com/onassar/PHP-MemcachedCache) and/or
[PHP-APCCache](https://github.com/onassar/PHP-APCCache)) and flow.
