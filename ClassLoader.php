<?php
     
    namespace Tools;

    /**
     * Simple class to capsule the classloader so more than one autoload function can be used.
     * This package is part of JPTs SocketFramework - it has been pushed into our core to prevent
     * from multiple autoload-registerings
     * 
     * @author JPT
     * @package JPT\SocketFramework
     * @version $Id:$
     */
    class ClassLoader {

        /**
         * Contains class name prefix and path prefix for all class paths it shall handle
         * 
         * @var array
         */
        protected $classNameMapping = array();
        
        /**
         * Default constructor.
         * Registers the main class path for the socket framework.
         */
        public function __construct() {
            $this->classNameMapping = array(
                'JPT\SocketFramework' =>  'SocketFramework' . 
                                          \DIRECTORY_SEPARATOR . 'Classes',
                'Tools' => __DIR__
            );
        }
        
        /**
         * Registers a new class path for the autoloader
         * 
         * @param string $classNamePrefix
         * @param string $pathPrefix
         */
        public function addClassPathMapping($classNamePrefix, $pathPrefix) {
            $this->classNameMapping[$classNamePrefix] = $pathPrefix;	
        }
        
        /**
         * Autoloader function.
         * 
         * @throws \Exception
         * @param string $className
         * @return void
         */
        public function loadClass($className) {
        
            $classDirectory = trim(str_replace('\\', \DIRECTORY_SEPARATOR, $className) . ".php",'/\\');

            if (file_exists($classDirectory)) {
                require_once($classDirectory);
            }
            
            if (class_exists($className,false))
                return;
                
            $matchingClassNamePrefix = FALSE;
            
            foreach($this->classNameMapping AS $classNamePrefix => $pathPrefix) {
            
                if(substr($className, 0, strlen($classNamePrefix)) !== $classNamePrefix)
                    continue;
                    
                $matchingClassNamePrefix = TRUE;
                $classNameSuffix         = substr($className, strlen($classNamePrefix), strlen($className));
                
                $fileName = $this->classNameMapping[$classNamePrefix];
                $fileName .= str_replace('\\', \DIRECTORY_SEPARATOR, $classNameSuffix) . ".php";
                
                if (stream_resolve_include_path($fileName)) {
                    $fileName = stream_resolve_include_path($fileName);
                }
                
                break;
                
            }
            if($matchingClassNamePrefix === FALSE) return;		
            if(file_exists($fileName) === TRUE) {
                require_once($fileName);
            }
            else {
                throw new \Exception("Could not load class: '" . $className . "' - File does not exist: '" . $fileName . "'!", 1289659295);
            }
        }
        
        /**
         * Simple function that registers loadClass as an autoloader.
         * 
         * @return void
         * @throws Exception in case spl_autoload_register fails.
         */
        public function initialize() {
            spl_autoload_register(array($this, 'loadClass'), TRUE, TRUE);
        }
        
    }
    
    $classLoader = new \Tools\ClassLoader();
    $classLoader->initialize();

?>