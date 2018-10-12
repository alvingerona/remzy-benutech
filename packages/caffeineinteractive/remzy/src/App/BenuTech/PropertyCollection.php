<?php

namespace CaffeineInteractive\Remzy\App\BenuTech;

use Carbon\Carbon;
use CaffeineInteractive\Remzy\App\ApiPropCollect\PropItemInterface;
use CaffeineInteractive\Remzy\App\Models\Property;
use CaffeineInteractive\Remzy\App\Boundary\Coordinate;

class PropertyCollection implements PropItemInterface
{
    const FIELD_SALES_DATE  = "sa_date_transfer";

    const FIELD_SALES_AMOUNT = "sa_val_transfer";

    const FIELDS_AVM = "avm_final_value";

	protected $address;

	protected $city;

	protected $beds;	

	protected $baths;

	protected $sqft;

	protected $lotSize;

	protected $lastSold;

	protected $amountLastSold;

	protected $estimatedValue;

	protected $dwellingType;

	protected $id;

    protected $dbProperty;

    protected $hjRow;

    protected $coordinates;

    protected $halfBaths;

    protected $ownerOccupied;

    protected $mortgageAmt;

    protected $mortgageDt;

    protected $preMoverScore;

    protected $preMoverScoreVal;

    protected $apiRow;

