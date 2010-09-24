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
 * Custom view to set the parser-interceptor
 * 
 * @version $Id$
 * @package	Tx_FormhandlerFluid
 * @subpackage Core
 * @author	Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_View_TemplateView extends Tx_Fluid_View_TemplateView
{
	/**
	 * Build parser configuration
	 *
	 * @return Tx_Fluid_Core_Parser_Configuration
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function buildParserConfiguration()
	{
		$this->interceptor = $this->objectManager->get('Tx_FormhandlerFluid_Core_Parser_Interceptor');		
		$parserConfiguration = parent::buildParserConfiguration();
		$parserConfiguration->addInterceptor($this->interceptor);
		return $parserConfiguration;
	}
}