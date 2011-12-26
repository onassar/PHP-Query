<?php
/*
    // select
    $query->select();
    $query->select('username');
    $query->select('username', 'NOW()');
    $query->select(array('username', 'first_name'));

    // aggregates
    $query->count();
    $query->average('column');
    $query->sum('column');

    // select aliasing
    $query->select(
        array('u.username', 'u.first_name'),
        array('views' => 'us.views', 'us.logins')
    );
    $query->select(array('username', $subquery));
    $query->select(array('un' => 'username', 'now' => 'NOW()'));
    $query->select(array('un' => 'username', 'views' => $subquery));

    // updates
    $query->update('username', 'onassar');
    $query->update('views', 'views + 1', false);
    $query->update('timestamp_updated', 'NOW()');
    $query->update(
        array(
            'username' => 'onassar',
            'first_name' => 'Oliver'
        )
    );
    $query->update(array('username' => $subquery));
    $query->update(
        array('username', $subquery),
        array('last_name', 'Nassar'),
        array('timestamp_updated', 'NOW()')
    );
    $query->update(
        array('username' => 'onassar'),
        array('first_name' => 'Oliver'),
        array('last_name', 'first_name', false)
    );
    $query->update(
        array(
            'views' => array('views + 1', false),
            'first_name' => 'Oliver',
            'last_name' => array('Nassar')
        )
    );

    // insertion (<insert> method signature matches that of <update>)
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

    // order by department, sub-query, time_logged_in desecnding, school-field,
    // school-field descending, school-field, school-field descending
    $query->orderBy(array(
        'department',
        $subquery,
        'time_logged_in' => false,
        array('school', array('primary', 'secondary', 'post')),
        array('school', array('primary', 'secondary', 'post'), false),
        'school' => array(array('primary', 'secondary', 'post')),
        'school' => array(array('primary', 'secondary', 'post'), false)
    ));

    // limit row counts retrieved to 100; remove any limit
    $query->rows(100);
    $query->rows(0);
    $query->rows(false);

    // begin select from 5th record (based on ordering)
    $query->offset(5);
*/

    /**
     * Query
     * 
     * ActiveRecord inspired PHP ORM, which focuses on being decoupled from
     * other libraries.
     * 
     * @author Oliver Nassar <onassar@gmail.com>
     * @notes  if a raw query is executed (either via __construct($statement) or
     *         $this->run($statement), then properties like $this->type will
     *         have no value, and can be used to properly filter checks that are
     *         based on standard usage of the Query class, such as
     *         $this->from(), $this->where(), etc.)
     * @todo   either switch the apostrophes for splitting to `, or make sure
     *         apostrophes are escaped
     */
    class Query
    {
        /**
         * _type
         * 
         * Type of query that should be run (select, update, ...)
         * 
         * @var    string
         * @access protected
         */
        protected $_type;

        /**
         * _columns
         * 
         * Columns/fields that should be selected from the database
         * 
         * @var    array (default: array())
         * @access protected
         */
        protected $_columns = array();

        /**
         * _inputs
         * 
         * Input (columns:values) that should be used in CU operationsw (insert,
         * update)
         * 
         * @var    array (default: array())
         * @access protected
         */
        protected $_inputs = array();

        /**
         * _tables
         * 
         * List of tables, and optionally their aliases for the query, for usage
         * in the call
         * 
         * @var    array (default: array())
         * @access protected
         */
        protected $_tables = array();

        /**
         * _conditions
         * 
         * Conditions for a query to execute (select, update)
         * 
         * @var    array (default: array())
         * @access protected
         */
        protected $_conditions = array();

        /**
         * _groupings
         * 
         * Columns/fields whereby a result set should be grouped into/by
         * 
         * @var    array (default: array())
         * @access protected
         */
        protected $_groupings = array();

        /**
         * _filters
         * 
         * Filters that should be applied to a result set after it has been
         * returned by the database
         * 
         * @var    array (default: array())
         * @access protected
         */
        protected $_filters = array();

        /**
         * _orders
         * 
         * Columns and orderings for a result set to be returned/updated
         * (select, update)
         * 
         * @var    array (default: array())
         * @access protected
         */
        protected $_orders = array();

        /**
         * _rows
         * 
         * Number of rows that should be returned for a statement (select,
         * update)
         * 
         * @var    int (default: 10)
         * @access protected
         */
        protected $_rows = 10;

        /**
         * _offset
         * 
         * Where a select query should begin it's search
         * 
         * @var    int (default: 0)
         * @access protected
         */
        protected $_offset = 0;

        /**
         * __construct
         * 
         * @access public
         * @return void
         */
        public function __construct()
        {
        }

        /**
         * _conditions
         * 
         * Sets passed in conditions to a specific format, and returns
         * (recursively) the results. Used by having and where methods
         * 
         * @access protected
         * @return array conditions as formatted to the proper internal
         *         pattern, for assignment to either $this->where or
         *         $this->_filters properties
         */
        protected function _conditions()
        {
            $conditions = array();
            $args = func_get_args();
            foreach ($args as $key => $arg) {
                if (is_array($arg)) {
                    $numeric = true;
                    foreach ($arg as $sub => $value) {
                        if (!is_int($sub)) {
                            $numeric = false;
                            if (is_array($value)) {
                                $conditions = array_merge(
                                    $conditions,
                                    call_user_func_array(
                                        array($this, '_conditions'),
                                        array_merge(array($sub), $value)
                                    )
                                );
                            } else {
                                $conditions = array_merge(
                                    $conditions,
                                    call_user_func_array(
                                        array($this, '_conditions'),
                                        array($sub, $value)
                                    )
                                );
                            }
                        } elseif (is_array($value)) {
                            $numeric = false;
                            $conditions = array_merge(
                                $conditions,
                                call_user_func_array(
                                    array($this, '_conditions'),
                                    $value
                                )
                            );
                        }
                    }
                    if ($numeric === true) {
                        $conditions = array_merge(
                            $conditions,
                            call_user_func_array(
                                array($this, '_conditions'),
                                $arg
                            )
                        );
                    }
                } else {
                    if (count($args) === 2) {
                        $operand = '=';
                        if (is_array($args[1])) {
                            $operand = 'IN';
                        }
                        $conditions = array_merge(
                            $conditions,
                            call_user_func_array(
                                array($this, '_conditions'),
                                array($args[0], $operand, $args[1], true)
                            )
                        );
                    } elseif (count($args) === 3) {
                        $operand = '=';
                        $value = $args[1];
                        $auto = true;
                        if (in_array(true, $args, true) || in_array(false, $args, true)) {
                            $auto = end($args);
                        } else {
                            $operand = $args[1];
                            $value = $args[2];
                        }
                        if (is_array($value)) {
                            $operand = 'IN';
                        }
                        $conditions = array_merge(
                            $conditions,
                            call_user_func_array(
                                array($this, '_conditions'),
                                array($args[0], $operand, $value, $auto)
                            )
                        );
                    } elseif (count($args) === 4) {
                        $conditions[$args[0]] = array(
                            $args[1],
                            $args[2],
                            $args[3]
                        );
                    }
                    break;
                }
            }
            return $conditions;
        }

        /**
         * _inputs
         * 
         * Formats an input (update/insert) call to have it's data stored in a
         * consistent/organized way for parsing
         * 
         * @notes  third optional parameter in a call is whether or not to
         *         auto-add apostrophes
         * @access protected
         * @return void
         */
        protected function _inputs()
        {
            $args = func_get_args();
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    $keys = array_keys($arg);
                    if (is_string($keys[0])) {
                        foreach ($arg as $sub => $value) {
                            if (is_array($value)) {
                                $params = $value;
                                array_unshift($params, $sub);
                                call_user_func_array(array($this, '_inputs'), $params);
                            } else {
                                $this->_inputs($sub, $value);
                            }
                        }
                    } else {
                        call_user_func_array(array($this, '_inputs'), $arg);
                    }
                } elseif (is_string($arg)) {
                    if (count($args) === 2) {
                        $this->_inputs[$args[0]] = array($args[1], true);
                    } elseif (count($args) === 3) {
                        $this->_inputs[$args[0]] = array($args[1], $args[2]);
                    }
                    break;
                }
            }
        }

        /**
         * average
         * 
         * An alias of Query::select(array('average' => 'AVG(column)')). Sets
         * the average of a column for the query to be selected
         * 
         * @access public
         * @param  string $column column that should have it's average calculated
         * @param  string $name. (default: 'average') the name/alias/key for the
         *         average
         * @return void
         */
        public function average($column, $name = 'average')
        {
            $this->select(array($name => 'AVG(' . ($column) . ')'));
        }

        /**
         * count
         * 
         * An alias of Query::select(array('count' => 'COUNT(status)')). Sets
         * the number of columns for the query to be selected
         * 
         * @notes  $coloumn could be something like `DISTINCT user_id` for more
         *         accurate/flexible counting
         * @access public
         * @param  string $column. (default: '1') the column that should be used
         *         for counting
         * @param  string $name. (default: 'count') the name/alias/key for the
         *         count
         * @return void
         */
        public function count($column = '1', $name = 'count')
        {
            $this->select(array($name => 'COUNT(' . ($column) . ')'));
        }

        /**
         * sum
         * 
         * An alias of Query::select(array('sum' => 'SUM(column)')). Sets the
         * sum of a column for the query to be selected
         * 
         * @access public
         * @param  string $column column that should have it's sum calculated
         * @param  string $name. (default: 'sum') the name/alias/key for the sum
         * @return void
         */
        public function sum($column, $name = 'sum')
        {
            $this->select(array($name => 'SUM(' . ($column) . ')'));
        }

        /**
         * select
         * 
         * Sets the columns to return in a select statement
         * 
         * @access public
         * @return void
         */
        public function select()
        {
            $this->_type = 'select';
            $args = func_get_args();
            if (empty($args)) {
                $this->select('*');
            } else {

                // loop through arguments, formatting the primary key selection or adding the columns
                foreach ($args as $arg) {

                    // can't be if/elseif, need's to be consecutive
                    if (!is_array($arg)) {
                        $this->_columns[] = $arg;
                    }
                    if (is_array($arg)) {
                        foreach ($arg as $key => $value) {
                            if (is_int($key)) {
                                $this->select($value);
                            } else {
                                $this->_columns[$key] = $value;
                            }
                        }
                    }
                }
            }
        }

        /**
         * update
         * 
         * Stores a column/value to be updated, by calling _inputs interally. If
         * no arguments passed, calls itself with default columns/values
         * 
         * @access public
         * @return void
         */
        public function update()
        {
            $this->_type = 'update';
            $args = func_get_args();
            if (empty($args)) {
                throw new Exception(
                    'Column must be specified for <update> method.'
                );
            }

            // internal input routing
            $args = func_get_args();
            call_user_func_array(array($this, '_inputs'), $args);
        }

        /**
         * insert
         * 
         * Stores a column/value to be inserted, by calling _inputs internally.
         * If no arguments passed, calls itself with default columns/values
         * 
         * @access public
         * @return void
         */
        public function insert()
        {
            $this->_type = 'insert';
            $args = func_get_args();
            call_user_func_array(array($this, '_inputs'), $args);
        }

        /**
         * table
         * 
         * Records the tables that should be used for the statement, with their
         * alias/key specified
         * 
         * @access public
         * @return void
         */
        public function table()
        {
            $args = func_get_args();
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    foreach ($arg as $key => $value) {
                        if (is_int($key)) {
                            $this->table($value);
                        } else {
                            $this->_tables[$key] = $value;
                        }
                    }
                } else {
                    $this->_tables[] = $arg;
                }
            }
        }

        /**
         * from
         * 
         * An alias of Query::table
         * 
         * @access public
         * @return void
         */
        public function from()
        {
            $args = func_get_args();
            call_user_func_array(array($this, 'table'), $args);
        }

        /**
         * into
         * 
         * An alias of Query::table
         * 
         * @access public
         * @return void
         */
        public function into()
        {
            $args = func_get_args();
            call_user_func_array(array($this, 'table'), $args);
        }

        /**
         * where
         * 
         * Sets the conditional's for a select or update statement
         * 
         * @access public
         * @return void
         */
        public function where()
        {
            $args = func_get_args();
            $this->_conditions[][] = call_user_func_array(
                array($this, '_conditions'),
                $args
            );
        }

        /**
         * andWhere
         * 
         * An alias of Query::where
         * 
         * @access public
         * @return void
         */
        public function andWhere()
        {
            $args = func_get_args();
            call_user_func_array(array($this, 'where'), $args);
        }

        /**
         * orWhere
         * 
         * Makes the previous where call/conditions non-binding with a logical
         * OR/or/|| condition
         * 
         * @access public
         * @return void
         */
        public function orWhere()
        {
            if (empty($this->_conditions)) {
                throw new Exception(
                    'Query::orWhere call requires Query::where call first.'
                );
            }
            $args = func_get_args();
            call_user_func_array(array($this, 'where'), $args);
            $condition = array_pop($this->_conditions);
            $condition = $condition[0];
            $last = &$this->_conditions[count($this->_conditions)-1];
            array_push($last, $condition);
        }

        /**
         * groupBy
         * 
         * Specifies what columns/fields an SQL result set should be grouped
         * into
         * 
         * @access public
         * @return void
         */
        public function groupBy()
        {
            $args = func_get_args();
            $this->_groupings = array_merge((array) $this->_groupings, $args);
        }

        /**
         * having
         * 
         * Sets the filters/having conditions for a select statement
         * 
         * @access public
         * @return void
         */
        public function having()
        {
            $args = func_get_args();
            $this->_filters[][] = call_user_func_array(array($this, '_conditions'), $args);
        }

        /**
         * orHaving
         * 
         * Makes the previous having condition non-binding with a logical
         * OR/or/|| condition
         * 
         * @access public
         * @return void
         */
        public function orHaving()
        {
            if (empty($this->_conditions)) {
                throw new Exception('Query::orHaving call requires Query::having call first.');
            }
            $args = func_get_args();
            call_user_func_array(array($this, 'having'), $args);
            $condition = array_pop($this->_conditions);
            $condition = $condition[0];
            $last = &$this->_conditions[count($this->_conditions)-1];
            array_push($last, $condition);
        }

        /**
         * filter
         * 
         * An alias of Query::having
         * 
         * @access public
         * @return void
         */
        public function filter()
        {
            $args = func_get_args();
            call_user_func_array(array($this, 'having'), $args);
        }

        /**
         * orFilter
         * 
         * An alias of Query::orHaving
         * 
         * @access public
         * @return void
         */
        public function orFilter()
        {
            $args = func_get_args();
            call_user_func_array(array($this, 'orHaving'), $args);
        }

        /**
         * orderBy
         * 
         * Sets the order expressions for an SQL select or update statement
         * 
         * @access public
         * @return void
         */
        public function orderBy()
        {
            $args = func_get_args();
            if (count($args) === 1) {
                if (is_array($args[0])) {
                    foreach ($args[0] as $key => $arg) {
                        if (is_string($key)) {
                            if (is_bool($arg)) {
                                $arg = array($key, $arg);
                            } elseif (is_array($arg)) {
                                $arg = array_values($arg);
                                array_unshift($arg, $key);
                            }
                        }
                        if (is_object($arg)) {
                            $arg = array($arg);
                        }
                        call_user_func_array(array($this, 'orderBy'), $arg);
                    }
                } else {
                    $this->_orders[] = array($args[0], array(), true);
                }
            } elseif (count($args) === 2) {
                if (is_bool($args[1])) {
                    $this->_orders[] = array($args[0], array(), $args[1]);
                } elseif (is_array($args[1])) {
                    $this->_orders[] = array($args[0], $args[1], true);
                } else {
                    throw new Exception(
                        'Unexpected second parameter type; should be ' .
                        '*boolean* or *array* (or not passed).'
                    );
                }
            } elseif (count($args) === 3) {
                $this->_orders[] = $args;
            }
        }

        /**
         * rows
         * 
         * Sets the maximum number of rows to retrieve/update (select, update).
         * Makes a sub-call to remove any offset directives from the query if
         * there is no limit on the number of rows to be returned. SQL will
         * throw an error if a query is evaluated that has an offset, but no
         * limit.
         * 
         * @access public
         * @param  int $rows number of rows to limit the result set by
         * @return void
         */
        public function rows($rows = 10)
        {
            if ($rows === false || $rows === 0) {
                $this->offset(false);
            }
            $this->_rows = $rows;
        }

        /**
         * limit
         * 
         * An alias of Query::rows
         * 
         * @access public
         * @return void
         */
        public function limit()
        {
            $args = func_get_args();
            call_user_func_array(array($this, 'rows'), $args);
        }

        /**
         * offset
         * 
         * What row to begin the retrieval from
         * 
         * @access public
         * @param  int $offset. (default: 0) what row to begin retrieval from
         *         (aka the result-set's offset)
         * @return void
         */
        public function offset($offset = 0)
        {
            $this->_offset = $offset;
        }

        /**
         * parse
         * 
         * Creates a valid SQL statement based on the properties set by this
         * instance of the Query class
         * 
         * @access public
         * @return string valid, minified, SQL statement ready to be
         *         executed/run
         */
        public function parse()
        {
            // command
            if (is_null($this->_type)) {
                throw new Exception(
                    'Query::$type must be specified by calling Query::select,' .
                    'Query::update, or Query::insert.'
                );
            }
            $command = strtoupper($this->_type);
            if ($this->_type === 'insert') {
                $command .= ' INTO';
            }

            // columns
            if ($this->_type === 'select') {
                $columns = array();
                foreach ($this->_columns as $key => $column) {
                    $exp = $column;
                    if (is_object($column)) {
                        $exp = '(' . ($column->parse()) . ')';
                    }
                    if (is_string($key)) {
                        $exp .= ' AS ' . ($key);
                    }
                    $columns[] = $exp;
                }
                $columns = implode(', ', $columns);
            }

            // inputs
            if ($this->_type === 'update') {
                $inputs = array();
                foreach ($this->_inputs as $column => $details) {
                    $exp = ($column) . ' = ';
                    if (is_object($details[0])) {
                        $exp .= '(' . ($details[0]->parse()) . ')';
                    } elseif ($details[1] === true) {
                        if (
                            is_int($details[0])
                            || in_array($details[0], array('NOW()'))
                        ) {
                            $exp .= $details[0];
                        } else {
                            $exp .= '\'' . ($details[0]) . '\'';
                        }
                    } else {
                        $exp .= $details[0];
                    }
                    $inputs[] = $exp;
                }
                $inputs = implode(', ', $inputs);
            } elseif ($this->_type === 'insert') {
                $columns = array();
                $values = array();
                foreach ($this->_inputs as $column => $details) {
                    $columns[] = $column;
                    if (is_object($details[0])) {
                        $value = '(' . ($details[0]->parse()) . ')';
                    } elseif ($details[1] === true) {
                        if (
                            is_int($details[0])
                            || in_array($details[0], array('NOW()'))
                        ) {
                            $value = $details[0];
                        } else {
                            $value = '\'' . ($details[0]) . '\'';
                        }
                    } else {
                        $value = $details[0];
                    }
                    $values[] = $value;
                }
            }

            // tables
            foreach ($this->_tables as $alias => $table) {
                if (is_int($alias)) {
                    $tables[] = $table;
                } else {
                    $tables[] = ($table) . ' AS ' . ($alias);
                }
            }
            $tables = implode(', ', $tables);

            // conditions
            if (in_array($this->_type, array('select', 'update'))) {
                $conditions = array();
                if (!empty($this->_conditions)) {
                    foreach ($this->_conditions as $clause) {
                        $or = array();
                        foreach ($clause as $inclusionary) {
                            $and = array();
                            foreach ($inclusionary as $column => $details) {
                                $value = $details[1];
                                if (is_object($value)) {
                                    $value = '(' . ($value->parse()) . ')';
                                } elseif (is_array($value)) {
                                    if ($details[2] === false) {
                                        $value = '(' . implode(', ', $value) . ')';
                                    } else {
                                        $values = array();
                                        foreach ($details[1] as $column) {
                                            if (!is_int($column)) {
                                                $values[] = '\'' . ($column) . '\'';
                                            } else {
                                                $values[] = $column;
                                            }
                                        }
                                        $value = '(' . implode(', ', $values) . ')';
                                    }
                                } elseif ($details[2] === true) {
                                    if (!is_int($value)) {
                                        $value = '\'' . ($value) . '\'';
                                    }
                                }
                                $and[] = ($column) . ' ' . ($details[0]) . ' ' . ($value);
                            }
                            if (count($and) === 1) {
                                $or[] = implode(' AND ', $and);
                            } else {
                                $or[] = '(' . implode(' AND ', $and) . ')';
                            }
                        }
                        if (count($or) === 1) {
                            $conditions[] = implode(' OR ', $or);
                        } else {
                            $conditions[] = '(' . implode(' OR ', $or) . ')';
                        }
                    }
                    $conditions = implode(' AND ', $conditions);
                }
            }

            // groupings
            if (in_array($this->_type, array('select'))) {
                $groupings = array();
                if (!empty($this->_groupings)) {
                    $groupings = implode(', ', $this->_groupings);
                }
            }

            // filters
            if (in_array($this->_type, array('select', 'update'))) {
                $filters = array();
                if (!empty($this->_filters)) {
                    foreach ($this->_filters as $clause) {
                        $or = array();
                        foreach ($clause as $inclusionary) {
                            $and = array();
                            foreach ($inclusionary as $column => $details) {
                                $value = $details[1];
                                if (is_object($value)) {
                                    $value = '(' . ($value->parse()) . ')';
                                } elseif (is_array($value)) {
                                    if ($details[2] === false) {
                                        $value = '(' . implode(', ', $value) . ')';
                                    } else {
                                        $values = array();
                                        foreach ($details[1] as $column) {
                                            if (!is_int($column)) {
                                                $values[] = '\'' . ($column) . '\'';
                                            } else {
                                                $values[] = $column;
                                            }
                                        }
                                        $value = '(' . implode(', ', $values) . ')';
                                    }
                                } elseif ($details[2] === true) {
                                    if (!is_int($value)) {
                                        $value = '\'' . ($value) . '\'';
                                    }
                                }
                                $and[] = ($column) . ' ' . ($details[0]) . ' ' . ($value);
                            }
                            if (count($and) === 1) {
                                $or[] = implode(' AND ', $and);
                            } else {
                                $or[] = '(' . implode(' AND ', $and) . ')';
                            }
                        }
                        if (count($or) === 1) {
                            $filters[] = implode(' OR ', $or);
                        } else {
                            $filters[] = '(' . implode(' OR ', $or) . ')';
                        }
                    }
                    $filters = implode(' AND ', $filters);
                }
            }

            // orders
            if (in_array($this->_type, array('select', 'update'))) {
                $orders = array();
                if (!empty($this->_orders)) {
                    foreach ($this->_orders as $rule) {
                        $column = $rule[0];
                        if (is_object($column)) {
                            $column = '(' . ($column->parse()) . ')';
                        }
                        $exp = $column;
                        if (!empty($rule[1])) {
                            $exp = 'field(' . ($exp) . ', \'' . implode('\', \'', $rule[1]) . '\')';
                        }
                        if ($rule[2] === false) {
                            $exp .= ' DESC';
                        }
                        $orders[] = $exp;
                    }
                    $orders = implode(', ', $orders);
                }
            }

            // limits/rows
            if (in_array($this->_type, array('select', 'update'))) {
                $rows = $this->_rows;
            }

            // offset
            if (in_array($this->_type, array('select'))) {
                $offset = $this->_offset;
            }

            // conclusory parsing
            $statement = $command;
            if ($this->_type === 'select') {
                $statement .= ' ' . ($columns) . ' FROM ' . ($tables);
                if (empty($conditions) === false) {
                    $statement .= ' WHERE ' . ($conditions);
                }
                if (empty($groupings) === false) {
                    $statement .= ' GROUP BY ' . ($groupings);
                }
                if (empty($filters) === false) {
                    $statement .= ' HAVING ' . ($filters);
                }
                if (empty($orders) === false) {
                    $statement .= ' ORDER BY ' . ($orders);
                }
                if (empty($rows) === false) {
                    $statement .= ' LIMIT ' . ($rows);
                }
                if (empty($offset) === false || $offset === 0) {
                    $statement .= ' OFFSET ' . ($offset);
                }
            } elseif ($this->_type === 'insert') {
                $statement .= ' ' . ($tables);
                $statement .= ' (' . implode(', ', $columns) . ')';
                $statement .= ' VALUES (' . implode(', ', $values) . ')';
            } elseif ($this->_type === 'update') {
                $statement .= ' ' . ($tables) . ' SET ' . ($inputs);
                if (empty($conditions) === false) {
                    $statement .= ' WHERE ' . ($conditions);
                }
                if (empty($orders) === false) {
                    $statement .= ' ORDER BY ' . ($orders);
                }
                if (empty($rows) === false) {
                    $statement .= ' LIMIT ' . ($rows);
                }
            }
            return $statement;
        }
    }
