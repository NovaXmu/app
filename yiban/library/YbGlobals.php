<?php

/**
* 
*/
class Library_YbGlobals
{
	
 	function __construct()
	{
		if (!defined('Library_Ybapi_CLASSESS_DIR'))
		{
			define('Library_Ybapi_CLASSESS_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
			
			require(Library_Ybapi_CLASSESS_DIR.'Lang.php');
			require(Library_Ybapi_CLASSESS_DIR.'YbException.php');
			require(Library_Ybapi_CLASSESS_DIR.'YbOpenApi.php');
		}
	}
}
	

?>