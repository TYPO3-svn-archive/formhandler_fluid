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
 * Interceptor to inject own tagbuilders on demand
 * 
 * @version $Id$
 * @package	Tx_FormhandlerFluid
 * @subpackage Core
 * @author	Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_Core_Parser_Interceptor implements Tx_Fluid_Core_Parser_InterceptorInterface {

	/**
	 * @var Tx_FormhandlerFluid_ViewHelpers_FormViewHelper
	 */
	protected $form;

	/**
	 * Adds a ViewHelper node using the EscapeViewHelper to the given node.
	 * If "escapingInterceptorEnabled" in the ViewHelper is FALSE, will disable itself inside the ViewHelpers body.
	 *
	 * @param Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface $node
	 * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
	 * @return Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function process(Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface $node, $interceptorPosition) {
		if ($interceptorPosition === Tx_Fluid_Core_Parser_InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER &&
			$node instanceof Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode) {
			if ($this->form && $node->getViewHelper() instanceof Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper) {
				$tagbuilder = t3lib_div::makeInstance('Tx_FormhandlerFluid_Core_ViewHelper_FormTagBuilder');
				$tagbuilder->setFormViewHelper($this->form);
				$node->getViewHelper()->injectTagBuilder($tagbuilder);
			} 
			elseif ($node->getViewHelper() instanceof Tx_FormhandlerFluid_ViewHelpers_FormViewHelper) {
				$this->form = $node->getViewHelper();
			}
		}
		elseif ($this->form &&
			$interceptorPosition === Tx_Fluid_Core_Parser_InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER &&
			$node instanceof Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode &&
			$node->getViewHelper() instanceof Tx_FormhandlerFluid_ViewHelpers_FormViewHelper) {
			$this->form = null;
		}
		return $node;
	}

	/**
	 * This interceptor wants to hook into object accessor creation, and opening / closing ViewHelpers.
	 *
	 * @return array Array of INTERCEPT_* constants
	 */
	public function getInterceptionPoints() {
		return array(
			Tx_Fluid_Core_Parser_InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER,
			Tx_Fluid_Core_Parser_InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER
		);
	}
}