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
 * @see Tx_Fluid_ViewHelpers_Form_RadioViewHelper
 * @version $Id$
 * @package	Tx_FormhandlerFluid
 * @subpackage View_Helpers
 * @author	Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_ViewHelpers_Form_RadioViewHelper extends Tx_Fluid_ViewHelpers_Form_RadioViewHelper
{
	/**
	 * @param boolean $checked Specifies that the input element should be preselected
	 * @param string $label The label for this element (Only in Tx_FormhandlerFluid_View_TemplateView)
	 * @param string $label Where to inject the label (left|right) (Only in Tx_FormhandlerFluid_View_TemplateView)
	 * @return string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Christian Opitz <co@netzelf.de>
	 */
	public function render($checked = NULL, $label = null, $labelPlacement = 'right')
	{
		if ($this->tag instanceof Tx_FormhandlerFluid_Core_ViewHelper_FormTagBuilder)
		{
			$this->tag->addAttribute('label', $label);
			$this->tag->addAttribute('labelPlacement', $labelPlacement);
		}
		return parent::render($checked);
	}
	
	// == fixing fluid bugs ==
	
	/* (non-PHPdoc)
	 * @see typo3/sysext/fluid/Classes/ViewHelpers/Form/Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper#getPropertyValue()
	 * @see http://forge.typo3.org/issues/9950
	 */
	protected function getPropertyValue() {
		$formObject = $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObject');
		$propertyName = $this->arguments['property'];
		return Tx_Extbase_Reflection_ObjectAccess::getPropertyPath($formObject, $propertyName);
	}
}