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
class Tx_FormhandlerFluid_Core_ViewHelper_FormTagBuilder extends Tx_Fluid_Core_ViewHelper_TagBuilder {

	/**
	 * @var Tx_FormhandlerFluid_ViewHelpers_FormViewHelper
	 */
	protected $formViewHelper;
	
	public function setFormViewHelper(Tx_FormhandlerFluid_ViewHelpers_FormViewHelper $form) {
		$this->formViewHelper = $form;
	}
	
	/**
	 * Renders and returns the tag - parses the label argument and when
	 * the formViewHelper is on readOnly just the values are returned
	 */
	public function render() {
		if ($this->formViewHelper->isReadOnly()) {
			return $this->renderReadOnly();
		}
		
		if ($this->attributes['label']) {
			$label = '<label';
			if ($this->attributes['id']) {
				$label .= ' for="'.$this->attributes['id'].'"';
			}
			$label .= '>'.$this->attributes['label'].'</label>';
		}
		if ($this->attributes['labelPlacement'] == 'left') {
			return $label.parent::render();
		}
		unset($this->attributes['labelPlacement'], $this->attributes['label']);
		
		return parent::render().$label;
	}
	
	/**
	 * Renders only the values
	 * 
	 * @return string The value or concatenated values of this tag
	 */
	public function renderReadOnly() {
		if ($this->tagName == 'textarea') {
			return $this->content;
		}
		if ($this->tagName == 'input') {
			$value = (string) $this->attributes['value'];
			switch ($this->attributes['type']) {
				case 'submit':
				case 'hidden':
					return '';
				case 'checkbox':
				case 'radio':
					if ($this->attributes['checked']) {
						$value = $this->attributes['label'] ? $this->attributes['label'] : $value;
					}else{
						return '';
					}
				default:
					return $value;
			}
		}
		if ($this->tagName == 'select') {
			preg_match_all('/selected="selected"\>([^\<]+)/', $this->content, $selected);
			return implode(', ', $selected[1]);
		}
	}
}