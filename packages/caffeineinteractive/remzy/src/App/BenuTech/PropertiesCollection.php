<?php

namespace CaffeineInteractive\Remzy\App\BenuTech;

use CaffeineInteractive\Remzy\App\ApiPropCollect\PropItemsInterface;
use CaffeineInteractive\Remzy\App\BenuTech\BenuTech;

class PropertiesCollection implements PropItemsInterface
{
	protected $properties;
	protected $process_properties;

	public function __construct()
	{
		$this->setProperties(collect([]));	
		$this->process_properties = collect([]);
	}

	public function setProperties($collect)
	{
		$this->properties = $collect;

		return $this;
	}

	public function getProcessProperties()
	{
		return $this->process_properties;
	}

	public function getProperties()
	{
		return $this->properties;
	}

	public function processResult()
	{
		$properties = $this->getProperties();

		if(!$properties)
		{
			return $this;
		}

		foreach ($properties as $row) 
		{
			if(!env("BENUTECG_DEMO_MODE", false))
			{
				BenuTech::createUpdate($row);
			}

			$property = new PropertyCollection;

			$property->setData($row);


			if(!env("BENUTECG_DEMO_MODE", false))
			{
				$property-->loadEmptyFieldsFromModel();	
			}

			$this->process_properties->push([
				'address'          => $property->getAddress(),
				'city'             => $property->getCity(),
				'beds'             => $property->getBeds(),
				'baths'            => $property->getBaths(),
				'sqft'             => $property->getSqft(),
				'lotSize'          => $property->getLotSize(),
				'lastSold'         => $property->getLastSold(),
				'lastSoldAmt'      => $property->getAmountLastSold(),
				'estimatedValue'   => $property->getEstimatedValue(),
				'dwellingType'     => $property->getDwellingType(),
				'apiId'            => $property->getId(),
				'singleUrl'        => $property->getSingleUrl(),
				'allData'          => $property->getAllDAta(),
				'coordinates'      => $property->getArrayCoordinates(),
				'mortgageDt'       => $property->getMortgageDt(),
				'mortgageAmt'      => $property->getMortgageAmt(),
				'isOwnerOccupied'  => $property->getOwnerOccupied(),
				'preMoverScore'    => $property->getPreMoverScore(),
				'preMoverScoreVal' => $property->getPreMoverScoreVal(),
				'lotSize'          => $property->getLotSize(),
				'allDataUrl'       => $property->getAllDataUrl(),
			]);
		}

		return $this;	
	}
}