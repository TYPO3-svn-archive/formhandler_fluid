<?php
class Tx_FormhandlerFluid_Core_This_Element {

	/**
	 * @var Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode
	 */
	protected $element;
	
	/**
	 * @var array
	 */
	protected $arguments;

	/**
     * @param $element the $element to set
     * @return Tx_FormhandlerFluid_Core_This_Element
     */
    public function __construct($element, $arguments) {
        $this->element = $element;
        $this->arguments = $arguments;
    }
    
    /**
     * @param string $argument
     * @return mixed|NULL
     */
    public function getArgument($argument) {
    	if ($this->hasArgument($argument)) {
    		return $this->arguments[$argument];
    	}
    	return NULL;
    }
    
    public function hasArgument($argument) {
    	return array_key_exists($argument, $this->arguments);
    }
    
    /**
     * Evaluates if the method is a "getter"-method means that it's name
     * is getVar, checks if an argument with that name exists and returns
     * it when found or NULL if not (first checks var and then Var)
     * 
     * @param string $method
     * @param array $arguments
     * @return mixed|NULL
     */
    public function __call($method, $arguments) {
    	if (strpos($method, 'get') !== false && $method !== 'get') {
    		$ucArgument = substr($method, 3);
    		$lcArgument = strtolower($ucArgument[0]).substr($ucArgument, 1);
    		
    		if ($this->hasArgument($lcArgument)) {
    			return $this->getArgument($lcArgument);
    		}
    		if ($this->hasArgument($ucArgument)) {
    			return $this->getArgument($ucArgument);
    		}
    	}
    	return null;
    }
	
    public function __get($argument) {
    	return  $this->getArgument($argument);
    }
    
    public function __toString() {
    	//return $this->element->evaluateChildNodes();
    }
}