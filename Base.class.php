<?php

    // Namespace overhead
    namespace onassar\Query;

    /**
     * Base
     * 
     * ActiveRecord inspired PHP ORM, which focuses on being decoupled from
     * other libraries. Queries are *not* processed by this ORM, but rather
     * parsed and made available through the <parse> method.
     * 
     * @todo    either switch the apostrophes for splitting to `, or make sure
     *          apostrophes are escaped; the result of *not* doing this is
     *          potential sql-injections or failing-queries (the former being
     *          more serious)
     * @link    https://github.com/onassar/PHP-Query
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    class Base
    {
        /**
         * _columns
         * 
         * Columns/fields that should be selected from the database.
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_columns = array();

        /**
         * _conditions
         * 
         * Conditions for a query to execute (select, update).
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_conditions = array();

        /**
         * _filters
         * 
         * Filters that should be applied to a result set after it has been
         * returned by the database.
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_filters = array();

        /**
         * _groupings
         * 
         * Columns/fields whereby a result set should be grouped into/by.
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_groupings = array();

        /**
         * _insertRecords
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_insertRecords = array();

        /**
         * _lockType
         * 
         * Whether the locking call should prevent reading or writing.
         * 
         * @access  protected
         * @var     array (default: null)
         */
        protected $_lockType = null;

        /**
         * _offset
         * 
         * Where a select query should begin it's search.
         * 
         * @access  protected
         * @var     false|int (default: 0)
         */
        protected $_offset = 0;

        /**
         * _orders
         * 
         * Columns and orderings for a result set to be returned/updated
         * (select, update).
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_orders = array();

        /**
         * _replaceRecords
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_replaceRecords = array();

        /**
         * _rows
         * 
         * Number of rows that should be returned for a statement (select,
         * update).
         * 
         * @access  protected
         * @var     false|int (default: 10)
         */
        protected $_rows = 10;

        /**
         * _tables
         * 
         * List of tables, and optionally their aliases for the query, for usage
         * in the call.
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_tables = array();

        /**
         * _type
         * 
         * Type of query that should be run (select, update, ...).
         * 
         * @access  protected
         * @var     string
         */
        protected $_type;

        /**
         * _updateValues
         * 
         * Array of column:value pairs that should be used in update operations.
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_updateValues = array();

        /**
         * __construct
         * 
         * @access  public
         * @return  void
         */
        public function __construct()
        {
        }

        /**
         * __toString
         * 
         * An alias of <parse>.
         * 
         * @access  public
         * @return  void
         */
        public function __toString()
        {
            return $this->parse();
        }

        /**
         * _conditions
         * 
         * Sets passed in conditions to a specific format, and returns
         * (recursively) the results. Used by having and where methods.
         * 
         * @access  protected
         * @return  array conditions as formatted to the proper internal
         *          pattern, for assignment to either $this->where or
         *          $this->_filters properties
         */
        protected function _conditions()
        {
            $conditions = array();
            $args = func_get_args();
            foreach ($args as $key => $arg) {
                if (is_array($arg) === true) {
                    $numeric = true;
                    foreach ($arg as $sub => $value) {
                        if (is_int($sub) === false) {
                            $numeric = false;
                            if (is_array($value) === true) {
                                $callback = array($this, '_conditions');
                                $conditions = array_merge(
                                    $conditions,
                                    call_user_func_array(
                                        $callback,
                                        array_merge(array($sub), $value)
                                    )
                                );
                            } else {
                                $callback = array($this, '_conditions');
                                $conditions = array_merge(
                                    $conditions,
                                    call_user_func_array(
                                        $callback,
                                        array($sub, $value)
                                    )
                                );
                            }
                        } elseif (is_array($value) === true) {
                            $numeric = false;
                            $callback = array($this, '_conditions');
                            $conditions = array_merge(
                                $conditions,
                                call_user_func_array($callback, $value)
                            );
                        }
                    }
                    if ($numeric === true) {
                        $callback = array($this, '_conditions');
                        $conditions = array_merge(
                            $conditions,
                            call_user_func_array($callback, $arg)
                        );
                    }
                } else {
                    if (count($args) === 2) {
                        $operand = '=';
                        if (is_array($args[1]) === true) {
                            $operand = 'IN';
                        }
                        $callback = array($this, '_conditions');
                        $conditions = array_merge(
                            $conditions,
                            call_user_func_array(
                                $callback,
                                array($args[0], $operand, $args[1], true)
                            )
                        );
                    } elseif (count($args) === 3) {
                        $operand = '=';
                        $value = $args[1];
                        $auto = true;

                        /**
                         * Conditions include specification (true or false,
                         * doesn't matter which); automatically set the operand
                         * to *IN* since there were only 3-parameters set;
                         * otherwise since 3-parameters, assume operand properly
                         * set in 2nd position.
                         */
                        if (
                            in_array(true, $args, true) === true
                            || in_array(false, $args, true) === true
                        ) {
                            $auto = end($args);
                            if (is_array($value) === true) {
                                $operand = 'IN';
                            }
                        } else {
                            $operand = $args[1];
                            $value = $args[2];
                        }
                        $callback = array($this, '_conditions');
                        $conditions = array_merge(
                            $conditions,
                            call_user_func_array(
                                $callback,
                                array($args[0], $operand, $value, $auto)
                            )
                        );
                    } elseif (count($args) === 4) {

                        /**
                         * Check here is done to allow a subquery (of Query
                         * type) to be specified as a column name to be matched
                         * against a value.
                         */
                        $column = $args[0];
                        if (
                            is_object($args[0]) === true
                            && gettype($args[0]) === gettype($this)
                        ) {
                            $column = '(' . ($args[0]) . ')';
                        }
                        $conditions[$column] = array(
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
         * _insertRecords
         * 
         * @access  protected
         * @return  void
         */
        protected function _insertRecords()
        {
            $args = func_get_args();
            foreach ($args as $record) {
                foreach ($record as $column => $value) {
                    if (is_array($value) === false) {
                        $record[$column] = array($value, true);
                    }
                }
                array_push($this->_insertRecords, $record);
            }
        }

        /**
         * _referencesAlias
         * 
         * Returns whether or not the passed in string is likely to be
         * referencing a table alias. The first check returns false if the
         * string passed in is surrounded by single quotes. After that, the
         * string is exploded by the period symbol (which is used to denote
         * table aliases), and then checked whether a table alias is present.
         * 
         * @access  protected
         * @param   string $str
         * @return  bool
         */
        protected function _referencesAlias(string $str)
        {
            if (preg_match('/^\'.+\'$/', $str) === 1) {
                return false;
            }
            $pieces = explode('.', $str);
            if (count($pieces) > 1) {
                return true;
            }
            return false;
        }

        /**
         * _replaceRecords
         * 
         * @access  protected
         * @return  void
         */
        protected function _replaceRecords()
        {
            $args = func_get_args();
            foreach ($args as $record) {
                foreach ($record as $column => $value) {
                    if (is_array($value) === false) {
                        $value = array($value, true);
                    }
                }
                array_push($this->_replaceRecords, $record);
            }
        }

        /**
         * _updateValues
         * 
         * Formats an update call to have it's data stored in a
         * consistent/organized way for parsing.
         * 
         * @note    third optional parameter in a call is whether or not to
         *          auto-add apostrophes
         * @access  protected
         * @return  void
         */
        protected function _updateValues()
        {
            $args = func_get_args();
            foreach ($args as $arg) {
                if (is_array($arg) === true) {
                    $keys = array_keys($arg);
                    if (is_string($keys[0]) === true) {
                        foreach ($arg as $sub => $value) {
                            if (is_array($value) === true) {
                                $params = $value;
                                array_unshift($params, $sub);
                                $callback = array($this, '_updateValues');
                                call_user_func_array($callback, $params);
                            } else {
                                $this->_updateValues($sub, $value);
                            }
                        }
                    } else {
                        $callback = array($this, '_updateValues');
                        call_user_func_array($callback, $arg);
                    }
                } elseif (is_string($arg) === true) {
                    if (count($args) === 2) {
                        $this->_updateValues[$args[0]] = array($args[1], true);
                    } elseif (count($args) === 3) {
                        $this->_updateValues[$args[0]] = array(
                            $args[1],
                            $args[2]
                        );
                    }
                    break;
                }
            }
        }

        /**
         * _wrapWithTildes
         * 
         * Wraps a string or array of strings in tildes to ensure proper query
         * escaping.
         * 
         * @note    The empty space check below is done to ensure more
         *          complicated "column" names don't get messed up (eg.
         *          "(t.tag) AGAINST ('love' IN NATURAL LANGUAGE MODE)").
         * @access  protected
         * @param   array|string $value
         * @return  array|string
         */
        protected function _wrapWithTildes($value)
        {
            if (is_array($value) === true) {
                $strs = $value;
                foreach ($strs as $index => $str) {
                    $strs[$index] = $this->_wrapWithTildes($str);
                }
                return $strs;
            }
            $haystack = $value;
            $needle = '`';
            $found = strpos($haystack, $needle) !== false;
            if ($found === true) {
                return $value;
            }
            if ($value === '*') {
                return $value;
            }
            if ($value === 'COUNT(1)') {
                return $value;
            }
            if ($value === 'RAND()') {
                return $value;
            }
            if ($value === 'MATCH') {
                return $value;
            }
            preg_match('/^MAX\(/', $value, $matches);
            if (count($matches) > 0) {
                return $value;
            }
            preg_match('/^DISTINCT\(/', $value, $matches);
            if (count($matches) > 0) {
                return $value;
            }
            preg_match('/^COUNT\(DISTINCT\(/', $value, $matches);
            if (count($matches) > 0) {
                return $value;
            }
            if(strstr($value, ' ') !== false) {
                return $value;
            }
            $pieces = array();
            $exploded = explode('.', $value);
            foreach ($exploded as $piece) {
                array_push($pieces, '`' . ($piece) . '`');
            }
            return implode('.', $pieces);
        }

        /**
         * andWhere
         * 
         * An alias of <where>.
         * 
         * @access  public
         * @return  void
         */
        public function andWhere()
        {
            $args = func_get_args();
            $callback = array($this, 'where');
            call_user_func_array($callback, $args);
        }

        /**
         * average
         * 
         * An alias of Query::select(array('average' => 'AVG(column)')). Sets
         * the average of a column for the query to be selected.
         * 
         * @access  public
         * @param   string $column column that should have it's average
         *          calculated
         * @param   string $name (default: 'average') the name/alias/key for the
         *          average
         * @return  void
         */
        public function average(string $column, string $name = 'average')
        {
            $this->select(array($name => 'AVG(' . ($column) . ')'));
        }

        /**
         * count
         * 
         * An alias of Query::select(array('count' => 'COUNT(status)')). Sets
         * the number of columns for the query to be selected.
         * 
         * @note    $coloumn could be something like `DISTINCT user_id` for more
         *          accurate/flexible counting
         * @access  public
         * @param   string $column (default: '1') the column that should be used
         *          for counting
         * @param   string $name (default: 'count') the name/alias/key for the
         *          count
         * @return  void
         */
        public function count(string $column = '1', string $name = 'count')
        {
            $this->select(array($name => 'COUNT(' . ($column) . ')'));
        }

        /**
         * delete
         * 
         * @access  public
         * @return  void
         */
        public function delete()
        {
            $this->_type = 'delete';
            $args = func_get_args();
            $callback = array($this, 'table');
            call_user_func_array($callback, $args);
        }

        /**
         * filter
         * 
         * An alias of <having>.
         * 
         * @access  public
         * @return  void
         */
        public function filter()
        {
            $args = func_get_args();
            $callback = array($this, 'having');
            call_user_func_array($callback, $args);
        }

        /**
         * from
         * 
         * An alias of <table>.
         * 
         * @access  public
         * @return  void
         */
        public function from()
        {
            $args = func_get_args();
            $callback = array($this, 'table');
            call_user_func_array($callback, $args);
        }

        /**
         * groupBy
         * 
         * Specifies what columns/fields an SQL result set should be grouped
         * into.
         * 
         * @access  public
         * @return  void
         */
        public function groupBy()
        {
            $args = func_get_args();
            $this->_groupings = array_merge((array) $this->_groupings, $args);
        }

        /**
         * having
         * 
         * Sets the filters/having conditions for a select statement.
         * 
         * @access  public
         * @return  void
         */
        public function having()
        {
            $args = func_get_args();
            $callback = array($this, '_conditions');
            $this->_filters[][] = call_user_func_array($callback, $args);
        }

        /**
         * insert
         * 
         * Stores a column/value to be inserted, by calling _insertRecords 
         * internally.
         * 
         * @throws  Exception
         * @access  public
         * @return  void
         */
        public function insert()
        {
            // set query type
            $this->_type = 'insert';

            // argument retrieval for validation and storage
            $args = func_get_args();
            if (empty($args) === true) {
                $msg = 'Column must be specified for <insert> method.';
                throw new Exception($msg);
            }

            // internal input routing
            $args = func_get_args();
            $callback = array($this, '_insertRecords');
            call_user_func_array($callback, $args);
        }

        /**
         * into
         * 
         * An alias of <table>.
         * 
         * @access  public
         * @return  void
         */
        public function into()
        {
            $args = func_get_args();
            $callback = array($this, 'table');
            call_user_func_array($callback, $args);
        }

        /**
         * limit
         * 
         * An alias of <rows>.
         * 
         * @access  public
         * @return  void
         */
        public function limit()
        {
            $args = func_get_args();
            $callback = array($this, 'rows');
            call_user_func_array($callback, $args);
        }

        /**
         * lock
         * 
         * @access  public
         * @param   array $tables
         * @param   string $lockType
         * @return  void
         */
        public function lock(array $tables, string $lockType)
        {
            foreach ($tables as $key => $table) {
                $tables[$key] = ($table) . ' ' . strtoupper($lockType);
            }
            $this->_type = 'lock';
            $this->table($tables);
            // $this->_lockType = $lockType;
            $this->_lockType = '';// Temporary
        }

        /**
         * offset
         * 
         * What row to begin the retrieval from.
         * 
         * @access  public
         * @param   int|bool $offset (default: 0) what row to begin retrieval
         *          from (aka the result-set's offset)
         * @return  void
         */
        public function offset($offset = 0)
        {
            $this->_offset = $offset;
        }

        /**
         * orderBy
         * 
         * Sets the order expressions for an SQL select or update statement.
         * 
         * @throws  Exception
         * @access  public
         * @return  void
         */
        public function orderBy()
        {
            $args = func_get_args();
            if (count($args) === 1) {
                if ($args[0] === false) {
                    $this->_orders = array();
                } elseif (is_array($args[0]) === true) {
                    foreach ($args[0] as $key => $arg) {
                        if (is_string($key) === true) {
                            if (is_bool($arg) === true) {
                                $arg = array($key, $arg);
                            } elseif (is_array($arg) === true) {
                                $arg = array_values($arg);
                                array_unshift($arg, $key);
                            }
                        } elseif (count($arg) === 1) {
                            $arg = array($arg, true);
                        }
                        if (is_object($arg) === true) {
                            $arg = array($arg);
                        }
                        $callback = array($this, 'orderBy');
                        call_user_func_array($callback, $arg);
                    }
                } else {
                    $this->_orders[] = array($args[0], array(), true);
                }
            } elseif (count($args) === 2) {
                if (is_bool($args[1]) === true) {
                    $this->_orders[] = array($args[0], array(), $args[1]);
                } elseif (is_array($args[1]) === true) {
                    $this->_orders[] = array($args[0], $args[1], true);
                } else {
                    $msg = 'Unexpected second parameter type; should be ' .
                        '*boolean* or *array* (or not passed).';
                    throw new Exception($msg);
                }
            } elseif (count($args) === 3) {
                $this->_orders[] = $args;
            }
        }

        /**
         * orFilter
         * 
         * An alias of <orHaving>.
         * 
         * @access  public
         * @return  void
         */
        public function orFilter()
        {
            $args = func_get_args();
            $callback = array($this, 'orHaving');
            call_user_func_array($callback, $args);
        }

        /**
         * orHaving
         * 
         * Makes the previous having condition non-binding with a logical
         * OR/or/|| condition.
         * 
         * @throws  Exception
         * @access  public
         * @return  void
         */
        public function orHaving()
        {
            if (empty($this->_conditions) === true) {
                $msg = '<orHaving> call requires <having> call first.';
                throw new Exception($msg);
            }
            $args = func_get_args();
            $callback = array($this, 'having');
            call_user_func_array($callback, $args);
            $condition = array_pop($this->_conditions);
            $condition = $condition[0];
            $last = &$this->_conditions[count($this->_conditions) - 1];
            array_push($last, $condition);
        }

        /**
         * orWhere
         * 
         * Makes the previous where call/conditions non-binding with a logical
         * OR/or/|| condition.
         * 
         * @throws  Exception
         * @access  public
         * @return  void
         */
        public function orWhere()
        {
            if (empty($this->_conditions) === true) {
                $msg = '<orWhere> call requires <where> call first.';
                throw new Exception($msg);
            }
            $args = func_get_args();
            $callback = array($this, 'where');
            call_user_func_array($callback, $args);
            $condition = array_pop($this->_conditions);
            $condition = $condition[0];
            $last = &$this->_conditions[count($this->_conditions) - 1];
            array_push($last, $condition);
        }

        /**
         * parse
         * 
         * Creates a valid SQL statement based on the properties set by this
         * instance of the Query class.
         * 
         * @throws  Exception
         * @access  public
         * @return  string valid, minified, SQL statement ready to be
         *          executed/run
         */
        public function parse()
        {
            // no table found
            if (empty($this->_tables) === true) {
                if ($this->_type !== 'unlock') {
                    $msg = 'Table must be specified for query';
                    throw new Exception($msg);
                }
            }

            // command
            if (is_null($this->_type) === true) {
                $msg = 'Query::$type must be specified by calling <select>,' .
                    '<set>, <delete> or <insert>.';
                throw new Exception($msg);
            }
            $command = strtoupper($this->_type);
            if ($this->_type === 'insert' || $this->_type === 'replace') {
                $command .= ' INTO';
            }

            // columns
            if ($this->_type === 'select') {
                $columns = array();
                foreach ($this->_columns as $key => $column) {
                    $column = preg_replace('/ as /i', ' AS ', $column);
                    if (strstr($column, ' AS ') !== false) {
                        list($column, $alias) = explode(' AS ', $column);
                        $column = $this->_wrapWithTildes($column) . ' AS ' .
                            ($alias);
                        $exp = $column;
                    } else {
                        $exp = $this->_wrapWithTildes($column);
                    }
                    if (is_object($column) === true) {
                        $exp = '(' . ($column->parse()) . ')';
                    }
                    if (is_string($key) === true) {
                        $exp .= ' AS ' . ($key);
                    }
                    $columns[] = $exp;
                }
                $columns = implode(', ', $columns);
            }

            // inputs
            if ($this->_type === 'update') {
                $inputs = array();
                foreach ($this->_updateValues as $column => $details) {
                    $exp = $this->_wrapWithTildes($column) . ' = ';
                    if (is_object($details[0]) === true) {
                        $exp .= '(' . ($details[0]->parse()) . ')';
                    } elseif ($details[1] === true) {
                        if (
                            is_int($details[0]) === true
                            || in_array($details[0], array('NOW()')) === true
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
            } elseif ($this->_type === 'insert' || $this->_type === 'replace') {
                $columns = array();
                $values = array();
                $records = $this->_insertRecords;
                if ($this->_type === 'replace') {
                    $records = $this->_replaceRecords;
                }
                foreach ($records as $record) {
                    if (empty($columns) === true) {
                        $columns = array_keys($record);
                    }
                    $recordValues = array();
                    foreach ($record as $column => $value) {
                        if (is_object($value[0]) === true) {
                            $value = '(' . ($value[0]->parse()) . ')';
                        } elseif ($value[1] === true) {
                            if (
                                is_int($value[0]) === false
                                && in_array($value[0], array('NOW()')) === false
                            ) {
                                $value = '\'' . ($value[0]) . '\'';
                            } else {
                                $value = $value[0];
                            }
                        } else {
                            $value = $value[0];
                        }
                        array_push($recordValues, $value);
                    }
                    array_push($values, $recordValues);
                }
            }

            // tables
            $tables = array();
            foreach ($this->_tables as $alias => $table) {
                if (is_int($alias) === true) {
                    $table = preg_replace('/ as /i', ' AS ', $table);
                    if (strstr($table, ' AS ') !== false) {
                        list($table, $alias) = explode(' AS ', $table);
                        $tables[] = $this->_wrapWithTildes($table) . ' AS ' .
                            ($alias);
                    } else {
                        $tables[] = $this->_wrapWithTildes($table);
                    }
                } else {
                    $tables[] = $this->_wrapWithTildes($table) . ' AS ' .
                        ($alias);
                }
            }
            $tables = implode(', ', $tables);

            // conditions
            if (
                in_array(
                    $this->_type,
                    array('delete', 'select', 'update')
                ) === true
            ) {
                $conditions = array();
                if (empty($this->_conditions) === false) {
                    foreach ($this->_conditions as $clause) {
                        $or = array();
                        foreach ($clause as $inclusionary) {
                            $and = array();
                            foreach ($inclusionary as $column => $details) {
                                $value = $details[1];
                                if (is_object($value) === true) {
                                    $value = '(' . ($value->parse()) . ')';
                                } elseif (is_array($value) === true) {
                                    if ($details[2] === false) {
                                        $value = '(' . implode(', ', $value) . ')';
                                    } else {
                                        $values = array();
                                        foreach ($details[1] as $subcolumn) {
                                            if (is_int($subcolumn) === false) {
                                                $values[] = '\'' . ($subcolumn) . '\'';
                                            } else {
                                                $values[] = $subcolumn;
                                            }
                                        }
                                        $value = '(' . implode(', ', $values) . ')';
                                    }
                                } elseif ($details[2] === true) {
                                    if (is_int($value) === false) {
                                        $value = '\'' . ($value) . '\'';
                                    }
                                }
                                $condition = $this->_wrapWithTildes($column) .
                                    ' ' . ($details[0]) . ' ';
                                if ($this->_referencesAlias($value) === true) {
                                    $condition .= $this->_wrapWithTildes($value);
                                } else {
                                    $condition .= $value;
                                }
                                $and[] = $condition;
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
            if (in_array($this->_type, array('select')) === true) {
                $groupings = array();
                if (empty($this->_groupings) === false) {
                    $groupings = implode(', ', $this->_groupings);
                }
            }

            // filters
            if (in_array($this->_type, array('select', 'update')) === true) {
                $filters = array();
                if (empty($this->_filters) === false) {
                    foreach ($this->_filters as $clause) {
                        $or = array();
                        foreach ($clause as $inclusionary) {
                            $and = array();
                            foreach ($inclusionary as $column => $details) {
                                $value = $details[1];
                                if (is_object($value) === true) {
                                    $value = '(' . ($value->parse()) . ')';
                                } elseif (is_array($value) === true) {
                                    if ($details[2] === false) {
                                        $value = '(' . implode(', ', $value) . ')';
                                    } else {
                                        $values = array();
                                        foreach ($details[1] as $subcolumn) {
                                            if (is_int($subcolumn) === false) {
                                                $values[] = '\'' . ($subcolumn) . '\'';
                                            } else {
                                                $values[] = $subcolumn;
                                            }
                                        }
                                        $value = '(' . implode(', ', $values) . ')';
                                    }
                                } elseif ($details[2] === true) {
                                    if (is_int($value) === false) {
                                        $value = '\'' . ($value) . '\'';
                                    }
                                }

                                $condition = $this->_wrapWithTildes($column) .
                                    ' ' . ($details[0]) . ' ';
                                if ($this->_referencesAlias($value) === true) {
                                    $condition .= $this->_wrapWithTildes($value);
                                } else {
                                    $condition .= $value;
                                }
                                $and[] = $condition;
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
            if (
                in_array(
                    $this->_type,
                    array('delete', 'select', 'update')
                ) === true
            ) {
                $orders = array();
                if (empty($this->_orders) === false) {
                    foreach ($this->_orders as $rule) {
                        $column = $rule[0];
                        if (is_object($column) === true) {
                            $column = '(' . ($column->parse()) . ')';
                        }
                        $exp = $this->_wrapWithTildes($column);
                        if (empty($rule[1]) === false) {
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
            if (
                in_array(
                    $this->_type,
                    array('delete', 'select', 'update')
                ) === true
            ) {
                $rows = $this->_rows;
            }

            // offset
            if (in_array($this->_type, array('select')) === true) {
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
                if ($rows !== false) {
                    $statement .= ' LIMIT ' . ($rows);
                }
                if ($offset !== false) {
                    $statement .= ' OFFSET ' . ($offset);
                }
            } elseif ($this->_type === 'delete') {
                $statement .= ' FROM ' . ($tables);
                if (empty($conditions) === false) {
                    $statement .= ' WHERE ' . ($conditions);
                }
                if (empty($orders) === false) {
                    $statement .= ' ORDER BY ' . ($orders);
                }
                if (empty($rows) === false) {
                    $statement .= ' LIMIT ' . ($rows);
                }
            } elseif ($this->_type === 'insert' || $this->_type === 'replace') {
                $statement .= ' ' . ($tables);
                $columns = $this->_wrapWithTildes($columns);
                $statement .= ' (' . implode(', ', $columns) . ')';
                $statement .= ' VALUES ';
                foreach ($values as $index => $value) {
                    $values[$index] = '(' . implode(', ', $value) . ')';
                }
                $statement .= implode(', ', $values);
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
            } elseif ($this->_type === 'lock') {
                $statement .= ' TABLES';
                $statement .= ' ' . ($tables);
                $statement .= ' ' . strtoupper($this->_lockType);
            } elseif ($this->_type === 'unlock') {
                $statement .= ' TABLES';
            }
            return $statement;
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
         * @access  public
         * @param   false|int $rows number of rows to limit the result set by
         * @return  void
         */
        public function rows($rows = 10)
        {
            if ($rows === false || $rows === 0) {
                $this->offset(false);
            }
            $this->_rows = $rows;
        }

        /**
         * select
         * 
         * Sets the columns to return in a select statement.
         * 
         * @access  public
         * @return  void
         */
        public function select()
        {
            $this->_type = 'select';
            $args = func_get_args();
            if (empty($args) === true) {
                $this->select('*');
            } elseif ($args[0] === false) {
                $this->_columns = [];
            } else {

                // loop through arguments, formatting the primary key selection
                // or adding the columns
                foreach ($args as $arg) {

                    // can't be if/elseif, need's to be consecutive
                    if (is_array($arg) === false) {
                        // $this->_columns[] = $this->_wrapWithTildes($arg);
                        $this->_columns[] = $arg;
                    }
                    if (is_array($arg) === true) {
                        foreach ($arg as $key => $value) {
                            if (is_int($key) === true) {
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
         * replace
         * 
         * @throws  Exception
         * @access  public
         * @return  void
         */
        public function replace()
        {
            // set query type
            $this->_type = 'replace';

            // argument retrieval for validation and storage
            $args = func_get_args();
            if (empty($args) === true) {
                $msg = 'Column must be specified for <replace> method.';
                throw new Exception($msg);
            }

            // internal input routing
            $args = func_get_args();
            $callback = array($this, '_replaceRecords');
            call_user_func_array($callback, $args);
        }

        /**
         * set
         * 
         * Stores a column/value to be updated, by calling _updateValues
         * interally. If no arguments passed, calls itself with default
         * columns/values.
         * 
         * @throws  Exception
         * @access  public
         * @return  void
         */
        public function set()
        {
            // set query type
            $this->_type = 'update';

            /**
             * By default; remove limit on update query; note that this is set
             * here, rather than in the <parse> method, to allow for it to be
             * overridden.
             */
            $this->limit(false);

            // argument retrieval for validation and storage
            $args = func_get_args();
            if (empty($args) === true) {
                $msg = 'Column must be specified for <set> method.';
                throw new Exception($msg);
            }

            // internal input routing
            $args = func_get_args();
            $callback = array($this, '_updateValues');
            call_user_func_array($callback, $args);
        }

        /**
         * sum
         * 
         * An alias of Query::select(array('sum' => 'SUM(column)')). Sets the
         * sum of a column for the query to be selected.
         * 
         * @access  public
         * @param   string $column column that should have it's sum calculated
         * @param   string $name (default: 'sum') the name/alias/key for the sum
         * @return  void
         */
        public function sum(string $column, string $name = 'sum')
        {
            $this->select(array($name => 'SUM(' . ($column) . ')'));
        }

        /**
         * table
         * 
         * Records the tables that should be used for the statement, with their
         * alias/key specified.
         * 
         * @access  public
         * @return  void
         */
        public function table()
        {
            $args = func_get_args();
            foreach ($args as $arg) {
                if (is_array($arg) === true) {
                    foreach ($arg as $key => $value) {
                        if (is_int($key) === true) {
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
         * unlock
         * 
         * @access  public
         * @return  void
         */
        public function unlock()
        {
            $this->_type = 'unlock';
        }

        /**
         * update
         * 
         * An alias of <table>.
         * 
         * @access  public
         * @return  void
         */
        public function update()
        {
            $args = func_get_args();
            $callback = array($this, 'table');
            call_user_func_array($callback, $args);
        }

        /**
         * where
         * 
         * Sets the conditionals for a select or set statement.
         * 
         * @access  public
         * @return  void
         */
        public function where()
        {
            $args = func_get_args();
            $callback = array($this, '_conditions');
            $this->_conditions[][] = call_user_func_array($callback, $args);
        }
    }
