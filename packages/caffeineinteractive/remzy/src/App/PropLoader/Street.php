<?php

namespace CaffeineInteractive\Remzy\App\PropLoader;

use CaffeineInteractive\Remzy\App\PropLoader\Street;

class Street
{
	protected $street;

	protected $prefix;

	protected $unitNum;

	protected $streetTypes;

	public function __construct($street, $prefix = null, $unitNum = null)
	{
		$this->street = $street;
		$this->prefix = $prefix;
		$this->unitNum = $unitNum;
		$this->streetTypes = null;

		$this->setStreetType();
	}

	public function getUnitNum()
	{
		return $this->unitNum;
	}

	public function getPrefix()
	{
		return $this->prefix;
	}

	public function getFullStreetAddress()
	{
		$arr = [];
		$arr[] = $this->street;

		if($this->prefix)
		{
			$arr[] = $this->prefix;
		}

		if($this->unitNum)
		{
			$arr[] = $this->unitNum;
		}		

		return implode(' ', $arr);
	}

	public function hasSteetType()
	{
		return ($this->streetTypes ? true : false);
	}

	public function setStreetType()
	{
		$suffixes = PropLoad::addrSuffixes(true);
		$found = false;

		if(!$this->street || trim($this->street) == "")
		{
			return false;
		}

		foreach ($suffixes  as $key => $lbl) 
		{	
			$streetParts = explode(' ', trim($this->street));
			$lastWord = array_pop($streetParts);
			
			if(strtolower($lastWord) == strtolower($lbl))
			{
				$found = true;
			}elseif(strtolower($lastWord) == strtolower($key))
			{
				$found = true;
			}

			if($found)
			{
				$this->streetTypes[0] = strtolower($lbl);
				$this->streetTypes[1] = strtolower($key);
				
				return $found;
			}
		}

		return null;
	}

	public function streetInTypes()
	{
		if(!$this->hasSteetType())
		{
			return null;
		}

		$streetParts = explode(' ', trim($this->street));
		unset($streetParts[count($streetParts) - 1]);
		$streetNoLast = implode(' ', $streetParts);

		return [
			$streetNoLast . ' ' . $this->streetTypes[0],
			$streetNoLast . ' ' . $this->streetTypes[1],
		];
	}

	public static function make($street, $prefix = null, $unitNum = null)
	{
		$obj = new Street(trim($street), trim($prefix), $unitNum);

		return $obj;
	}
}