    public function __construct($data = null)
    {
        if($data)
        {
            $this->setData($data);
        }
    }

    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Street address
     *
     * @param $row
     * @return void
     */
    public function setAddress($row)
    {
        $addr = "";
        if(isset($row->sa_site_street_name))
        {
            $addr = $row->sa_site_house_nbr . " " . $row->sa_site_street_name . ', ' . $row->sa_site_city . ', ' . strtoupper($row->sa_site_state) . ' ' . $row->sa_site_zip;
        }

        $this->address = $addr;

        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($row)
    {
        $this->city = $row->sa_site_city;

        return $this;
    }

    public function getBeds()
    {
        return $this->beds;
    }

    public function setBeds($row)
    {
        if($row->sa_nbr_bedrms && $row->sa_nbr_bedrms > 0)
        {
            $this->beds = $row->sa_nbr_bedrms;            
        }

        return $this;
    }

    public function getBaths()
    {
        return $this->baths;
    }

    public function setBaths($row)
    {
        if($row->sa_nbr_bath && $row->sa_nbr_bath > 0)
        {
            $this->baths = $row->sa_nbr_bath;            
        }

        return $this;
    }

    public function getSqft()
    {
        return $this->sqft;
    }

    public function getHalfBaths()
    {
        return 0;
    }

    public function setSqft($row)
    {
        if($row->sa_sqft && $row->sa_sqft > 0){
            $this->sqft = $row->sa_sqft;            
        }

        return $this;
    }

    public function getLotSize()
    {
        return $this->lotSize;
    }

    public function setLotSize($row)
    {

        if(!$row->sa_lotsize ){
            return $this;
        }

       $this->lotSize = [
        "acre" => $row->sa_lotsize / 43560, 
        "sqft" => $row->sa_lotsize
       ];

        return $this;
    }

    public function getLotSizeSqft()
    {
        if(!$this->lotSize)
        {
            return null;
        }

        return $this->lotSize["sqft"];
    }

    public function getLastSold()
    {
        return $this->lastSold;
    }

    public function setLastSold($row)
    {
        $this->lastSold = $row->{self::FIELD_SALES_DATE};
        return $this;
    }

    public function getAmountLastSold()
    {
        return $this->amountLastSold;
    }

    public function setAmountLastSold($row)
    {
        $this->amountLastSold = $row->{self::FIELD_SALES_AMOUNT};
        return $this;
    }

    public function getEstimatedValue()
    {
        return $this->estimatedValue;
    }

    public function setEstimatedValue($row)
    {
        if(isset($row->{self::FIELDS_AVM}) && $row->{self::FIELDS_AVM})
        {
            $this->estimatedValue = [
                "human" => '$' . number_format($row->{self::FIELDS_AVM}),
                "value" => $row->{self::FIELDS_AVM}
            ];
        }
        return $this;
    }

    public function getDwellingType()
    {
        return $this->dwellingType;
    }

    public function setDwellingType($row)
    {
        if(!isset($row->use_code_std) || $row->use_code_std == "" || !$row->use_code_std)
        {
            return $this;
        }

        if($row->use_code_std == "Rsfr")
        {
            $this->dwellingType = [
                "label" => "Single-Family Home",
                "name" => "Single-Family Home",
            ];
        }else
        {
            $this->dwellingType = [
                "label" => "Multi Family Home",
                "name" => "Multi Family Home",
            ];
        }

        return $this;
    }

    public function setFromModel($property)
    {
        $this->address = $property->address;
        $this->city = $property->city;
        $this->beds = $property->beds;
        $this->baths = $property->baths;
        $this->sqft = $property->house_area;
        $this->lotSize = $property->lot_area ?  [
            "acre" => $property->lot_area / 43560, 
            "sqft" => $property->lot_area
        ] : null;
        $this->lastSold = $property->last_sold_date;
        $this->amountLastSold = $property->last_sold_price;
        $this->estimatedValue = $property->estimated_value_max ? [
            "human" => '$' . number_format($property->estimated_value_max),
            "value" => $property->estimated_value_max
        ] : null;
        $this->dwellingType = $property->property_type ? [
            "label" => $property->property_type,
            "name" => $property->property_type,
        ] : null;
        $this->id = $property->benutech_id;
        $this->dbProperty = $property;
        $loc = $property->location ? explode("|", $property->location) : null;
        $this->coordinates = $loc ? Coordinate::set((float)$loc[0], (float)$loc[1]) : null;
        $this->halfBaths = 0;
        $this->ownerOccupied = $property->owned_occupied && $property->owned_occupied == "y";
        $this->mortgageAmt = $property->mortgage_amt ? '$' . number_format($property->mortgage_amt) : null;

        return $this;
    }

    public function setCoordinates($long, $lat)
    {
        $this->coordinates = Coordinate::set((float)$long, (float)$lat);
        return $this;
    }

    public function getArrayCoordinates()
    {

        if(!$this->coordinates)
        {
            return null;
        }

        return $this->coordinates->toArray();
    }    

    public static function createFromModel($property)
    {
        $propCollect = new PropertyCollection;

        return $propCollect->setFromModel($property);
    }   
    
    public function getMortgageDt()
    {
        return null;
    }
    
    public function setMortgageDt($row)
    {
        if(isset($row->first_position_loan_date))
        {
            $this->mortgageDt = $row->first_position_loan_date;
        }

        return $this;
    }   

    public function setMortgageAmt($row)
    {
        if(isset($row->first_position_loan_val) && $row->first_position_loan_val && $row->first_position_loan_val > 0)
        {
            $this->mortgageAmt = '$' . number_format($row->first_position_loan_val);
        }

        return $this;
    }

    public function getMortgageAmt()
    {
        return $this->mortgageAmt;
    }
    
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    public function setPreMoverScoreVal($row)
    {

        if(!isset($row->{self::FIELDS_AVM}) || !isset($row->{self::FIELD_SALES_AMOUNT}))
        {

            return $this;
        }

        if($row->{self::FIELD_SALES_AMOUNT} <= 0){

            return $this;
        }

        $soldYear = explode("-", $row->{self::FIELD_SALES_DATE})[0];
        $yearOfOwnerShip = date("Y") - $soldYear;

        if($yearOfOwnerShip <= 0){

            return $this;
        }

        $yearAppreciate = ($row->{self::FIELDS_AVM} - $row->{self::FIELD_SALES_AMOUNT} ) / $yearOfOwnerShip;

        $this->preMoverScoreVal = ($yearAppreciate / $row->{self::FIELD_SALES_AMOUNT}) * 100;

        return $this;
    }
    
    public function getPreMoverScoreVal()
    {

        return $this->preMoverScoreVal;
    }

    public function getPreMoverScore()
    {
        return $this->preMoverScore ;
    }        

    public function setPreMoveScore($row)
    {

        $score = $this->getPreMoverScoreVal();

        if(!$score)
        {   
            $lbl = "Low";
        }elseif($score < 4)
        {
            $lbl = "Low";
        }elseif($score > 4 && $score < 7)
        {
            $lbl = "Med-Low";
        }elseif($score > 7 && $score < 10)
        {
            $lbl = "Med-High";
        }elseif($score > 10)
        {
            $lbl = "High";
        }else{
             $lbl = "Low";
        }

        $this->preMoverScore = $lbl;

        return $this;
    }

    public function getOwnerOccupied()
    {
        return $this->ownerOccupied;
    }

    public function setOwnerOccupied($row)
    {
        $this->ownerOccupied = (isset($row->sa_site_mail_same) && $row->sa_site_mail_same == "Y" ? true : false);
        return $this;
    }

    public function setId($row)
    {
        $this->id = $row->sa_property_id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setData($row)
    {
        $this->apiRow = $row;

        $this->setAddress($row)
            ->setOwnerOccupied($row)
            ->setMortgageAmt($row)
            ->setMortgageDt($row)
            ->setDwellingType($row)
            ->setEstimatedValue($row)
            ->setAmountLastSold($row)
            ->setLastSold($row)
            ->setLotSize($row)
            ->setSqft($row)
            ->setBaths($row)
            ->setBeds($row)
            ->setCity($row)
            ->setId($row)
            ->setPreMoverScoreVal($row)
            ->setPreMoveScore($row);

        if($row->sa_x_coord && $row->sa_y_coord)
        {
            $this->setCoordinates($row->sa_x_coord, $row->sa_y_coord);
        }

        return $this;
    }
    
    public function getSingleUrl()
    {
        return route('property.single.benutech', ['id' => $this->getId()]);
    } 

    public function getAllDataUrl()
    {
        // Get all data URL only there AVM or estimated value don't exists.
        if(!$this->getMortgageAmt() || !$this->getEstimatedValue())
        {

            if(env("BENUTECG_DEMO_MODE", false))
            {
                return null;
            }

            return route("ciremzy.benutech.property.details", ["benutechPropId" => $this->getId(), "stateFips" => $this->apiRow->mm_fips_state_code]);
        }
    }

    public function getAllDAta()
    {
        return $this->apiRow;
    }

    /**
     * Load only if property benutech ID is set.
     * 
     * @return 
     */
    public function loadEmptyFieldsFromModel()
    {
        $property = Property::ForBenuTechId($this->id)->first();

        if(!$property)
        {
            return $this;
        }

        if(!$this->estimatedValue && $property->estimated_value_max && $property->estimated_value_max > 0)
        {
            $this->estimatedValue = [
                "human" => '$' . number_format($property->estimated_value_max),
                "value" => $property->estimated_value_max
            ];
        }

        if(!$this->mortgageAmt && $property->mortgage_amt && $property->mortgage_amt > 0)
        {
            $this->mortgageAmt = '$' . number_format($property->mortgage_amt);
        }
        
        return $this;
    }
}