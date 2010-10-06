<?php
/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *
 * $Id$
 *                                                                        */

/**
 * Our tiny autoloader
 *
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_FormhandlerFluid
 */
class Tx_FormhandlerFluid_Loader {	
	/**
	 * @var array The namespaces that this Loader cares for
	 */
	private static $_namespace = 'Tx_FormhandlerFluid';
	
	/**
	 * @var string The path to Pikee_Loader (Framework-Root)
	 */
	private static $_path = '';
	
	/**
	 * Registers the static autoload method to SPL
	 * 
	 * @param boolean 
	 * @return void
	 */
	public static function initAutoload() {
		self::$_path = t3lib_extMgm::extPath('formhandler_fluid').'Classes';
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}
	
	/**
	 * The autoloading method - checks is in one of the registered namespaces
	 * and tries to load it from the calculated path. If the class is not found
	 * after unsuccesfull inclusion it's generated on the fly and throws an
	 * exception in its constructor method.
	 * 
	 * @param string $className The name of a class to load
	 * @return void
	 */
	public static function autoload($className) {

        if (strpos($className, self::$_namespace) !== 0) {

        	return;
        }
        
        $fileName = self::$_path.str_replace('_', '/', substr($className, strlen(self::$_namespace))).'.php';
        
        include_once($fileName);

        // does the class requested actually exist now?
        if (class_exists($className) || interface_exists($className)) {

            return;
        }
       
        // no, create a new one!
        eval("class $className {

            function __construct() {
                throw new Exception('Class $className not found (tryed to load it from $fileName)');
            }

            static function __callstatic(\$m, \$args) {
                throw new Exception('Class $className not found (tryed to load it from $fileName)');
            }
        }");
    }
}