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
 * This helper overrides the hidden fields of the default form-helper and
 * takes the formValuesPrefix of formhandler into account
 * 
 *
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	View_Fluid_ViewHelper
 */
class Tx_FormhandlerFluid_ViewHelpers_FormViewHelper extends Tx_Fluid_ViewHelpers_FormViewHelper {

	/**
	 * @var Whether values should be rendered without elements
	 */
	protected $readOnly;
	
	/**
	 * Whether values should be rendered without elements
	 * @return boolean
	 */
	public function isReadOnly() {
		return $this->readOnly;
	}
	
	/**
	 * Render the form.
	 *
	 * @param string $action Target action
	 * @param array $arguments Arguments
	 * @param string $controller Target controller
	 * @param string $extensionName Target Extension Name (without "tx_" prefix and no underscores). If NULL the current extension name is used
	 * @param string $pluginName Target plugin. If empty, the current plugin name is used
	 * @param integer $pageUid Target page uid
	 * @param mixed $object Object to use for the form. Use in conjunction with the "property" attribute on the sub tags
	 * @param integer $pageType Target page type
	 * @param string $fieldNamePrefix Prefix that will be added to all field names within this form. If not set the prefix will be tx_yourExtension_plugin
	 * @param string $actionUri can be used to overwrite the "action" attribute of the form tag
	 * @param boolean $readOnly When set no form-elements will be rendered but only the values or selected/checked labels
	 * @return string rendered form
	 */
	public function render($action = NULL, array $arguments = array(), $controller = NULL, $extensionName = NULL, $pluginName = NULL, $pageUid = NULL, $object = NULL, $pageType = 0, $fieldNamePrefix = NULL, $actionUri = NULL, $readOnly = false) {
		$this->readOnly = $readOnly;
		$tag = parent::render($action, $arguments, $controller, $extensionName, $pluginName, $pageUid, $object, $pageType, $fieldNamePrefix, $actionUri);
		
		return str_replace('[---nope---]', '', ($this->readOnly) ? $this->tag->getContent() : $tag);
	}
	
	/* (non-PHPdoc)
	 * @see typo3/sysext/fluid/Classes/ViewHelpers/Tx_Fluid_ViewHelpers_FormViewHelper#renderHiddenReferrerFields()
	 */
	protected function renderHiddenReferrerFields() {
		if ($this->readOnly){
			return '';
		}
		
		$randomID = Tx_Formhandler_Globals::$randomID;
		
		$hiddenFields = '
		<input type="hidden" name="no_cache" value="1" />
		<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'" />
		<input type="hidden" name="'.$this->prefixFieldName('submitted').'" value="1" />
		<input type="hidden" name="'.$this->prefixFieldName('randomID').'" value="'.$randomID.'" />
		<input type="hidden" id="removeFile-'.$randomID.'" name="'.$this->prefixFieldName('removeFile').'" value="" />
		<input type="hidden" id="removeFileField-'.$randomID.'" name="'.$this->prefixFieldName('removeFileField').'" value="" />
		<input type="hidden" id="submitField-'.$randomID.'" name="'.$this->prefixFieldName('submitField').'" value="" />';
		
		return $hiddenFields;
	}
	
	/* (non-PHPdoc)
	 * @see typo3/sysext/fluid/Classes/ViewHelpers/Tx_Fluid_ViewHelpers_FormViewHelper#getFieldNamePrefix()
	 */
	protected function getFieldNamePrefix() {
		return (string) Tx_Formhandler_Globals::$formValuesPrefix;
	}
	
	/**
	 * Trigger ObjectAccessorMode
	 * @see Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper#isObjectAccessorMode()
	 */
	protected function addFormNameToViewHelperVariableContainer() {
		if (Tx_Formhandler_Globals::$formValuesPrefix) {
			$this->viewHelperVariableContainer->add('Tx_Fluid_ViewHelpers_FormViewHelper', 'formName', '---nope---');
		}
	}
	
	/**
	 * Propagate the registered fieldnames to the controller
	 */
	protected function removeFormFieldNamesFromViewHelperVariableContainer() {
		$names = $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formFieldNames');
		foreach ($names as $i => $name){
			$names[$i] = str_replace('[---nope---]', '', $name);
		}
		
		$this
		->controllerContext
		->getArguments()
		->getArgument('fieldNames')
		->setValue($names);
		
		parent::removeFormFieldNamesFromViewHelperVariableContainer();
	}

	/**
	 * Removes the "form name" from the ViewHelperVariableContainer.
	 */
	protected function removeFormNameFromViewHelperVariableContainer() {
		if (Tx_Formhandler_Globals::$formValuesPrefix) {
			$this->viewHelperVariableContainer->remove('Tx_Fluid_ViewHelpers_FormViewHelper', 'formName');
		}
	}
	
	/* (non-PHPdoc)
	 * @see typo3/sysext/fluid/Classes/ViewHelpers/Tx_Fluid_ViewHelpers_FormViewHelper#renderRequestHashField()
	 */
	protected function renderRequestHashField() {
		return '';
	}
}