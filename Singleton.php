<?php
   
    namespace Tools;
    
    /**
     * Singleton core class
     *
     * @author David Beuchert
     * @package Tools
     */
    abstract class Singleton
    {
    
        /**
         * All created instances
         *
         * @var array
         */
        static private $instances = array();
        
        /**
         * Returns an object instance or, if not created, creates a new one and returns it
         *
         * @return mixed The instanced object
         */
        final public static function getInstance()
        {
            $class = get_called_class();
            
            if (empty(self::$instances[$class])) {
                $rc = new \ReflectionClass($class);
                self::$instances[$class] = $rc->newInstanceArgs(func_get_args());
            }
            return self::$instances[$class];
        }
        
        /**
         * Empty constructor to prevent ReflectionClass exception when instancing without constructor
         *
         * @return mixed The instanced object
         */
        public function __construct() {}
        
        /**
         * Prevent from object cloning
         *
         * @throws \Exception
         * @return void
         */
        final public function __clone()
        {
            throw new Exception('Cloning singletons is invalid');
        }
    
    }