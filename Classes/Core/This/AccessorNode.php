<?php
class Tx_FormhandlerFluid_Core_This_AccessorNode extends Tx_Fluid_Core_Parser_SyntaxTree_AbstractNode
{
	/**
	 * Object path which will be called. Is a list like "post.name.email"
	 * @var Tx_FormhandlerFluid_Core_This_Document
	 */
	protected $document;

	/**
	 * Object path which will be called. Is a list like "post.name.email"
	 * @var string
	 */
	protected $objectPath;
	
	/**
	 * Constructor. Takes an object path as input.
	 *
	 * The first part of the object path has to be a variable in the
	 * TemplateVariableContainer.
	 *
	 * @param string $objectPath An Object Path, like object1.object2.object3
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($objectPath, $document)
	{
		$this->objectPath = $objectPath;
		$this->document = $document;
	}

	/**
	 * Evaluate this node and return the correct object.
	 *
	 * Handles each part (denoted by .) in $this->objectPath in the following order:
	 * - call appropriate getter
	 * - call public property, if exists
	 * - fail
	 *
	 * The first part of the object path has to be a variable in the
	 * TemplateVariableContainer.
	 *
	 * @return object The evaluated object, can be any object type.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @todo Depending on the context, either fail or not!!!
	 */
	public function evaluate()
	{
		$objectPath = explode('.', $this->objectPath);
		//shift "this"
		array_shift($objectPath);
		
		$id = array_shift($objectPath);
		$argument = array_shift($objectPath);
		$element = $this->document->getElementById($id);
		
		if ($element !== null && $element->hasArgument($argument))
		{
			$this->addChildNode($element->getArgument($argument));
			$object = $this->evaluateChildNodes();
    		
			if (count($objectPath)){
    			return Tx_Extbase_Reflection_ObjectAccess::getPropertyPath($object, implode('.', $objectPath));
    		}else{
    			return $object;
    		}
		}
		return null;
	}
}