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
class Tx_FormhandlerFluid_ViewHelpers_Form_LabelViewHelper extends Tx_Fluid_Core_ViewHelper_TagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'label';
	
	protected $for;
	
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
	}
	
	/**
	 * @param string $for The id of the element for which the label is
	 * @return string
	 */
	public function render($for) {
		$this->for = $for;
		
		$this->detectErrors();
		
		$this->tag->addAttribute('for', $for);
		$this->tag->setContent($this->renderChildren());

		return $this->tag->render();
	}
	
	protected function detectErrors() {
		/*
		//Ineffective:
		$errors = $this->controllerContext->getRequest()->getErrors($for);
		$valid = true;
		foreach ($errors as $error) {
			if ($error instanceof Tx_Extbase_Validation_PropertyError) {
				if ($error->getPropertyName() === $this->for) {
					$valid = false;
					break;
				}
			}
		}*/
		// Not cleaner but faster:
		if (!$this->templateVariableContainer->offsetExists('errors')) {
			return;
		}
		$errors = $this->templateVariableContainer->get('errors');
		$valid = empty($errors[$this->for]);
		
		if (!$valid) {
    		$class = ($this->arguments['class']) ? $this->arguments['class'].' ' : '';
    		$class .= 'error';
    		$this->tag->addAttribute('class', $class);
		}
	}
	
	protected function detectRequired() {
		
	}
}