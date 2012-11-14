<?php

    namespace Tools;

    /**
     * Extends mysqli by some useful functions
     *
     * @author David Beuchert
     * @package Tools
     */
    
    class Database extends \mysqli {
    
        /**
         * Multiplier for milliseconds to microseconds
         * @constant Database::INTERVAL_MILLISECONDS
         */
        const INTERVAL_MILLISECONDS = 1000;
        
        /**
         * Multiplier for seconds to microseconds
         * @constant Database::INTERVAL_SECONDS
         */
        const INTERVAL_SECONDS = 1000000;
    
        /**
         * The last query result object
         * @var \mysqli_result
         */
        protected $result;
        
        /**
         * Query sleep time
         * @var int
         * @see Database::setQuerySleep()
         */
        protected $querySleep = 0;
        
    
        /**
         * Instances mysqli by using the default access and checks for connection errors
         *
         * @throws \Exception
         * @param string $host
         * @param string $db
         * @param string $user
         * @param string $pass
         */
        public function __construct($host, $db, $user = 'root', $pass = '') {
        
            parent::__construct($host, $user, $pass, $db);
            
            if ($this->connect_error) {
                new \Exception('Could not connect to database - MySQL error: ' . $this->connect_error, $this->connect_errno);
            }
            
        }

        /**
         * Perfoms a query and stores the result for later use
         *
         * @param string $query
         * @param array $args
         * @throws \Exception
         * @return bool|\mysqli_result|\Tools\Database
         */
        public function query($query, $args = Array()) {
        
            if ($this->querySleep > 0) {
                usleep($this->querySleep);
            }

            if (count($args) > 0) {
                if (!is_array($args)) {
                    $args = Array($args);
                }
                foreach ($args as $key => $arg) {
                    $args[$key] = $this->escape_string($arg);
                }
                $query = vsprintf($query, $args);
            }
        
            $this->result = parent::query($query);
            
            if ($this->result === false) {
                throw new \Exception('MySQL Query "' . $query . '" failed with message: ' . $this->error, $this->errno);
            }
            
            return $this;
            
        }
        
        /**
         * Returns an array of result rows based on the latest result
         *
         * @return Array
         */
        public function getArray() {
        
            if (!($this->result instanceof \mysqli_result)) {
                trigger_error('Unable to return result array as the result is not an instance of MySQLi_Result');
                return Array();
            }
            
            $return = Array();
            while ($row = $this->result->fetch_assoc()) {
                $return[] = $row;
            }
            
            return $return;
            
        }
        
        /**
         * Returns a single result row
         *
         * @return Array
         */
        public function getSingle() {
            
            $result = $this->getArray();
            if (count($result) > 0) {
                return $result[0];
            }
            
            return Array();
            
        }
        
        /**
         * Returns the current result object
         *
         * @return \mysqli_result
         */
        public function getResult() {
            return $this->result;
        }
        
        /**
         * Forces a sleep before each query to prevent database overloading (useful when working with live databases)
         *
         * @param int|bool $sleep Sleep time in milliseconds or false to turn off
         * @param int $multiplier The multiplier for the sleep value to be transformed for usleep (predfined constants are INTERVAL_MILLISECONDS and INTERVAL_SECONDS)
         * @return \Tools\Database
         */
        public function setQuerySleep($sleep, $multiplier = self::INTERVAL_MILLISECONDS) {
        
            // Zero or false should turn it off
            if (!$sleep || $sleep < 0) {
                $sleep = 0;
            }
            
            // Multiply by 1000 to transform milliseconds to microseconds
            $this->querySleep = $sleep * $multiplier;
        
            return $this;
        
        }
        
    }