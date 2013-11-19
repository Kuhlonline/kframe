<?php

	chdir("./../../");
	$appStr 	= (isset($_GET['page'])) ? (($_GET['page']) ? $_GET['page'] : 'main') : 'main';

	/** Page Initialization **/
	$pageInit	= getcwd()."/sys/boot/page_init.php";
	$loaded		= include($pageInit);
	if (!$loaded) {
		throw new Exception('Could not initialize page');
	}

	/** Include Page **/
	$pageUX		= getcwd()."/ui/$appStr.phtml";
	$loaded		= include($pageUX);
	if (!$loaded) {
		throw new Exception('Could not load page');
	}

?>