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
class Tx_FormhandlerFluid_View_Form extends Tx_Formhandler_AbstractView
{	
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
	protected function initializeView()
	{
		$this->view = t3lib_div::makeInstance('Tx_FormhandlerFluid_View_TemplateView');
		$this->controllerContext = Tx_FormhandlerFluid_Controller_Form::getControllerContext();
		$this->view->setControllerContext($this->controllerContext);
		
		// Set the view paths (usually done in controller but this view is not
		// alway called from an controller (f.i. Tx_FormhandlerFluid_View_FluidMail)
		$this->settings = Tx_Formhandler_Globals::$settings;
		if ($this->settings['templateRoot'])
		{
			$path = Tx_Formhandler_StaticFuncs::resolvePath($this->settings['templateRoot']);
			$path = rtrim($path, '\\/').'/';
			$this->view->setTemplateRootPath($path.$this->getSetting('templatePath', 'Private/Templates'));
			$this->view->setLayoutRootPath($path.$this->getSetting('layoutPath', 'Private/Layouts'));
			$this->view->setPartialRootPath($path.$this->getSetting('partialsPath', 'Private/Templates'));
		}
		elseif ($this->settings['templateFile'])
		{
			$path = Tx_Formhandler_StaticFuncs::resolvePath($this->settings['templateFile']);
			$this->view->setTemplatePathAndFilename($path);
		} else {
			Tx_Formhandler_StaticFuncs::throwException('no_template_file');
		}
	}
	
	public function getSetting($key, $default)
	{
		return $this->settings[$key] ? $this->settings[$key] : $default;
	}
	
	/**
	 * Proxy to the view
	 * 
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->view, $method), $args);
	}
	
	/* (non-PHPdoc)
	 * @see Classes/View/Tx_FormhandlerFluid_AbstractView#render()
	 */
	public function render($gp, $errors)
	{				
		$this->view->assign('gp', $gp);
		$this->view->assign('errors', $errors);
		
		$this->processErrors($errors);
		$this->assignDefaults();
		
		return $this->view->render($this->action);
	}
	
	public function setForm($form)
	{
		$this->setAction($form);
	}
	
	public function setAction($action)
	{
		$this->action = $action;
	}
	
	public function setTemplate($templateCode, $templateName, $forceTemplate = FALSE)
	{
		$parts = explode('_');
		$this->setAction(strtolower($parts[1]));
	}
	
	public function hasTemplate()
	{
		return $this->view->hasTemplate($this->action);
	}
	
	/**
	 * Translates the formhandler-error-array to extbase propertyErrors and
	 * sets them in the request (to output them with form.errors-helper)
	 * 
	 * @param array $errors
	 * @todo Think about a way to pass translated messages for each validator
	 */
	protected function processErrors($errors)
	{
		$extBaseErrors = array();
		
		foreach ((array) $errors as $field => $validators)
		{
			/* @var $propertyError Tx_Extbase_Validation_PropertyError */
			$propertyError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', $field);
			$propertyErrors = array();
			foreach ((array) $validators as $validator)
			{
				array_push($propertyErrors, t3lib_div::makeInstance('Tx_Extbase_Error_Error', '', $validator));
			}
			$propertyError->addErrors($propertyErrors);
			array_push($extBaseErrors, $propertyError);
		}
		
		$this->controllerContext->getRequest()->setErrors($extBaseErrors);
	}
	
	/**
	 * Assign all vars that the view needs
	 */
	protected function assignDefaults()
	{
		$this->view->assignMultiple(
			array(
				'timestamp'			=> time(),
				'submission_date'	=> date('d.m.Y H:i:s', time()),
				'randomId'			=> Tx_Formhandler_Globals::$randomID,
				'fieldNamePrefix'	=> Tx_Formhandler_Globals::$formValuesPrefix,
				'ip'				=> t3lib_div::getIndpEnv('REMOTE_ADDR'),
				'pid'				=> $GLOBALS['TSFE']->id,
				'currentStep'		=> Tx_Formhandler_Session::get('currentStep'),
				'totalSteps'		=> Tx_Formhandler_Session::get('totalSteps'),
				'lastStep'			=> Tx_Formhandler_Session::get('lastStep'),
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
	
	/**
	 * Pass language files to the fh:translate-view-helper 
	 * 
	 * @param array $langFiles
	 */
	public function setLangFiles(array $langFiles)
	{
		Tx_FormhandlerFluid_ViewHelpers_TranslateViewHelper::setLangFiles($langFiles);
	}
}