<?php
class Tx_FormhandlerFluid_Util_Div {

	
	/**
	 * Stolen from Fluid
	 * @see typo3/sysext/fluid/Classes/Parser/TemplateParser#$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static $SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS = '/
		(?P<ArrayPart>                                             # Start submatch
			(?P<Key>[a-zA-Z0-9\-_]+)                               # The keys of the array
			\s*:\s*                                                   # Key|Value delimiter :
			(?:                                                       # Possible value options:
				(?P<QuotedString>                                     # Quoted string
					(?:"(?:\\\"|[^"])*")
					|(?:\'(?:\\\\\'|[^\'])*\')
				)
				|(?P<VariableIdentifier>[a-zA-Z][a-zA-Z0-9\-_.]*)    # variable identifiers have to start with a letter
				|(?P<Number>[0-9.]+)                                  # Number
				|{\s*(?P<Subarray>(?:(?P>ArrayPart)\s*,?\s*)+)\s*}              # Another sub-array
			)                                                         # END possible value options
		)                                                          # End array part submatch
	/x';
	
	/**
	 * Stolen from Fluid
	 * @see typo3/sysext/fluid/Classes/Parser/TemplateParser#recursiveArrayHandler()
	 * 
	 * @param string $arrayText Array text
	 * @return array
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public static function parseFluidArray($arrayText) {
		$arrayText = trim($arrayText, '{}');
		$matches = array();
		$arrayToBuild = array();
		if (preg_match_all(self::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS, $arrayText, $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $singleMatch) {
				$arrayKey = $singleMatch['Key'];
				if (array_key_exists('Number', $singleMatch) && ( !empty($singleMatch['Number']) || $singleMatch['Number'] === '0' ) ) {
					$arrayToBuild[$arrayKey] = floatval($singleMatch['Number']);
				} elseif ( ( array_key_exists('QuotedString', $singleMatch) && !empty($singleMatch['QuotedString']) ) ) {
					$arrayToBuild[$arrayKey] = self::unquoteString($singleMatch['QuotedString']);
				} elseif ( array_key_exists('Subarray', $singleMatch) && !empty($singleMatch['Subarray'])) {
					$arrayToBuild[$arrayKey] = self::parseFluidArray($singleMatch['Subarray']);
				}
			}
		}
		return $arrayToBuild;
	}
	
	/**
	 * Stolen from Fluid
	 * @see typo3/sysext/fluid/Classes/Parser/TemplateParser#unquoteString()
	 *
	 * @param string $quotedValue Value to unquote
	 * @return string Unquoted value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function unquoteString($quotedValue) {
		switch ($quotedValue[0]) {
			case '"':
				$value = str_replace('\"', '"', trim($quotedValue, '"'));
			break;
			case "'":
				$value = str_replace("\'", "'", trim($quotedValue, "'"));
			break;
		}
		return str_replace('\\\\', '\\', $value);
	}
	
	/**
	 * Removes the dots from the array keys
	 * 
	 * @param array $tsArray Array with dots
	 * @return array Without dots in keys
	 */
	public static function convertTsArray($tsArray) {
		$array = array();
		foreach ($tsArray as $key => $value) {
			if (strpos($key, '.') === false && !in_array($key, $array)) {
				$array[$key] = $value;
			}else{
				$array[trim($key, '.')] = self::convertTsArray($value);
			}
		}
		return $array;
	}
}