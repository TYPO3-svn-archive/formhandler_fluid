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
 * REQUIRES DOM and quite valid markup to work
 * 
 * Strips all HTML and applies basic formats (email compatible) - means it does
 * text basic formatting (h1-h6,a,b,i,u,strong,em,big,sup,legend), prefixes block
 * elements with default margin (p, h1-h6, ul...) with 2 newlines and such without
 * default margin with single newlines. When those are nesting those with margin
 * succeed (means max. 2 newlines between blocks). Furthermore it parses ULs and 
 * OLs and even respects nesting:
 * 
 * = Examples =
 * 
 * <code title="Example showing main capabilities of this helper">
 * <fh:format.html2txt>
 *	<div><a href="sth.html">This is a test</a><h1>Heading</h1></div>
 * 	<h2>Subheading</h2>
 * 	<p>Lorem <u>ipsum</u> dolor <i>sit</i> amet <b>consectetuer</b> Vestibulum Aliquam ut <br />magna tempor.</p>
 * 	Text
 * 	<ul>
 * 		<li>Regular list item</li>
 * 		<li>
 * 			Sublist
 * 			<ul><li>Sublist item</li>
 * 				<li>Sublist item</li>
 * 			</ul>
 * 		</li>
 * 		<li>Regular list item</li>
 * 	</ul>
 * 	<ol>
 * 		<li>Regular list item</li>
 * 		<li>
 * 			<ol>
 * 				<li>Sublist item</li><li>Sublist item</li>
 * 			</ol>
 * 		</li>
 * 		<li>Text prior to sublist
 * 			<ol><li>Sublist item</li>
 * 				<li>Sublist item</li>
 * 			</ol>
 * 		</li>
 * 		<li>Regular list item</li>
 * 		<li>Regular list item</li>
 * 	</ol>
 * 	<dl><dt>Topic</dt><dd>Description</dd><dt>Topic</dt><dd>Description</dd></dl>
 * </fh:format.html2txt>
 * </code>
 * 
 * <output>
 * [This is a test]<sth.html>
 * 
 * _*Heading*_
 * 
 * _*Subheading*_
 * 
 * Lorem _ipsum_ dolor /sit/ amet *consectetuer* Vestibulum Aliquam ut magna tempor.
 * 
 * Text
 * 
 * -  Regular list item
 * -  Sublist
 *    -  Sublist item
 *    -  Sublist item
 * -  Regular list item
 * 
 * 1. Regular list item
 * 2. 1. Sublist item
 *    2. Sublist item
 * 3. Text prior to sublist
 *    1. Sublist item
 *    2. Sublist item
 * 4. Regular list item
 * 5. Regular list item
 * 
 * Topic
 *    Description
 * Topic
 *    Description
 * </output>
 *
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	View_Fluid_ViewHelper
 */
