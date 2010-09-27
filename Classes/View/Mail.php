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
 * This is a proxy between formhandler controller and fluid view
 *
 * @version $Id$
 * @package	Tx_Formhandler
 * @subpackage View
 * @author Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_View_Mail extends Tx_FormhandlerFluid_View_Form
{
	protected function initializeView()
	{
		parent::initializeView();
		$this->controllerContext->getRequest()->setControllerName('Mail');
	}
	
	/**
	 * Main method called by the controller.
	 *
	 * @param array $gp The current GET/POST parameters
	 * @param array $errors In this class the second param is used to pass information about the email mode (HTML|PLAIN)
	 * @return string content
	 */
	public function render($gp, $config)
	{
		if ($this->hasTemplate())
		{
    		$result = parent::render($config['suffix'] != 'plain' ? $this->checkBinaryCrLf($gp) : $gp, array());
		}
		
		$this->controllerContext->getRequest()->setFormat('html');
		return trim($result);
	}
	
	public function setTemplate($templateCode, $templateName, $forceTemplate = FALSE)
	{
		$conf = explode('_', $templateName);
		$this->setAction(strtolower($conf[1]));
		if (stripos($conf[2], 'plain') === 0)
		{
			$this->controllerContext->getRequest()->setFormat('txt');
		}else{
			$this->controllerContext->getRequest()->setFormat('html');
		}
	}
	
	/**
	 * Sanitizes GET/POST parameters by processing the 'checkBinaryCrLf' setting in TypoScript
	 *
	 * @return void
	 */
	protected function checkBinaryCrLf($gp)
	{
		$componentSettings = $this->getComponentSettings();
		if ($componentSettings['checkBinaryCrLf'])
		{
			$paramsToCheck = t3lib_div::trimExplode(',', $componentSettings['checkBinaryCrLf']);
			foreach($paramsToCheck as $field) {
				if(!is_array($field)) {
					$gp[$field] = str_replace (chr(13), '', $gp[$field]);
					$gp[$field] = str_replace ('\\', '', $gp[$field]);
					$gp[$field] = nl2br($gp[$field]);
				}
			}
		}
		return $gp;
	}
}