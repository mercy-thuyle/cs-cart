<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*  
  _   _       _ _          _    _____           _       _       
 | \ | |     | | |        | |  / ____|         (_)     | |      
 |  \| |_   _| | | ___  __| | | (___   ___ _ __ _ _ __ | |_ ___ 
 | . ` | | | | | |/ _ \/ _` |  \___ \ / __| '__| | '_ \| __/ __|
 | |\  | |_| | | |  __/ (_| |  ____) | (__| |  | | |_) | |_\__ \
 |_| \_|\__,_|_|_|\___|\__,_| |_____/ \___|_|  |_| .__/ \__|___/
                                                 | |            
   _____                                      _ _|_|            
  / ____|                                    (_) |              
 | |     ___  _ __ ___  _ __ ___  _   _ _ __  _| |_ _   _       
 | |    / _ \| '_ ` _ \| '_ ` _ \| | | | '_ \| | __| | | |      
 | |___| (_) | | | | | | | | | | | |_| | | | | | |_| |_| |      
  \_____\___/|_| |_| |_|_| |_| |_|\__,_|_| |_|_|\__|\__, |      
                                                     __/ |      
                                                    |___/       
       NullScripts | Best Null Scripts Site In The World
	     --==== DOWNLOADED FROM NULLSCRIPTS.NET ====--	                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Api;

// Area will be defined in Api::defineArea.
define('API', true);
define('NO_SESSION', true);

try {
    require dirname(__FILE__) . '/init.php';

    /**
     * @var Api $api
     */
    $api = Tygh::$app['api'];
    if ($api instanceof Api) {
        $api->handleRequest();
    }
} catch (Exception $e) {
    \Tygh\Tools\ErrorHandler::handleException($e);
} catch (Throwable $e) {
    \Tygh\Tools\ErrorHandler::handleException($e);
}