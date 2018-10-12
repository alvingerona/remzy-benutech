<?php

namespace CaffeineInteractive\Remzy\App\BenuTech;

use CaffeineInteractive\Remzy\App\PropLoader\PropLoaderInterface;
use CaffeineInteractive\Remzy\App\PropLoader\PropLoader;
use CaffeineInteractive\Remzy\App\Models\Property;
use CaffeineInteractive\Remzy\App\BenuTech\Api;
use CaffeineInteractive\Remzy\App\BenuTech\Query;
use CaffeineInteractive\Remzy\App\BenuTech\ResponseHandler;
use CaffeineInteractive\Remzy\App\BenuTech\PropertiesCollection;

class BenuTech extends PropLoader implements PropLoaderInterface
{    
    protected $query;

    public function collectionClass()
    {
    	return new PropertiesCollection();	
    }

	public function send()
	{
		try {
            $res = new Api;
            $data = $res->globalSearch($this->getPayload(),$this->query->getPageSize(), $this->query->getPageNumber(), $this->query->getOrder());
            $this->response = new ResponseHandler($data);
		}catch (ClientException $e) {
			
		}

		return $this->afterSend();
    }    
    
    public function getQuery()
    {
    	return $this->response->getQuery();	
    }

	public function setQuery($request, $pageLimit, $pageNumber)
	{
		$this->query = ($request ? self::requestToQuery($request, $pageLimit, $pageNumber) : new Query );

		return $this;
    }    
    
    public static function api()
    {
    	return new Api;
    }

    public function getPayload()
    {
        return $this->query->toArray();
    }

    public function getByIds($benuIds)
    {
    	$properties = Property::whereIn("benutech_id", $benuIds)->get();

    	$response = collect([]);

    	foreach ($properties as $k => $prop)
    	{
    		$updateProp = Api::propDetailsPopulateNull($prop);
    		$coll = PropertyCollection::createFromModel($updateProp);
			$response->push($coll);
    	}

    	return $response;
    }