class Tx_FormhandlerFluid_ViewHelpers_Format_Html2txtViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	protected $tab = '~#~TAB~#~';
	protected $bullet = '~#~BULLET~#~';
	
	/**
	 * Format the arguments with the given printf format string.
	 *
	 * @return string The formatted value
	 */
	public function render() {
		$format = $this->renderChildren();
		return $this->convertHtmlToText($format);
	}
	
    /**
     * Do the conversion
     * 
     * @param string $html
     * @return string The rendered text
     */
    protected function convertHtmlToText($html) {
    	$html = $this->fixNewlines($html);
    	
    	//DOM messes utf8
    	$html = utf8_decode($html);
    	$doc = new DOMDocument();
    	if (!$doc->loadHTML('<span>'.$html.'</span>')) {
    		throw new Tx_Fluid_Core_ViewHelper_Exception('Could not load HTML - badly formed?');
    	}
    
    	$output = $this->iterateOverNode($doc);
    
    	$output = preg_replace("/[ \t]*\n[ \t]*/im", "\n", $output);
    	
    	//return $output;
    	return str_replace(
    		array(
    			$this->tab,
    			$this->bullet,
    		),array(
    			'   ',
    			'-  '
    		),
    		trim($output));
    }
    
    /**
     * Unify newlines; in particular, \r\n becomes \n, and
     * then \r becomes \n. This means that all newlines (Unix, Windows, Mac)
     * all become \ns.
     *
     * @param text text with any number of \r, \r\n and \n combinations
     * @return the fixed text
     */
    protected function fixNewlines($text) {
    	// replace \r\n to \n
    	$text = str_replace("\r\n", "\n", $text);
    	// remove \rs
    	$text = str_replace("\r", "\n", $text);
    
    	return $text;
    }
    
	/**
	 * Iterates over all nodes, organizes breaks (vertical margins) and calls
	 * the postProcessTag for each tag that is not DOMText
	 * 
	 * @param DOMElement|DOMText $node
	 * @param string $prevOutput
	 * @return string
	 */
	protected function iterateOverNode($node, $prevOutput = '') {
    	if ($node instanceof DOMText) {
    		return $prevOutput.preg_replace("/\\s+/im", " ", $node->wholeText);
    	}
    	if ($node instanceof DOMDocumentType) {
    		// ignore
    		return "";
    	}
    
    	$name = strtolower($node->nodeName);
    	$this->preProcessTag($node);
    	
    	if (in_array($name, array('style', 'head', 'title', 'meta', 'script', 'object'))) {
    		return '';
    	}
    
    	$output = '';
    	for ($i = 0; $i < $node->childNodes->length; $i++) {
    		$output = $this->iterateOverNode($node->childNodes->item($i), $output);
    	}
    	
    	$before = ''; $after ='';
    	if (strlen($output)) {
        	if ($this->isSingleBreakElement($name)) {
        		$prevOutput = rtrim($prevOutput);
        		$before = "\n";
        		
        		$len = strlen($output);
        		$pos = strlen(rtrim($output));
        		$output = ltrim($output);
        		$after .= ($len - $pos == 2) ? "\n\n" : "\n";
        	}
        	elseif ($this->isDoubleBreakElement($name)){
        		$prevOutput = rtrim($prevOutput);
        		$output = ltrim($output);
        		$before = $after = "\n\n";
        	}
    		
        	$this->postProcessTag($output, $before, $after, $name, $node);
        	
        	return $prevOutput.$before.$output.$after;
    	}
    	
		return $prevOutput;
    }
    
    /**
     * This method gets called for each DOMElement BEFORE it's children are rendered 
     * 
     * @param DOMElement $node
     */
    protected function preProcessTag($node) {
    	switch ($node->nodeName) {
    		case 'ul':
    		case 'ol':
    			if ($node->parentNode->nodeName == 'li') {
    				$node->setAttribute('rel', intval($node->parentNode->getAttribute('rel')) + 1);
    			}else{
    				$node->setAttribute('rel', 0);
    			}
    			$node->setAttribute('top', 0);
    		break;
    		case 'li':
    			$rel = intval($node->parentNode->getAttribute('rel'));
    			$node->setAttribute('rel', $rel);
    			if ($node->parentNode->nodeName == 'ol') {
    				$i = intval($node->parentNode->getAttribute('top')) + 1;
    				$node->parentNode->setAttribute('top', $i);
    				$node->setAttribute('left', str_repeat($this->tab, $rel).$i.'. ');
    			}
    			else {
        			$node->setAttribute('left', str_repeat($this->tab, $rel).$this->bullet);
    			}
    		break;
    	}
    }
    
    /**
     * This method gets called for each DOMElement AFTER it's children are rendered
     * Used to decorate tags according to theyr name and influence it's margin
     * 
     * @param string $output
     * @param string $before
     * @param string $after
     * @param string $name
     * @param DOMElement $node
     */
    protected function postProcessTag(&$output, &$before, &$after, $name, $node) {
    	switch ($name) {
    		case 'a':
    			$href = $node->getAttribute("href");
    			if ($href != null) {
    				if ($href == $output) {
    					$output = '<'.$output.'>';
    				} else {
    					$output = '['.$output.']<'.$href.'>';
    				}
    			}
			break;
    		case 'h1': case 'h2': case 'h3':
    			$output = '_*'.$output.'*_';
    		break;
    		case 'h4': case 'h5': case 'h6':
    		case 'u':
    			$output = '_'.$output.'_';
    		break;
    		case 'i': case 'em':
    			$output = '/'.$output.'/';
    		break;
    		case 'b': case 'strong':
    		case 'legend':
    			$output = '*'.$output.'*';
    		break;
    		case 'big': case 'sup':
    			$output = strtoupper($output);
    		break;
    		case 'ol':
    		case 'ul':
    			if ($node->parentNode->nodeName == 'li') {
    				$before = "\n";
    			}
    		break;
    		case 'li':
				if ($node->getAttribute('left')) {
					$parts = explode("\n", $output);
					if (count($parts) > 1) {
    					// Ok - this is a li directly following an nested ul or ul
    					// we have to strip out the previous indention
    					// Probably it's better to avoid that in preProcessing but I
    					// did not get it :(
						$parts[0] = str_replace($this->tab, '', $parts[0]);
						$output = implode("\n",$parts);
					}
				}
    			$output = $node->getAttribute('left').$output;
    		break;
    		case 'dd':
    			$output = $this->tab.$output;
    		break;
    	}
    }
    
    /**
     * If this tag is a block element without vertical margin
     * 
     * @param string $name The tag name
     * @return boolean
     */
    protected function isSingleBreakElement($name) {
    	return in_array($name, array(
    		'address',
    		'blockquote',
    		'center',
    		'dir',
    		'div',
    		'fieldset',
    		'form',
    		'legend',
    		'isindex',
        	'menu',
        	'noscript',
        	'dd', 'dt', 'li',
        	'pre',
        	'tr',
    		'br'
    	));
    }
	
    /**
     * If this is a block element that requires vertical margin
     * 
     * @param string $name
     * @return boolean
     */
    protected function isDoubleBreakElement($name) {
    	return in_array($name, array(
    		'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        	'ol', 'ul', 'dl',
        	'p',
    	));
    }
}
?>