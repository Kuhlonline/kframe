<?php

	global $config;
	global $asei;



	/**
	 * Boot
	 */
	setWorkingFolder();



	/**
	 * Global Config
	 * Load resi configuration
	 * then check that it was loaded
	 */
	$config 	= loadConfig();



	/**
	 * Timezone
	 */
	date_default_timezone_set("America/New_York");



	/**
	 * Get Libraries
	 */
	loadLibraries();



    /** 
     * Rest Data
     */
    $restData 		= loadRestData();
    $group			= $config['groups']['default'];
    $class 			= $restData['class'];



    /** Permissions **/
    global $restrictions;
    if (isset($config[$group][$class])) {
    	$method 	= strtoupper($restData['method']);
    	$view 		= $config[$group][$class]['view'];
    	$allowed 	= ($config[$group][$class][$method] != false);
    } else {
    	$allowed 	= false;
    	$view 		= 'self';
    }

    $restrictions	= array(
    	'view'		=> 'self',
    	'access'	=> $allowed,
    	'group'		=> $group
    );


    /** 
     * Load Event Handlers
     */
	loadEventHandlers($restData);



	/**
	 * If request is permitted, connect to database
	 * and register api request class objects
	 */
	if ($allowed) {
		
		if (!connectToDatabase()) {
			throw new Exception("Could not Connect to database");
			exit();
		}

		registerObjects();
	}



	/**
	 * Create Global Server Event Handler
	 */
	$asei->processRequest($denied);












	/**
	 Functional Methods
	 */



	/**
	 * Connect to Database
	 * @return boolean True if connected, False if not or error.
	 */
	function connectToDatabase() {
	    global $sqlConnection;
	    global $sqlConnected;
	    global $config;
	    
	    $cfgDatabase    = $config['mysql'];	    
	    if ($cfgDatabase['connect'] != true) return false;


	    $sqlConnection  = new mysqli(
	        $cfgDatabase['host'],
	        $cfgDatabase['user'],
	        $cfgDatabase['password'],
	        $cfgDatabase['database'],
	        $cfgDatabase['port']
	    );
	    
	    $sqlConnected   = ($sqlConnection->errno == 0);

	    return $sqlConnected;
	}



	/**
	 * Register Objects
	 * Registers all self registering objects at startup
	 * which are declared in [objects] section of the resi.conf
	 * @return void
	 */
	function registerObjects() {
		global $config;

		$objects = (isset($config['objects'])) ? $config['objects'] : null;

		
		foreach ($objects as $name => $type) {
			$obj 		= new $type();
			registerObject($name, $obj);
			$obj 		= null;
		}
		
	}



	/**
	 * Load Libraries
	 * Loads libraries based on what type of request
	 * is being made. Library types and locations are
	 * set in the global configuration
	 * @return void
	 */
	function loadLibraries() {
		global $clientMode;
		global $config;		
		global $workingFolder;

		switch ($clientMode) {

			case 'rest_api':
			default:
				$libList	= (isset($config['resi']['library']))
					? $config['resi']['library']
					: array()
				;
			break;
		}

		$ext 	= (isset($libList['ext']))
			? $libList['ext']
			: 'php'
		;

		$folder = (isset($libList['folder']))
			? $workingFolder.'/'.$libList['folder']
			: null
		;

		if (isset($libList['ext'])) 	unset($libList['ext']);
		if (isset($libList['folder']))	unset($libList['folder']);

		ksort($libList);

		foreach ($libList as $lib) {
			$path 			= realpath("$folder/$lib.$ext");
			if (!$path) 	continue;

			$included		= include($path);
			if (!$included)	continue;
		}
	}



	/**
	 * Set Working Folder
	 * Sets absolute root directory for system
	 */
	function setWorkingFolder() {
		global $workingFolder;
		$workingFolder = realpath('./../');
		define("ROOT", $workingFolder);
	}



	/**
	 * Load Configuration
	 * @return array resi configuration array
	 */
	function loadConfig() {
		$configPath			= realpath(ROOT."/cfg/sys.conf");
		if (!$configPath)	return false;

		$config 			= parse_ini_file($configPath, true);

		if ($config === false) {
			throw new Exception('Could not Load Config');
			exit();
		}

		return $config;
	}
	
?>