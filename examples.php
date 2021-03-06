<?php

    /**
     * Query examples
     * 
     * This file contains dozens of examples that showcase the various ways a
     * query can be generated dynamically. These include all the API methods, as
     * well as all the ways parameters can be passed to these methods to
     * showcase the API's flexibility.
     * 
     * @author  Oliver Nassar <onassar@gmail.com>
     */

    /**
     * Select statements
     * 
     */

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select('username');
    $query->from('users');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select('username', 'NOW()');
    $query->from('users');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select(array('username', 'first_name'));
    $query->from('users');
    echo $query->parse();


    /**
     * Aggregate select statements
     * 
     */

    // 
    $query = new Query();
    $query->count();
    $query->average('column');
    $query->sum('column');
    $query->from('users');
    echo $query->parse();


    /**
     * Alias select statements
     * 
     */

    // 
    $query = new Query();
    $query->select(
        array('u.username', 'u.first_name'),
        array('views' => 'us.views', 'us.logins')
    );
    $query->from(array(
        'u' => 'users',
        'us' => 'user_settings'
    ));
    echo $query->parse();

    // 
    $subquery = new Query();
    $subquery->select('colour');
    $subquery->from('favourites');
    $subquery->where('username', 'users.username');
    $query = new Query();
    $query->select(array('username', $subquery));
    $query->from('users');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select(array('un' => 'username', 'now' => 'NOW()'));
    $query->from('users');
    echo $query->parse();

    // 
    $subquery = new Query();
    $subquery->select('views');
    $subquery->from('analytics');
    $subquery->where('uid', 'users.uid', false);
    $query = new Query();
    $query->select(array('un' => 'username', 'views' => $subquery));
    $query->from('users');
    echo $query->parse();


    /**
     * Update statements
     * 
     */

    // 
    $query = new Query();
    $query->update('users');
    $query->set('username', 'onassar');
    echo $query->parse();

    // 
    $query = new Query();
    $query->update('users');
    $query->set('views', 'views + 1', false);
    $query->limit(10);
    echo $query->parse();

    // 
    $query = new Query();
    $query->update('users');
    $query->set('timestamp_updated', 'NOW()');
    echo $query->parse();

    // 
    $query = new Query();
    $query->update('users');
    $query->set(
        array(
            'username' => 'onassar',
            'first_name' => 'Oliver'
        )
    );
    echo $query->parse();

    // 
    $subquery = new Query();
    $subquery->select('name');
    $subquery->from('animals');
    $subquery->limit(1);
    $query = new Query();
    $query->update('users');
    $query->set(array('username' => $subquery));
    $query->where('uid', 1);
    echo $query->parse();

    // 
    $subquery = new Query();
    $subquery->select('name');
    $subquery->from('animals');
    $subquery->limit(1);
    $query = new Query();
    $query->update('users');
    $query->set(
        array('username', $subquery),
        array('last_name', 'Nassar'),
        array('timestamp_updated', 'NOW()')
    );
    $query->where('uid', 1);
    echo $query->parse();

    // 
    $query = new Query();
    $query->update('users');
    $query->set(
        array('username' => 'onassar'),
        array('first_name' => 'Oliver'),
        array('last_name', 'first_name', false)
    );
    echo $query->parse();

    // 
    $query = new Query();
    $query->update('users');
    $query->set(
        array(
            'views' => array('views + 1', false),
            'first_name' => 'Oliver',
            'last_name' => array('Nassar')
        )
    );
    echo $query->parse();


    /**
     * Insertions (<insert> method signature matches that of <update>)
     * 
     */

    // single record
    $query = new Query();
    $query->insert(array(
        'created' => 'NOW()',
        'name' => 'Oliver Nassar',
        'email' => 'onassar@gmail.com'
    ));
    $query->into('users');
    echo $query->parse();

    // single record with apostrophe option
    $query = new Query();
    $query->insert(array(
        'created' => 'NOW()',
        'name' => 'Oliver Nassar',
        'email' => 'onassar@gmail.com',
        'age' => array(33, false)
    ));
    $query->into('users');
    echo $query->parse();

    // [DEPRECATED] single record with sub arrays for apostrophe options
    $query = new Query();
    $query->insert(array(
        array('created', 'NOW()', true),
        array('name', 'Sherif Nassar', true),
        array('email', 'snassar@gmail.com', true),
        array('age', 72, false)
    ));
    $query->into('users');
    echo $query->parse();

    // [DEPRECATED] multiple records within one param (including multiple apostrphe types)
    $query = new Query();
    $query->insert(array(
        array(
            'created' => 'NOW()',
            'name' => 'Oliver Nassar',
            'email' => 'onassar@gmail.com',
            'age' => array(33, false)
        ),
        array(
            'created' => 'NOW()',
            'name' => 'Sherif Nassar',
            'email' => 'snassar@gmail.com',
            'age' => array(72, false)
        ),
        array(
            array('created', 'NOW()', true),
            array('name', 'Sherif Nassar', true),
            array('email', 'snassar@gmail.com', true),
            array('age', 72, false)
        )
    ));
    $query->into('users');
    echo $query->parse();

    // multiple records within multiple params
    $query = new Query();
    $query->insert(
        array(
            'created' => 'NOW()',
            'name' => 'Oliver Nassar',
            'email' => 'onassar@gmail.com',
            'age' => 33
        ),
        array(
            'created' => 'NOW()',
            'name' => 'Sherif Nassar',
            'email' => 'snassar@gmail.com',
            'age' => array(72, false)
        )
    );
    $query->into('users');
    echo $query->parse();

    // [DEPRECATED] desired format
    $query = new Query();
    $query->insert(
        array(
            array('created', 'NOW()', true),
            array('name', 'Oliver Nassar', true),
            array('email', 'onassar@gmail.com', true),
            array('age', 33, false)
        ),
        array(
            array('created', 'NOW()', true),
            array('name', 'Sherif Nassar', true),
            array('email', 'snassar@gmail.com', true),
            array('age', 72, false)
        )
    );
    $query->into('users');
    echo $query->parse();


    /**
     * Table specification
     * 
     */

    // 
    $query = new Query();
    $query->select('uid');
    $query->table('users');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select('users.uid', 'user_settings.views');
    $query->table('users', 'user_settings');
    $query->where('user_settings.uid', 'users.uid', false);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select('users.uid');
    $query->table(array('users', 'user_settings'));
    $query->where('user_settings.uid', 'users.uid', false);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select('u.uid');
    $query->table(
        array(
            'u' => 'users',
            'us' => 'user_settings'
        )
    );
    $query->where('us.uid', 'u.uid', false);
    echo $query->parse();


    /**
     * <table> method aliases
     * 
     */

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    echo $query->parse();

    // [DEPRECATED]
    $query = new Query();
    $query->insert('first_name', 'Oliver');
    $query->into('users');
    echo $query->parse();


    /**
     * Where conditionals
     * 
     */

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('department', 'engineering');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('department', 'position', false);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('age', '>', 10);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('first_name', '!=', 'Oliver');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('first_name', '!=', 'last_name', false);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('department', array('engineering', 'administration'));
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('department', 'IN', array('engineering', 'administration'));
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('username', 'LIKE', 'live');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where(
        array('username', 'LIKE', 'live'),
        array('department', 'engineering'),
        array('language' => 'english'),
        array('sport' => array('=', 'basketball')),
        array('hobby' => array('!=', 'team', false))
    );
    echo $query->parse();

    // 
    $subquery = new Query();
    $subquery->select('name');
    $subquery->from('schools');
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where(array(
        'username' => 'onassar',
        'first_name' => array('LIKE', 'live'),
        array('school', 'IN', $subquery),
        array('sport', 'basetkball')
    ));
    echo $query->parse();


    /**
     * OR-Conditionals (determined by previous <where> method call)
     * 
     */

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('department', 'athletics');
    $query->orWhere('department', 'administration');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->where('name', 'Oliver Nassar');
    $query->andWhere(array(
        'department' => 'athletics',
        'role' => 'teacher'
    ));
    $query->orWhere('department', 'administration');
    echo $query->parse();


    /**
     * Grouping
     * 
     */

    $query = new Query();
    $query->select();
    $query->from('users');
    $query->groupBy('sport');
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->groupBy('department', 'school');
    echo $query->parse();


    /**
     * Filtering (matches <where> method signature)
     * 
     */

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->filter('department', 'engineering');
    echo $query->parse();

    // <filter> alias
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->having('department', 'engineering');
    echo $query->parse();


    /**
     * Ordering
     * 
     */

    $query = new Query();
    $query->select();
    $query->from('users');
    $query->orderBy('department');
    echo $query->parse();

    // clear all previous order by calls
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->orderBy('department');
    $query->orderBy(false);
    echo $query->parse();

    // descending
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->orderBy('time_logged_in', false);
    echo $query->parse();

    // field ordering
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->orderBy('department', array('admin', 'science', 'english'));
    echo $query->parse();

    // field ordering (descending)
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->orderBy(
        'department',
        array('admin', 'science', 'english'),
        false
    );
    echo $query->parse();

    // multiple ordering
    $subquery = new Query();
    $subquery->select('id');
    $subquery->from('user_settings');
    $subquery->where('id', 'users.id');
    $subquery->limit(1);
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->orderBy(array(
        'department',
        $subquery,
        'time_logged_in' => false,
        array('type', array('regular', 'admin')),
        array('gender', array('male', 'female'), false),
        'school' => array(array('primary', 'middle', 'seconday')),
        'parents' => array(array('one', 'two'), false)
    ));
    echo $query->parse();


    /**
     * Limiting
     * 
     */

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->rows(100);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->rows(0);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->rows(false);
    echo $query->parse();


    /**
     * Offsets (record-retrieval start point)
     * 
     */

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->offset(5);
    echo $query->parse();

    // 
    $query = new Query();
    $query->select();
    $query->from('users');
    $query->offset(false);
    echo $query->parse();


    /**
     * Locking/Unlocking
     * 
     */

    // 
    $query = new Query();
    $query->lock('table', 'read');
    $query->lock('table', 'write');
    $query->unlock();
