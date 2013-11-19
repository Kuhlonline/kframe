<?php
	
	/** Boot **/
	if (!require('./api_init.php')) {
		throw new Exception('Could not initialize');
		exit();
	}




	/**
	 Functional Methods
	 */




	/**
	 * Load Event Handlers
	 * @param  array  $restData Array of Rest Data
	 * @return void
	 */
	function loadEventHandlers(array $restData = array()) {
		global $asei;
		global $mlHandler;

		$asei		= new asei($restData);
		$mlHandler 	= new htmlOutputHandler();

		registerObject('mlHandler', $mlHandler);
		registerHandler('renderHtml', 'mlHandler', 'handleHtmlOutput');

		//$asei->processRequest();
	}



	/**
	 * Load Rest Data
	 * Parses URL redirect request in to data
	 * @return array Array of rest data
	 */
	function loadRestData() {
		$rest = array('rest'=>null);

		if (isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING'], $rest);
		}

		$data = array_filter(explode('/', $rest['rest']));
		if (!$data or !count($data)) return false;

		$keys			= array_keys($data);
		$firstKey		= (isset($keys[0])) ? $keys[0] : null;
		$lastKey		= $keys[count($keys)-1];
		$class 			= (isset($data[$firstKey]))	? $data[$firstKey] 	: null;
		$type 			= (isset($data[$lastKey]))	? $data[$lastKey] 	: null;
		
		if ($class) 					unset($data[$firstKey]);
		if (isset($data[$lastKey])) 	unset($data[$lastKey]);
	

		sort($data);
		$param 			= $data;
		$reqMethod 		= strtolower($_SERVER['REQUEST_METHOD']);
		$method 		= $reqMethod;
		$valid			= ($class and $method and $type);


		/** Get GET/POST/PUT/DELETE **/
		switch ($reqMethod) {

			case 'post':
				$param 			= array_merge($_POST, $param);
			break;

			case 'delete':
			case 'put':
				$buffer 		= array();
				parse_str(file_get_contents('php://input'), $buffer);
				$param 			= array_merge($buffer, $param);
			break;

			default:
			case 'get':
			break;

		}

		if ($reqMethod == 'post') {
			$param 			= array_merge($_POST, $param);
		}

		$struct	= array(
			'class'			=> $class,
			'method'		=> $method,
			'type'			=> $type,
			'data'			=> $param,
			'valid'			=> $valid,
			'request_type'	=> $reqMethod
		);

		return $struct;
	}



?>