<?php

    /**
     * Query examples
     *
     * This file contains numerous examples that showcase the various ways a
     * query can be generated dynamically. These include all the API methods, as
     * well as all the ways parameters can be passed to these methods to
     * showcase the API's flexibility.
     *
     * @author Oliver Nassar <onassar@gmail.com>
     */

    /**
     * Select statements
     */
    $query = (new Query());
    $query->select();
    $query->from('users');
    echo $query->parse();

    $query = (new Query());
    $query->select('username');
    $query->from('users');
    echo $query->parse();

    $query = (new Query());
    $query->select('username', 'NOW()');
    $query->from('users');
    echo $query->parse();

    $query = (new Query());
    $query->select(array('username', 'first_name'));
    $query->from('users');
    echo $query->parse();

    /**
     * Aggregate select statements
     */
    $query = (new Query());
    $query->count();
    $query->average('column');
    $query->sum('column');
    $query->from('users');
    echo $query->parse();

    /**
     * Alias select statements
     */
    $query = (new Query());
    $query->select(
        array('u.username', 'u.first_name'),
        array('views' => 'us.views', 'us.logins')
    );
    $query->from('users');
    echo $query->parse();

    $query = (new Query());
    $query->select(array('username', $subquery));
    $query->from('users');
    echo $query->parse();

    $query = (new Query());
    $query->select(array('un' => 'username', 'now' => 'NOW()'));
    $query->from('users');
    echo $query->parse();

    $query = (new Query());
    $query->select(array('un' => 'username', 'views' => $subquery));
    $query->from('users');
    echo $query->parse();

    /**
     * Update statements
     */
    $query = (new Query());
    $query->update('username', 'onassar');
    $query->table('users');
    echo $query->parse();

    $query = (new Query());
    $query->update('views', 'views + 1', false);
    $query->table('users');
    echo $query->parse();

    $query = (new Query());
    $query->update('timestamp_updated', 'NOW()');
    $query->table('users');
    echo $query->parse();

    $query = (new Query());
    $query->update(
        array(
            'username' => 'onassar',
            'first_name' => 'Oliver'
        )
    );
    $query->table('users');
    echo $query->parse();

    $query = (new Query());
    $query->update(array('username' => $subquery));
    $query->table('users');
    echo $query->parse();

    $query = (new Query());
    $query->update(
        array('username', $subquery),
        array('last_name', 'Nassar'),
        array('timestamp_updated', 'NOW()')
    );
    $query->table('users');
    echo $query->parse();

    $query = (new Query());
    $query->update(
        array('username' => 'onassar'),
        array('first_name' => 'Oliver'),
        array('last_name', 'first_name', false)
    );
    $query->table('users');
    echo $query->parse();

    $query = (new Query());
    $query->update(
        array(
            'views' => array('views + 1', false),
            'first_name' => 'Oliver',
            'last_name' => array('Nassar')
        )
    );
    $query->table('users');
    echo $query->parse();

    // insertion (<insert> method signature matches that of <update>)
    $query = (new Query());
    $query->insert();

    // table selection
    $query->table('users');
    $query->table('users', 'user_settings');
    $query->table(array('users', 'user_settings'));
    $query->table(
        array(
            'u' => 'users',
            'us' => 'user_settings'
        )
    );

    // <table> aliases
    $query->from('users');
    $query->into('users');

    // conditionals
    $query->where('department', 'engineering');
    $query->where('department', 'position', false);
    $query->where('age', '>', 10);
    $query->where('age', '!=', 'oliver');
    $query->where('age', '!=', 'first_name', false);
    $query->where('department', array('engineering', 'administration'));
    $query->where('department', 'IN', array('engineering', 'administration'));
    $query->where('username', 'LIKE', 'live');
    $query->where(
        array('username', 'LIKE', 'live'),
        array('department', 'engineering'),
        array('school', 'IN', $subquery),
        array('language' => 'english'),
        array('sport' => array('=', 'basketball'))
    );
    $query->where(array(
        'username' => 'onassar',
        'first_name' => array('LIKE', 'live'),
        array('school', 'IN', $subquery),
        array('sport', 'basetkball')
    ));

    // or-conditions (determined by previous <where> method call)
    $query->orWhere('department', 'administration');

    // grouping
    $query->groupBy('sport');
    $query->groupBy('department', 'school');

    // filtering (matches <where> method signature)
    $query->filter('department', 'engineering');

    // <filter> alias
    $query->having('department', 'engineering');

    // general ordering
    $query->orderBy('department');

    // descending ordering
    $query->orderBy('time_logged_in', false);

    // field ordering
    $query->orderBy('department', array('admin', 'science', 'english'));

    // field ordering, following by descending sub-orderning
    $query->orderBy('department', array('admin', 'science', 'english'), false);

    // order by department, sub-query, time_logged_in desecnding, type,
    // gender descending, school, parents-field descending

$subquery = (new Query());
$subquery->select('id');
$subquery->from('users');
$subquery->limit(1);

    $query->orderBy(array(
        'department',
        $subquery,
        'time_logged_in' => false,
        array('type', array('regular', 'admin')),
        array('gender', array('male', 'female'), false),
        'school' => array(array('primary', 'middle', 'seconday')),
        'parents' => array(array('one', 'two'), false)
    ));

    // limit row counts retrieved to 100; remove any limit
    $query->rows(100);
    $query->rows(0);
    $query->rows(false);

    // begin select from 5th record (based on ordering)
    $query->offset(5);