PHP Query
===

PHP-Query contains a single, instantiable, class, which provides a PHP ORM like
API for plain and straightforward query generation.

Queries are not executed by the class or ORM, but rather return the SQL
statement that ought to be run against a database resource.

While it is modeled after the [ActiveRecord](http://en.wikipedia.org/wiki/Active_record_pattern)
pattern, it follows plain language that I felt would make code easier to read
and maintain.

### API Samples

    // select a username&#039;s details
    $query = (new Query());
    $query->select(&#039;*&#039;);
    $query->from(&#039;users&#039;);
    $query->where(&#039;username&#039;, &#039;onassar&#039;);
    $parsed = $query->parse();

    // update a user record
    $query = (new Query());
    $query->update(&#039;fname&#039;, &#039;Oliver&#039;);
    $query->table(&#039;users&#039;);
    $query->where(&#039;username&#039;, &#039;onassar&#039;);
    $parsed = $query->parse();

    // insert a user record
    $query = (new Query());
    $query->insert(array(
        &#039;fname&#039;, &#039;Oliver&#039;,
        &#039;lname&#039;, &#039;Nassar&#039;,
        &#039;username&#039;, &#039;onassar&#039;
    ));
    $query->into(&#039;users&#039;);
    $parsed = $query->parse();

### Performance
Worth mentioning is that this approach to database-access isn&#039;t ideal for
speed and memory. You&#039;re adding an extra, &quot;unrequired&quot;, step for
your application.

Any potential speed or memory hits that you may take, however, could (and
should) be circumvented by using a proper caching engine, class
(see [PHP-MemcachedCache](https://github.com/onassar/PHP-MemcachedCache) and/or
[PHP-APCCache](https://github.com/onassar/PHP-APCCache)) and flow.
