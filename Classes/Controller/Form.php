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
 * We need very few changes to some formhandler-mechanisms 
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Controller
 */
class Tx_FormhandlerFluid_Controller_Form extends Tx_Formhandler_Controller_Form
{
	/**
	 * @var Tx_Extbase_MVC_Controller_ControllerContext
	 */
	protected static $controllerContext;
	
	/**
	 * We trick formhandler and put it across we have template-string
	 * @see Tx_FormhandlerFluid_StaticFuncs#readTemplateFile()
	 */
	protected function init()
	{		
		$this->templateFile = "-\n-";
		parent::init();
		
		if (!$this->view instanceof Tx_FormhandlerFluid_View_Form)
		{
			throw new Exception(__CLASS__.' needs an instance of Tx_FormhandlerFluid_View_Form as view!');
		}
	}
	
	/**
	 * Usually the controller sets the controllerContext on the view - in formhandler
	 * we need the view to pull it from here because some views are instantiated from
	 * without this controller (e.g. Tx_FormhandlerFluid_View_FluidMail)
	 * 
	 * @return Tx_Extbase_MVC_Controller_ControllerContext
	 */
	public static function getControllerContext()
	{
		if (!self::$controllerContext)
		{
			/* @var $request Tx_Extbase_MVC_Web_Request */
    		$request = t3lib_div::makeInstance('Tx_Extbase_MVC_Web_Request');
    		$request->setControllerExtensionName('formhandler');
    		$request->setPluginName('pi1');
    		$request->setControllerName('Form');
    		$request->setFormat('html');
    		$request->setBaseURI(t3lib_div::locationHeaderUrl(''));
    		
    		/* @var $uriBuilder Tx_Extbase_MVC_Web_Routing_UriBuilder */
    		$uriBuilder = t3lib_div::makeInstance('Tx_Extbase_MVC_Web_Routing_UriBuilder');
    		$uriBuilder->setRequest($request);
    		$uriBuilder->setNoCache(true);
    		
    		/* @var $arguments Tx_Extbase_MVC_Controller_Arguments */
    		$arguments = t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_Arguments');
    		$arguments->addArgument(
    			t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_Argument', 'fieldNames', 'Array')
    		);
    		$arguments->addArgument(
    			t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_Argument', 'stepForms', 'Array')
    		);
    		
    		self::$controllerContext = t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_ControllerContext');
    		self::$controllerContext->setRequest($request);
    		self::$controllerContext->setUriBuilder($uriBuilder);
    		self::$controllerContext->setArguments($arguments);
		}
		
		return self::$controllerContext;
	}
	
	protected function getStepInformation()
	{
		$this->findCurrentStep();
		
		$this->lastStep = Tx_Formhandler_Session::get('currentStep');
		if(!$this->lastStep) {
			$this->lastStep = 1;
		}
		
		if (!$this->settings['steps'] && $this->settings['form'])
		{
			$this->settings['steps'] = $this->settings['form'];
		}
		if ($this->settings['steps']) {
			$steps = t3lib_div::trimExplode(',', $this->settings['steps']);
		}else{
			$steps = array(0 => null);
		}
		self::getControllerContext()->getArguments()->getArgument('stepForms')->setValue($steps);
		$this->totalSteps = count($steps);
		
		Tx_Formhandler_StaticFuncs::debugMessage('total_steps', $this->totalSteps);
	}
	
	public function getSettings()
	{
		$settings = parent::getSettings();
		if(empty($settings['view'])) {
			$settings['view'] = 'Tx_FormhandlerFluid_View_Form';
		}
		return $settings;
	}
}