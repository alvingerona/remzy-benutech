<?php

namespace CaffeineInteractive\Remzy\App\BenuTech;

use CaffeineInteractive\Remzy\App\BenuTech\Api;
use CaffeineInteractive\Remzy\App\BenuTech\ResponseHandler;

/**
 * $match value
 * - =
 * - >
 * - >=
 * - <= 
 * - Not
 * - From-To : the value of this match must array to to and from. [to: NUM, from: NUM]
 */
class Query
{    
    const SITE_CITY = "sa_site_city";
    const SITE_ADDR = "site_address";
    const SITE_ZIP = "sa_site_zip";
    const NBR_BATH = "sa_nbr_bath";
    const FIPS_CODE = "mm_fips_state_code";
    const FINAL_AVM = "avm_final_value";
    const TRANSFER_DATE = "sa_date_transfer";
    const TRANSFER_VALUE = "sa_val_transfer";
    const NBR_BED = "sa_nbr_bedrms";
    const LOT_SIZE = "sa_lotsize";
    const BUILDING_SIZE = "sa_sqft";
    const PROPERTY_SINGLE = "use_code_std";
    const OWNER_OCCUPIED = "sa_site_mail_same";
    const COMPARE_BETWEEN = "From-To";
    const PROPTYPE_SINGLE = "RSFR";
    const LEAD_TYPE = "leads_type";

    protected $query;

    protected $pageNumber  = 0;

    protected $pageSize = 20;

    /**
     * @var null|array
     */
    protected $order = null; 


    public function setPageNumber($num)
    {
        $this->pageNumber = $num;

        return $this;
    }

    public function setPageSize($num)
    {
        $this->pageSize = $num;

        return $this;
    }

