<?php
class Tx_FormhandlerFluid_Core_This_Document
{
	protected $elements = array();
	
	/**
	 * @param string $id
	 * @return Tx_FormhandlerFluid_Core_This_Element
	 */
	public function getElementById($id)
	{
		return $this->elements[$id];
	}
	
	public function addElement($element, $arguments)
	{
		if (array_key_exists('id', $arguments))
		{
			$id = $arguments['id']->evaluate();
			$this->elements[$id] = t3lib_div::makeInstance('Tx_FormhandlerFluid_Core_This_Element', $element, $arguments);
		}
	}
}