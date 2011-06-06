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
 * This is a proxy between formhandler controller and fluid view
 *
 * @version $Id$
 * @package	Tx_Formhandler
 * @subpackage View
 * @author Christian Opitz <co@netzelf.de>
 */
class Tx_FormhandlerFluid_View_Form extends Tx_Formhandler_AbstractView {	
	/**
	 * @var Tx_Fluid_View_TemplateView
	 */
	protected $view;
	
	protected $action;
	
	/**
	 * @var Tx_Extbase_MVC_Controller_ControllerContext
	 */
	protected $controllerContext;
	
	protected $settings = array();
	
	/**
	 * Make the 'real' view (this is just a proxy)
	 * @see Tx_Extbase_MVC_Controller_ControllerContext#getControllerContext()
	 */
	protected function initializeView() {
		$this->view = t3lib_div::makeInstance('Tx_FormhandlerFluid_View_TemplateView');
		$this->controllerContext = Tx_FormhandlerFluid_Controller_Form::getControllerContext();
		$this->view->setControllerContext($this->controllerContext);
		
		// Set the view paths (usually done in controller but this view is not
		// always called from a controller (f.i. Tx_FormhandlerFluid_View_FluidMail)
		$this->settings = Tx_Formhandler_Globals::$settings;
		if (!empty($this->settings['templateRoot'])) {
			$path = Tx_Formhandler_StaticFuncs::resolvePath($this->settings['templateRoot']);
			$path = rtrim($path, '\\/').'/';
			$this->view->setTemplateRootPath($path.trim($this->settings['templateDir'], '\\/'));
			$this->view->setLayoutRootPath($path.trim($this->settings['layoutDir'], '\\/'));
			$this->view->setPartialRootPath($path.trim($this->settings['partialDir'], '\\/'));
		}
		elseif ($this->settings['templateFile']) {
			$path = Tx_Formhandler_StaticFuncs::resolvePath($this->settings['templateFile']);
			$this->view->setTemplatePathAndFilename($path);
		} else {
			Tx_Formhandler_StaticFuncs::throwException('no_template_file');
		}
	}
	
	public function getSetting($key, $default) {
		return $this->settings[$key] ? $this->settings[$key] : $default;
	}
	
	/**
	 * Proxy to the view
	 * 
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args) {
		return call_user_func_array(array($this->view, $method), $args);
	}
	
	/* (non-PHPdoc)
	 * @see Classes/View/Tx_FormhandlerFluid_AbstractView#render()
	 */
	public function render($gp, $errors) {				
		$this->view->assign('gp', $gp);
		$this->view->assign('errors', $errors);
		
		$this->processErrors($errors);
		$this->assignDefaults();
		$this->assignFromSetup();
		
		return $this->view->render($this->action);
	}
	
	public function setForm($form) {
		$this->setAction($form);
	}
	
	public function setAction($action) {
		$this->action = $action;
	}
	
	public function setTemplate($templateCode, $templateName, $forceTemplate = FALSE) {
		$parts = explode('_');
		$this->setAction(strtolower($parts[1]));
	}
	
	public function hasTemplate() {
		return $this->view->hasTemplate($this->action);
	}
	
	/**
	 * Translates the formhandler-error-array to extbase propertyErrors and
	 * sets them in the request (to output them with form.errors-helper)
	 * 
	 * @param array $errors
	 * @todo Think about a way to pass translated messages for each validator
	 */
	protected function processErrors($errors) {
		$extBaseErrors = array();
		
		foreach ((array) $errors as $field => $validators) {
			/* @var $propertyError Tx_Extbase_Validation_PropertyError */
			$propertyError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', $field);
			$propertyErrors = array();
			foreach ((array) $validators as $validator) {
				array_push($propertyErrors, t3lib_div::makeInstance('Tx_Extbase_Error_Error', '', $validator));
			}
			$propertyError->addErrors($propertyErrors);
			array_push($extBaseErrors, $propertyError);
		}
		
		$this->controllerContext->getRequest()->setErrors($extBaseErrors);
	}
	
