<?php

namespace CaffeineInteractive\Remzy\App\PropLoader;

use CaffeineInteractive\Remzy\App\PropLoader\PropLoad;
use CaffeineInteractive\Remzy\App\PropLoader\Street;

class AddressParts
{
	protected $completeAddr;

	protected $zip;

	protected $state;

	/**
	 * From Street address upto unit number if there is.
	 */
	protected $line1;

	/**
	 * Street address without unit number and unit prefix.
	 */
	protected $street;

	protected $city;

	/**
	 * Unit number without the prefix or number sign if exists.
	 */
	protected $unitNum;

	/**
	 * Unit number with unit prefix.
	 * 
	 * @var string|null
	 */
	protected $unit;

	/**
	 * Unit number prefix like apartment, apt, unit, un and etc..
	 */
	protected $unitPrefix;

	protected $streetPropType;

	public function __construct($address, $zip = null, $unit = null)
	{
		$this->zip = $zip;
		$this->unit = ($unit && trim($unit) != '' ? trim($unit) : null);

		$this->setCompleteAddr($address);
	}

	public function setCompleteAddr($str)
	{
		$this->completeAddr = $str;

		$this->init();

		return $this;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function getZip()
	{
		return $this->zip;
	}

	public function getState()
	{
		return $this->state;
	}

	public function getPossibleSearches()
	{
		$combinations = [];
		$foundCombi = null;

		if(!$this->unitPrefix && $this->unitNum)
		{
			// Loop through all
			foreach ($this->unitPrefixes() as $key => $value) {
				$combinations[] = Street::make($this->street, $key, $this->unitNum);
				$combinations[] = Street::make($this->street, $value, $this->unitNum);
			}
		}elseif($this->unitPrefix && $this->unitNum)
		{
			$foundCombi = Street::make($this->street, $this->unitPrefix, $this->unitNum);

		}else{
			$foundCombi = Street::make($this->street);
		}

		if($foundCombi && $foundCombi->hasSteetType())
		{
			$streets = $foundCombi->streetInTypes();
			$combinations[] = Street::make($streets[0], $foundCombi->getPrefix(), $foundCombi->getUnitNum());
			$combinations[] = Street::make($streets[1], $foundCombi->getPrefix(), $foundCombi->getUnitNum());
		}elseif($foundCombi)
		{
			$combinations[] = $foundCombi;
		}


		return $combinations;
	}

	public function getUnitNum()
	{
		return $this->unitNum;
	}

	public function toArrayPossibleSearches($streetOnly = false)
	{
		$arr = [];

		foreach ($this->getPossibleSearches() as $address1) 
		{
			if(!$streetOnly)
			{
				// Append city, state zip
				$arr[] = $this->streetToFull($address1->getFullStreetAddress());	
			}else
			{
				// No city, state zip
				$arr[] = $address1->getFullStreetAddress();
			}
			
		}

		return $arr;
	}

	protected function init()
	{
		$exp = explode(',', $this->completeAddr);
		$zip = null;
		$lastLine = (isset($exp[2]) ? $exp[2] : null);
		$lastLineExp = ($lastLine ? explode(' ', trim($lastLine)) : null);

		/**
		 * Extract zip code
		 * 
		 * When ZIP field is missing try to extract from address state.
		 */
		if(isset($exp[2]) && $exp[2] && !$this->zip)
		{
		    preg_match("/\b[A-Za-z]{2}\s+\d{5}(-\d{4})?\b/", $exp[2], $matches);
		    $zip = (isset($matches[0]) ? trim(preg_replace("/[^0-9,.]/", "", $matches[0])) : null);
			$this->zip = $zip;
		}

		$this->state = $this->stateToCode( (isset($lastLineExp[0]) ? trim($lastLineExp[0]) : null) );
		$this->line1 = (isset($exp[0]) ? trim($exp[0]) : null);
		$this->city = (isset($exp[1]) ? trim($exp[1]) : null);

		$this->setUnitParts();
		
		return $this;
	}

	public function stateToCode($state)
	{
       	/**
       	 * If state is inputed as name then convert it to abbr.
       	 */
       	foreach (config('states') as $stateCd => $stateName) 
       	{
       		if($state == $stateName)
       		{
       			$state = $stateCd;
       		}
       	}	

       	return $state;	
	}

	public function streetToFull($street)
	{
		return $street . ', ' . $this->getCity() . ', ' . $this->getState() . ' ' . $this->getZip();
	}

	/**
	 * Gets the formated street.
	 * Street + unit number.
	 */
	public function getFormatedStreet()
	{
		$streetParts = [];

		if($this->street)
		{
			$streetParts[] = $this->street;
		}

		if($this->unitPrefix)
		{
			$streetParts[] = $this->unitPrefix;
		}

		if($this->unitNum)
		{
			$streetParts[] = $this->unitNum;
		}

		return implode(' ', $streetParts);
	}

	public function getFormatedAddress()
	{
		$addrParts = [
			$this->getFormatedStreet()
		];

		if($this->city)
		{
			$addrParts[] = $this->city;
		}

		$stateZip = [];

		if($this->state)
		{
			$stateZip[] = $this->state;
		}	

		if($this->zip)
		{
			$stateZip[] = $this->zip;
		}

		$addrParts[] = implode(' ', $stateZip);

		return implode(', ', $addrParts);
	}

	protected function unitPrefixes()
	{
		return [
			'APT' => 'Apartment',
			'Bldg' => 'Building',
			'FL' => 'Floor',
			'STE' => 'Suite',
			'UNIT' => 'Unit',
			'RM' => 'Room',
			'DEPT' => 'Department'
		];
	}

	/**
	 * Check if unit number field is enabled.
	 * 
	 * @return boolean
	 */
	protected function isUnitNumEnabled()
	{
		/**
		 * Flag to enable or disable the unit number field.
		 */		
		$enabled = config('ciremzy.address_search.enable_unit_num', true);

		return ($enabled);
	}

	/**
	 * Sets the unit parts.
	 */
	protected function setUnitParts()
	{
		$parts = explode(' ', trim($this->line1));
		// When unit field is enabled then skip extracting the unit number from address field.
		if($this->isUnitNumEnabled())
		{ 
			$unitParts = explode(' ', $this->unit);
			$unit = end( $unitParts ); // Get the last part of array.
			
			$hashExp = explode('#', $unit);
			
			if(count($hashExp) > 1)
			{
				$unitParts = $hashExp;
			}

			$unitNum = end( $unitParts );

			if(count($unitParts) > 1){
				array_pop($unitParts); // remove the unit number.
			}
	
		}else
		{
			$unitNum = str_replace('#', '', array_pop($parts));
		}

		if(1 === preg_match('~[0-9]~', $unitNum))
		{
			$this->unitNum = $unitNum;
		}else{
			// Append again the part that was removed.
			// Unit number should not be present
			$parts[] = $unitNum;
		}

		if(!$this->isUnitNumEnabled())
		{
			$unitPrefix = strtolower(str_replace('.', '', trim(array_pop($parts))));
		}else{
			$unitPrefix = strtolower(str_replace('.', '',implode(' ', $unitParts)));
		}

		$unitPrefixes = $this->unitPrefixes();
		$foundUnitPref = null;

		//Check if any of prefixes matches
		foreach ($unitPrefixes as $key => $value)
		{
			$abbr = strtolower($key);
			$name = strtolower($value);

			if($unitPrefix == $abbr)
			{
				$foundUnitPref = $abbr;
				break;
			}elseif($unitPrefix == $name)
			{
				$foundUnitPref = $abbr;
				break;
			}
		}

		if($foundUnitPref)
		{
			$this->unitPrefix = $foundUnitPref;

		}else{
			// Append again the part that was removed.
			// When not found it mean the variable $unitPrefix is holding a non prefix.
			$parts[] = $unitPrefix;
		}

		// Set street property type once street has been sanitize.
		$this->setStreetPropType($parts);

		return $this;
	}

	/**
	 * Sets the street property type.
	 * Arrange the street address proeprty type string
	 * Street property type can be Blvd., Ave., Road, Avenue, etc.
	 * 
	 * @return     self
	 */
	protected function setStreetPropType($parts)
	{
		$street = implode(' ', $parts);
       	$addrExp = explode(' ', $street);
       	$addrLastWord = $addrExp[count($addrExp) - 1];
       	$suffixes = PropLoad::addrSuffixes(true);

       	// Check last string has number
       	if(1 === preg_match('~[0-9]~', $addrExp[ count($addrExp) - 1 ]))
       	{
       		$addrLastWord = $addrExp[count($addrExp) - 2];
       		array_pop($addrExp);
       	}

       	/**
       	 * Loop through suffixes and replaces short suffixes.
       	 */
       	foreach ($suffixes as $key => $suf) {
    		$addrLastWord = str_ireplace($key, $suf, $addrLastWord);
       	}

       	// Set the last array string to formatted suffix.
   		$addrExp[count($addrExp) - 1] = $addrLastWord;

   		/**
   		 * Arrange again the street into single string.
   		 *
   		 * @var        callable
   		 */
       	$address1 = PropLoad::centerAddrSuffixes(implode(' ', $addrExp));

       	$this->street = ($this->unitNum ? str_replace(' '. $this->unitNum, '', $address1) : $address1 );

       	return $this;
	}

}