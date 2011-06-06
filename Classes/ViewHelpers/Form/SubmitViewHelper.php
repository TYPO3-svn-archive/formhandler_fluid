<?php
/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This helper renders the submit buttons that are needed to tell formhandler
 * which action to do next.
 *
 * @version $Id$
 * @package	Tx_FormhandlerFluid
 * @subpackage View_Helpers
 * @author	Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_ViewHelpers_Form_SubmitViewHelper extends Tx_Fluid_ViewHelpers_Form_SubmitViewHelper {

	/**
	 * Renders the submit button.
	 *
	 * @return string
	 */
	public function render() {
		$this->tag->addAttribute('type', 'submit');
		$this->tag->addAttribute('value', $this->getValue());
		$this->tag->addAttribute('name', $this->getName());

		return $this->tag->render();
	}
	
	/**
	 * Finds the name out of the action-argument of this helper
	 * 
	 * @return string The name for the desired action 
	 */
	protected function getName() {
		$name = array('step');
		switch ($this->arguments['action']) {
			case 'reload':
				$name['step']	= Tx_FormhandlerFluid_Util_Div::getSessionValue('currentStep');
				$name['action']	= 'reload';
				break;
			case 'prev':
				$name['step']	= Tx_FormhandlerFluid_Util_Div::getSessionValue('currentStep') - 1;
				$name['action']	= 'prev';
				break;
			case 'next':
			default:
				$name['step']	= Tx_FormhandlerFluid_Util_Div::getSessionValue('currentStep') + 1;
				$name['action']	= 'next';
		}		
		return $this->prefixFieldName(implode('-', $name));
	}
}