	/**
	 * Assigns data passed to the view. The setup-keys will be the var-
	 * names in the view. You can use all typoScript-objects and additional there
	 * are 5 objects reserved to generate arrays:
	 * 
	 * - array: Will be an array of all contained properties
	 * - fluidArray: Will parse the string in .source to an array like in fluid
	 * - json: Will json_decode the string in .source
	 * - parseStr: Will parse .source like mb_parse_str does (query-strings)
	 * - list: Will explode .source by a configurable .delimiter (default is ",")
	 * 
	 * Source can contain ts-objects (note that for "array" they are not rendered before)	 * 
	 * 
	 * <code title="Simple vars">
	 * plugin.Tx_Formhandler.view.assign {
	 * 	name = John
	 *  age = TEXT
	 *  age.value = 35
	 * }
	 * # In fluid views this will be available in the var name:
	 * # <fh:translate key="hello"/> {name}
	 * </code>
	 * 
	 * <code title="Assign arrays">
	 * plugin.Tx_Formhandler.view.assign {
	 * 	items = array
	 *  items {
	 *    first = Please select
	 *    second = Hello
	 *    3 = World
	 *  }
	 * }
	 * # Then in fluid view:
	 * # <fh:form.select property="items" options="{items}"/>
	 * </code>
	 * 
	 * <code title="Assign fluid arrays">
	 * temp.items = CONTENT
	 * temp.items {
	 *   table = tx_myext_items
	 *   select.pidInList = 17
	 *   renderObj = TEXT
	 *   renderObj {
	 *     value = ,{field:uid}:"{field:title}"
	 *     insertData = 1
	 *   }
	 *   wrap = {0:"--"|}
	 * }
	 * plugin.Tx_Formhandler.view.assign {
	 * 	items = FLUID_ARRAY
	 *  items.value < temp.items
	 * }
	 * # Then in fluid view:
	 * # <fh:form.select property="items" options="{items}"/>
	 * </code>
	 */
	protected function assignFromSetup() {
		if (!is_array($this->settings['view.']['assign.'])) {
			return;
		}
		$assign = $this->settings['view.']['assign.'];
		foreach ($assign as $var => $conf) {
			if (strpos($var, '.') === false) {
				if (isset($assign[$var.'.']['source.']) && strtolower($conf) != 'array') {
					$source = Tx_Formhandler_Globals::$cObj->cObjGetSingle($assign[$var.'.']['source'], (array) $assign[$var.'.']['source.']);
				}
				
    			switch (strtolower(str_replace('_', '', $conf))) {
    				case 'array':
    					$data = Tx_FormhandlerFluid_Util_Div::convertTsArray( (array) $assign[$var.'.']);
    					break;
    				case 'parsestr':
    					mb_parse_str($source, $data);
    					break;
    				case 'list':					
    					$delimiter = $assign[$var.'.']['delimiter'] ? $assign[$var.'.']['delimiter'] : ',';
    					$data = t3lib_div::trimExplode($delimiter, $source);
    					break;
    				case 'json':
    					$data = json_decode($source, true);
    					break;
    				case 'fluidarray':
    					$data = Tx_FormhandlerFluid_Util_Div::parseFluidArray($source);
    					break;
    				default:
    					$data = Tx_Formhandler_Globals::$cObj->cObjGetSingle($conf, $assign[$var.'.']);
    			}
    			$this->view->assign($var, $data);
			}
		}
	}
	
	
	
	/**
	 * Assign all vars that the view needs
	 */
	protected function assignDefaults() {
		$this->view->assignMultiple(
			array(
				'timestamp'			=> time(),
				'submission_date'	=> date('d.m.Y H:i:s', time()),
				'randomId'			=> Tx_Formhandler_Globals::$randomID,
				'fieldNamePrefix'	=> Tx_Formhandler_Globals::$formValuesPrefix,
				'ip'				=> t3lib_div::getIndpEnv('REMOTE_ADDR'),
				'pid'				=> $GLOBALS['TSFE']->id,
				'currentStep'		=> Tx_FormhandlerFluid_Util_Div::getSessionValue('currentStep'),
				'totalSteps'		=> Tx_FormhandlerFluid_Util_Div::getSessionValue('totalSteps'),
				'lastStep'			=> Tx_FormhandlerFluid_Util_Div::getSessionValue('lastStep'),
				// f:url(absolute:1) does not work correct :(
				'baseUrl'			=> t3lib_div::locationHeaderUrl('')
			)
		);
		
		if ($this->gp['generated_authCode']) {
			$this->view->assign('authCode', $this->gp['generated_authCode']);
		}
		
		/*
		Stepbar currently removed - probably this should move in a partial
		$markers['###step_bar###'] = $this->createStepBar(
			Tx_FormhandlerFluid_Session::get('currentStep'),
			Tx_FormhandlerFluid_Session::get('totalSteps'),
			$prevName,
			$nextName
		);
		*/
		
		/*
		Not yet realized
		$this->fillCaptchaMarkers($markers);
		$this->fillFEUserMarkers($markers);
		$this->fillFileMarkers($markers);
		*/
	}
}