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
 * Strips all HTML and applyes basic formats
 *
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	View_Fluid_ViewHelper
 */
class Tx_FormhandlerFluid_ViewHelpers_Format_TrimViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Trims the content
	 * 
	 * @param Boolean $lines When setting this each line is trimmed and empty lines will be removed
	 * @return string The trimmed content
	 */
	public function render($lines = false) {
		$content = $this->renderChildren();
		if ($lines) {
			return preg_replace('/\s*\n\s*/', '', $content);
		}else{
			return trim($content);
		}
	}
}