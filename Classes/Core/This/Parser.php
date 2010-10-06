<?php
class Tx_FormhandlerFluid_Core_This_Parser extends Tx_Fluid_Core_Parser_TemplateParser {

	/**
	 * @var Tx_FormhandlerFluid_This_Document
	 */
	protected $document;
	
	protected function initializeViewHelperAndAddItToStack(Tx_Fluid_Core_Parser_ParsingState $state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree) {
		parent::initializeViewHelperAndAddItToStack($state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree);
		
		$this->document->addElement($state->getNodeFromStack(), $argumentsObjectTree);
	}
	
	protected function objectAccessorHandler(Tx_Fluid_Core_Parser_ParsingState $state, $objectAccessorString, $delimiter, $viewHelperString, $additionalViewHelpersString) {
		if (strpos($objectAccessorString, 'this.') !== false) {
			$node = $this->objectManager->create('Tx_FormhandlerFluid_Core_This_AccessorNode', $objectAccessorString, $this->document);
			$state->getNodeFromStack()->addChildNode($node);
			$objectAccessorString = '';
		}
		parent::objectAccessorHandler($state, $objectAccessorString, $delimiter, $viewHelperString, $additionalViewHelpersString);
	}
	
	protected function reset() {
		parent::reset();
		$this->document = $this->objectManager->create('Tx_FormhandlerFluid_Core_This_Document');
	}
}