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
 * @see Tx_Fluid_ViewHelpers_Form_CheckboxViewHelper
 * @version $Id$
 * @package	Tx_FormhandlerFluid
 * @subpackage View_Helpers
 * @author	Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_ViewHelpers_Form_CheckboxViewHelper extends Tx_Fluid_ViewHelpers_Form_CheckboxViewHelper
{
	/**
	 * When formTagBuilder is in use you can use a label attribute that will
	 * be the label for this element (setting the id is recommended)
	 */
	public function initializeArguments()
	{
		parent::initializeArguments();
		if ($this->tag instanceof Tx_FormhandlerFluid_Core_ViewHelper_FormTagBuilder)
		{
    		$this->registerTagAttribute('label', 'string', 'The label for this element');
    		$this->registerTagAttribute('labelPlacement', 'string', 'Where to inject the label (before|after)');
		}
	}
	
	/**
	 * Overriden parent method - when binding to empty arrays an exception was thrown
	 *
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
		
		$this->tag->addAttribute('type', 'checkbox');

		$nameAttribute = $this->getName();
		$valueAttribute = $this->getValue();
		if ($checked === NULL && $this->isObjectAccessorMode()) {
			$propertyValue = $this->getPropertyValue();
			if (is_bool($propertyValue)) {
				$checked = $propertyValue === (boolean)$valueAttribute;
			} elseif (is_array($propertyValue)) {
				$checked = in_array($valueAttribute, $propertyValue);
				$nameAttribute .= '[]';
			} else {
			    // throw new Tx_Fluid_Core_ViewHelper_Exception('Checkbox viewhelpers can only be bound to properties of type boolean or array. Property "' . $this->arguments['property'] . '" is of type "' . (is_object($propertyValue) ? get_class($propertyValue) : gettype($propertyValue)) . '".' , 1248261038);
			    // ^ That was it before :S
			    // When the property is an empty multidimensional array:
				$parts = explode('.', $this->arguments['property']);
			    $nameAttribute = $this->prefixFieldName(array_shift($parts));
			    $nameAttribute .= (count($parts)) ? '['.implode('][', $parts).']' : '';
			    $checked = $propertyValue && $propertyValue == $this->arguments['value'];
			}
		}

		$this->registerFieldNameForFormTokenGeneration($nameAttribute);
		$this->tag->addAttribute('name', $nameAttribute);
		$this->tag->addAttribute('value', $valueAttribute);
		if ($checked) {
			$this->tag->addAttribute('checked', 'checked');
		}

		$this->setErrorClassAttribute();

		$hiddenField = $this->renderHiddenFieldForEmptyValue();
		return $hiddenField . $this->tag->render();
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