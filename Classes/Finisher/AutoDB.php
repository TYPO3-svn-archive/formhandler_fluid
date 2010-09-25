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
 * Interceptor to do some custom actions on each form element
 * 
 * @version $Id$
 * @package	Tx_FormhandlerFluid
 * @subpackage Core
 * @author	Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_Finisher_AutoDB extends Tx_Formhandler_Finisher_AutoDB
{	
	/**
	 * Retrieve the fieldnames registered by the fluid form (those include
	 * the prefix if set)
	 * 
	 * @return array
	 */
	protected function getFormFieldNames()
	{
		$arguments = Tx_FormhandlerFluid_Controller_Form::getControllerContext()->getArguments();
		
		/* @var $view Tx_FormhandlerFluid_View_Form */
		$view = $this->componentManager->getComponent('Tx_FormhandlerFluid_View_Form');
		$forms = $arguments->getArgument('stepForms')->getValue();
		
		foreach ($forms as $form) {
			$view->setForm($form);
			$view->render(array(),array());
		}
		
		return $arguments->getArgument('fieldNames')->getValue();
	}
}