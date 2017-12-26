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
    $query = new Query();
    $query->select('*');
    $query->from('users');
    $query->where('username', 'onassar');
    $parsed = $query->parse();

    // update a user record
    $query = new Query();
    $query->update('users');
    $query->set('fname', 'Oliver');
    $query->where('username', 'onassar');
    $parsed = $query->parse();

    // insert a user record
    $query = new Query();
    $query->insert(array(
        'fname' => 'Oliver',
        'lname' => 'Nassar',
        'username' => 'onassar'
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

### Update
Regarding the section above, I've stumbled upon some information suggesting that
during the filter stage of generating a query, the performance will be effected
if a `varchar` column has it's value compared without apostrophes. Presumably,
the converse is true.

As an example, `U.username = 12345` should be written as
`U.username = '12345'`.

The `Query` class presumes that all columns are varchars, and thus wraps the
values in apostrophes. The only exception to this is when a third parameter is
pased to the `where` method.

`$query->where('user_id', 1)` will be rendered as `WHERE user_id = '1'` vs the
proper `WHERE user_id = '1'`. Passing in that third parameter forces the
apostrophes to be left off the value definition.

Keep this in mind if you're concerned about the performance of oftenly executed
queries, especially if they're part of any indices.