    public function setSort($orderBy, $order = "asc")
    {
        $this->order["sort"] = $orderBy;
        $this->order["direction"] = $order;

        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * get only those single property type
     *
     * @return this
     */
    public function whereSingleType()
    {
        return $this->setQueryProps(self::PROPERTY_SINGLE, [self::PROPTYPE_SINGLE], null); 
    }

    public function wherePropTypes($arr)
    {
        return $this->setQueryProps(self::PROPERTY_SINGLE, $arr, null); 
    }    

    public function whereInLeadTypes($arr)
    {
        return $this->setQueryProps(self::LEAD_TYPE, $arr, null); 
    }    

    /**
     * Get only those property who is not occupied.
     *
     * @return this
     */
    public function whereNotOccupied()
    {
        return $this->setQueryProps(self::OWNER_OCCUPIED, "N", null);  
    }

    public function whereBuildingSize($sizeSqft, $match)
    {
        return $this->setQueryProps(self::BUILDING_SIZE, $sizeSqft, $match); 
    }

    public function whereBetweenBuildingSize($min, $max)
    {
        return $this->whereBuildingSize(["from" => $min, 'to'  => $max], self::COMPARE_BETWEEN); 
    }    

    /**
     * Lot size
     *
     * @param integer|array $sizeSqft
     * @param string $match
     * @return void
     */
    public function whereLotSize($sizeSqft, $match)
    {
        return $this->setQueryProps(self::LOT_SIZE, $sizeSqft, $match); 
    }    

    public function whereBetweenLotSize($min, $max)
    {
        return $this->whereLotSize(["from" => $min, 'to'  => $max], self::COMPARE_BETWEEN); 
    }

    public function whereZip($zip)
    {
       $this->setQueryProps(self::SITE_ZIP, [$zip], null);
      
       if(!$this->isPropsExists(self::FIPS_CODE))
       {
            // TODO: find way to search zip without fips needed.
            // $this->setQueryProps(self::FIPS_CODE, "06", null);
            // $this->setQueryProps("mm_fips_muni_code", "037", null);
            // $this->setQueryProps("customFilters", [], null);
            // $this->setQueryProps("searchOptions", ["omit_saved_records" => false], null);
       }

        return $this;
    }

    public function whereBed($value, $match = "=")
    {
        return $this->setQueryProps(self::NBR_BED, $value, $match);
    }

    public function whereBetweenBed($min, $max)
    {
        return $this->whereBed(["from" => $min, "to"=> $max], self::COMPARE_BETWEEN);
    }

    /**
     * Bed
     *
     * @param integer $value
     * @param string $match
     * @return void
     */
    public function whereBath($value, $match = "=")
    {
        return $this->setQueryProps(self::NBR_BATH, $value, $match );
    }    

    public function whereBetweenBath($min, $max)
    {
        return $this->whereBath(["from" => $min, "to"=> $max], self::COMPARE_BETWEEN);
    }

    public function whereInCoordinatesPoly($bounds)
    {
        $coords = [];

        foreach($bounds as $bound)
        {
            $coords[] = $bound[0] . " " . $bound[1];
        }

        $value = ['wkt' => 'POLYGON((' . implode(",", $coords) . '))'];
        $ret = $this->setQueryProps('geometry', $value ,"polygon");

        return  $ret;   
    }

    public function whereInCoordinatesBox($bounds)
    {
        $coords = [
            $bounds["east"] . ' ' .$bounds["north"],
            $bounds["east"] . ' ' . $bounds["south"],
            $bounds["west"] . ' ' . $bounds["south"],
            $bounds["west"] . ' ' . $bounds["north"],
            $bounds["east"] . ' ' . $bounds["north"],
        ];
        $value = ['wkt' => 'POLYGON((' . implode(",", $coords) . '))'];
        $ret = $this->setQueryProps('geometry', $value ,"polygon");

        return $ret;
    }
/*
array (
    'searchOptions' =>  array (
        'omit_saved_records' => false,
    ),
    'customFilters' =>  array ( ),
    'geometry' => array (
        'match' => 'polygon',
        'value' =>  array (
            'wkt' => 'POLYGON((-97.95221815848188 30.20681602428497,-97.95629879463846 30.152817930936575,-97.837646450896 30.209799980183135,-97.9010532589283 30.218208823627553,-97.95221815848188 30.20681602428497))',
        ),
    ),
    'mm_fips_state_code' => '48',
    'mm_fips_muni_code' => '453',
    'use_code_std' =>  array (
        0 => 'RSFR',
        1 => 'RCON',
    ),
)
*/
    public function addSearchOption()
    {

    }

    /**
     * Sold between amount
     *
     * @param date $from
     * @param date $to
     * @return void
     */    
    public function whereBetweenSoldAmt($from, $to)
    {
        return $this->setQueryProps(self::TRANSFER_VALUE, ["from" => $from, "to"=> $to], self::COMPARE_BETWEEN);
    }

    public function whereSoldAmt($date, $match = "=")
    {
        return $this->setQueryProps(self::TRANSFER_VALUE, $value, $match);
    }

    /**
     * Sold between years
     *
     * @param year $fromYear
     * @param year $toYear
     * @return void
     */
    public function whereBetweenSoldYear($fromYear, $toYear)
    {
        $from = $fromYear . "-01-01";
        $to = $toYear . "-12-31";
        return $this->whereBetweenSoldDate($from, $to);
    }
    
    public function whereBetweenSoldDate($from, $to)
    {
        return $this->whereSoldDate(["from" => $from, "to"=> $to], self::COMPARE_BETWEEN);
    }

    public function whereSoldDate($date, $match = "=")
    {
        return $this->setQueryProps(self::TRANSFER_DATE, $date, $match);
    }

    public function whereBetweenAvm($from, $to)
    {
        return $this->whereAvm(["from" => $from, "to" => $to], self::COMPARE_BETWEEN);
    }

    public function whereAvm($value, $match = "=")
    {
        return $this->setQueryProps(self::FINAL_AVM, $value, $match );
    }

    public function whereFipsStateCode($value)
    {
        return $this->setQueryProps(self::FIPS_CODE, $value, null );
    }

    public function isPropsExists($key)
    {
        return isset($this->query[$key]);
    }

    public function setQueryProps($key, $value, $match = "=")
    {
        if($match)
        {
            $this->query[$key] = [
                "value" => $value,
                "match" => $match
            ];
        }else{
            $this->query[$key] = $value;
        }

        return $this;
    }

    public function get()
    {
        return json_encode($this->query);
    }

    public function toArray()
    {
        return $this->query;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function getPageNumber()
    {
        return $this->pageNumber;
    }
}