    private function requestToQuery($request, $pageLimit, $pageNumber)
    {
        $query = new Query;

		$postalcode = $request->get('postalcode', null);
		$showOnlyNonOccupied = $request->get('is-owner-occupied', false);

		$maxBeds = $request->get('beds')['max'];
		$minBeds = $request->get('beds')['min'];

		$maxBaths = $request->get('bath')['max'];
		$minBaths = $request->get('bath')['min'];

		$maxSquareFootage = $request->get('square-footage')['max'];
		$minSquareFootage = $request->get('square-footage')['min'];

		$maxLotSize = $request->get('lot-size')['max'];
		$minLotSize = $request->get('lot-size')['min'];

		$maxEstimatedVal = $request->get('estimated-value')['max'];
		$minEstimatedVal = $request->get('estimated-value')['min'];

		$maxLastSoldDate = $request->get('last-sold-date')['max'];
		$minLastSoldDate = $request->get('last-sold-date')['min'];

		$dwellingType = $request->get('dwelling-type');
		$addresses = $request->get('addresses');
		$premovescore = $request->get('premovescore', null);
		$lifeEvents = $request->get('life-events');

		// Boundary coordinates
		$boundCoor = $request->get('coordinates');

		// Polygon coordinates
		$polygonCoor = $request->get('polygon-coordinates');


		if($showOnlyNonOccupied)
		{
			$query->whereNotOccupied();
		}

		// Bound coordinates
		if($boundCoor && isset($boundCoor['north']) && $boundCoor['north'])
		{
        	$query->whereInCoordinatesBox($boundCoor);
		}

		if($polygonCoor)
		{
   			$query->whereInCoordinatesPoly($polygonCoor);
		}


		if($premovescore && trim($premovescore) != "")
		{
            // TODO: adjust for Benutech
			// $query['premovescore'] = $premovescore;
		}
		
		if($minBeds > 0 && $maxBeds > 0)
		{
            $query->whereBetweenBed($minBeds, $maxBeds);
		}elseif($minBeds == 0 && $maxBeds > 0)
		{
			$query->whereBed($maxBeds, "<=");
		}elseif($minBeds > 0 && $maxBeds == 0)
		{
            $query->whereBed($minBeds, ">=");
		}

		if($minBaths > 0 && $maxBaths > 0)
		{
			$query->whereBetweenBath($minBaths, $maxBaths);
		}elseif($minBaths == 0 && $maxBaths > 0)
		{
			$query->whereBath($maxBaths, "<=");
		}elseif($minBaths > 0 && $maxBaths == 0)
		{
            $query->whereBath($minBaths, ">=");
		}

		if($minSquareFootage > 0 && $maxSquareFootage > 0)
		{
			$query->whereBetweenBuildingSize($minSquareFootage, $maxSquareFootage);
		}elseif($minSquareFootage == 0 && $maxSquareFootage > 0)
		{
            $query->whereBuildingSize($maxSquareFootage, "<=");
		}elseif($minSquareFootage > 0 && $maxSquareFootage == 0)
		{
            $query->whereBuildingSize($maxSquareFootage, ">=");
		}

		if($minLotSize > 0 && $maxLotSize > 0)
		{
            $query->whereBetweenLotSize($minLotSize, $maxLotSize);
			// $query['lotSize'] = ($minLotSize) . ':' . ($maxLotSize);
		}elseif(($minLotSize == 0 || !$minLotSize) && $maxLotSize > 0)
		{ 	
            // All less than max lot size.
            $query->whereLotSize($maxLotSize, "<=");
			//$query['lotSize'] = '<=|' . ($maxLotSize);
		}elseif($minLotSize > 0 && ($maxLotSize == 0 || !$maxLotSize))
		{
            // All greater than min lot size.
            $query->whereLotSize($maxLotSize, ">=");
		}

		if($minEstimatedVal > 0 && $maxEstimatedVal > 0)
		{
            $query->whereBetweenAvm($minEstimatedVal, $maxEstimatedVal);
		}elseif($minEstimatedVal == 0 && $maxEstimatedVal > 0)
		{
            $query->whereAvm($maxEstimatedVal, "<=");
		}elseif($minEstimatedVal > 0 && $maxEstimatedVal == 0)
		{
            $query->whereAvm($minEstimatedVal, ">=");
		}

		$isFilterSoldDate = false;
		$lastSoldDateMinYear = config('ciremzy.search-filter.last-sold-date.min');
		if($minLastSoldDate && config('ciremzy.search-filter.last-sold-date.min') <  $minLastSoldDate)
		{
			$lastSoldDateMinYear = $minLastSoldDate;
			$isFilterSoldDate = true;
		}
		
		$lastSoldDateMaxYear = config('ciremzy.search-filter.last-sold-date.max');
		if($maxLastSoldDate && config('ciremzy.search-filter.last-sold-date.max') > $maxLastSoldDate)
		{
			$lastSoldDateMaxYear = $maxLastSoldDate;
			$isFilterSoldDate = true;
		}

		if($isFilterSoldDate)
		{
            $query->whereBetweenSoldYear($lastSoldDateMinYear, $lastSoldDateMaxYear);
			// $query['saleDate'] = $lastSoldDateMinYear . '/01/01' . ':' . $lastSoldDateMaxYear . '/12/31';
		}

		if($request->get('state', null))
		{
            // TODO : add filter for state
	//		$query['state'] = $request->get('state');
		}

		if($request->get('city', null))
		{
            // TODO : add filter for city
			// $query['city'] = $request->get('city');
		}		

		if($postalcode)
		{
            $query->whereZip($postalcode);
		//	$query['zip'] = $postalcode;
		}			

		if($request->get('address1', null))
		{
			$query['deliveryLine'] = $request->get('address1'); // . ', ' . $request->get('address2');
		}		

		if($addresses)
		{
            // TODO: add search by address line
		//	$query['deliveryLines'] = $addresses;
		}

		if($dwellingType && $dwellingType != 'sfr')
		{
			// Pull all property type except RSFR
			$query->wherePropTypes(explode(',', $dwellingType));
		}elseif($dwellingType && $dwellingType == 'sfr')
		{
            $query->whereSingleType();
		}

		if($lifeEvents && $lifeEvents != '')
		{
			$query->whereInLeadTypes(explode(',', $lifeEvents));
		}

		/**
		 * Keys to match from javascript datatable columns data.
		 *
		 * @var        array
		 */
		$sortKeys = [
			'city'                 => Query::SITE_CITY, 
			'beds'                 => Query::NBR_BED, 
			'baths'                => Query::NBR_BATH, 
			'lotSize'              => Query::LOT_SIZE, 
			'estimatedValue.human' => Query::FINAL_AVM, 
			'sqft'                 => Query::BUILDING_SIZE,
			'zip'                  => Query::SITE_ZIP,
			'lastSold'             => Query::TRANSFER_DATE,
			'lastSoldAmt'          => Query::TRANSFER_VALUE,
			'isOwnerOccupied'      => Query::OWNER_OCCUPIED,
			'mortgageDt'           => 'first_position_loan_date',
			'mortgageAmt'          => 'first_position_loan_val',
			'function'             => Query::PROPERTY_SINGLE
		];

		$orderBy = $request->get('orderBy', null);
		/**
		 * Assing sorting key.
		 */
		
		if($orderBy && isset($sortKeys[$orderBy]) ) 
		{
			$query->setSort( $sortKeys[$orderBy], $request->get("sort-order", "asc"));
		}

		/**
		 * Set search order.
		 */
        $query->setPageSize($pageLimit);
        $query->setPageNumber($pageNumber);

		if($request->get('noPageSize'))
		{
		//	unset($query['pageNumber']);
		//	unset($query['pageSize']);			
		}

		if($request->get('apiId', null))
		{
			if($request->get('filterByIdOnly', false))
			{
			//	$query = [];	
			}
			
		//	$query['id'] = $request->get('apiId');

			if(!$request->get('filterByIdOnly', false) && isset($query['pageNumber']) && isset($query['pageSize']))
			{
				//When searching by ID we remove page number and size;
			//	unset($query['pageNumber']);
			//	unset($query['pageSize']);
			}
        }
        
        $this->query = $query;

		return $query;
    }

