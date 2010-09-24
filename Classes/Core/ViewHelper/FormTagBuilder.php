<?php
class Tx_FormhandlerFluid_Core_ViewHelper_FormTagBuilder extends Tx_Fluid_Core_ViewHelper_TagBuilder
{
	protected $formViewHelper;
	
	public function setFormViewHelper(Tx_FormhandlerFluid_ViewHelpers_FormViewHelper $form)
	{
		$this->formViewHelper = $form;
	}
	
	/**
	 * Renders and returns the tag
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @api
	 */
	public function render()
	{
		if (empty($this->tagName)) {
			return '';
		}
		$output = '<' . $this->tagName;
		foreach($this->attributes as $attributeName => $attributeValue) {
			$output .= ' ' . $attributeName . '="' . $attributeValue . '"';
		}
		if ($this->hasContent() || $this->forceClosingTag) {
			$output .= '>' . $this->content . '</' . $this->tagName . '>';
		} else {
			$output .= ' />';
		}
		return $output;
	}
}