    private function sqftToAcre($sqftNum)
    {
    	return ($sqftNum / 43560);
    }    

	public function laterQuery($page = 1, $pagesize = 100)
	{

		return null;
    }    
    
	public function getProperties()
	{
		if(!$this->hasProperty())
		{
			return null;
		}	

		return $this->response->getRecords();
    }    
    
	public function toSnapshot($bool)
	{
        // Do nothing
		return $this;
	}

	public function setAction($classStr)
	{
        // Do nothing
		return $this;
    }
    
	public function hasProperty()
	{
		if(!$this->response)
		{
			return false;
		}

		return $this->response->hasRecords();
	}	    

	public function getFirstProperty()
	{
        return $this->response->first();
	}

	public function getResponseTotalPage()
	{
		return $this->response->recordsTotal();
	}

	public static function updateMore($benuTechId, $data)
	{
		$avmVal = $data['estimatedValue'];
		$mortAmt = $data['mortgageAmount'];
		$prop = Property::forBenuTechId($benuTechId)->first();

		if(!$prop)
		{
			return null;
		}

		$prop->estimated_value_min = $avmVal;
		$prop->estimated_value_max = $avmVal;
		$prop->mortgage_amt = str_replace(",", "", $mortAmt);

		return $prop->save();
	}

	public static function createUpdate($globalSearchRowData)
	{

		$collect = new PropertyCollection($globalSearchRowData);
		$prop = Property::forBenuTechId($collect->getId())->first();
		$addr = $collect->getAddress();

    	/**
    	 * Create new 
    	 */
		if(!$prop)
		{
			$prop = new Property;
		}

    	$prop->benutech_id = $collect->getId();
    	$prop->address = $addr;
    	$prop->zipcode = $collect->getAllDAta()->sa_site_zip;
    	$prop->house_area = $collect->getSqft();
    	$prop->lot_area = $collect->getLotSizeSqft();
    	$prop->baths = $collect->getBaths();
    	$prop->beds = $collect->getBeds();
    	$prop->half_baths = $collect->getHalfBaths();
    	$prop->api = 'BenuTech';
    	$prop->last_sold_price = $collect->getAmountLastSold();
    	$prop->last_sold_date = $collect->getLastSold();
    	$prop->fips = $collect->getAllDAta()->mm_fips_state_code;
    	$coor = $collect->getCoordinates();

    	if($collect->getAllDAta()->use_code_std && $collect->getAllDAta()->use_code_std == "Rsfr")
    	{
    		$prop->property_type = "RSFR";
    	}elseif($collect->getAllDAta()->use_code_std)
    	{
    		$prop->property_type = "Multi";
    	}

    	if($coor)
    	{
    		$prop->location = $coor->getLatitude() . '|' . $coor->getLongtitude();
    	}

    	if($collect->getEstimatedValue() && isset($collect->getEstimatedValue()->value))
    	{
    		$prop->estimated_value_min = $collect->getEstimatedValue()->value;
    		$prop->estimated_value_max = $collect->getEstimatedValue()->value;
    	}   

    	$prop->save(); 	

    	return $prop;
	}
}