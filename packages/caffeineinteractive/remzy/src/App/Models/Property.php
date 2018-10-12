<?php

namespace CaffeineInteractive\Remzy\App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Property extends Model
{
    protected $table = 'properties';
    protected $appends = ['last_sold_date_or_na', 'last_sold_price_or_na', 'estimated_value_max_human', 'apiId'];
    protected $fillable = ['address', 'last_sold_price', 'last_sold_date', 'ob_id', 'hj_id', 'api', 'zipcode', 'house_area', 'lot_area', 'baths', 'beds', 'property_type', 'owned_occupied', 'basement', 'year_build', 'location', 'estimated_value_min', 'estimated_value_max', 'half_baths', "benutech_id", "mortgage_amt", "fips"];

    public function scopeForOnBoardId($query, $onBoardId)
    {
    	return $query->where('ob_id', $onBoardId);
    }

    public function scopeForHomeJunctionId($query, $hjId)
    {
        return $query->where('hj_id', $hjId);
    }

    public function scopeForBenuTechId($query, $id)
    {
        return $query->where("benutech_id", $id)->where("api", "BenuTech");
    }

    public function scopeForAddr($query, $addr)
    {
        return $query->where('address', $addr);
    }

    public function scopeInAddresses($query, $addrs)
    {
        return $query->whereIn('address', $addrs);
    }

    public function getBedsOrNaAttribute()
    {
    	if(!isset($this->attributes['beds']) || !$this->attributes['beds'] || $this->attributes['beds'] < 1)
    	{
    		return 'n/a';
    	}

    	return $this->attributes['beds'] + 0;
    }

    public function getBathsOrNaAttribute()
    {
    	if(!isset($this->attributes['baths']) || !$this->attributes['baths'] || $this->attributes['baths'] < 1)
    	{
    		return 'n/a';
    	}

    	return $this->attributes['baths'] + 0;
    }    

    public function getLastSoldDateOrNaAttribute()
    {
    	if(!isset($this->attributes['last_sold_date']) || !$this->attributes['last_sold_date'] || $this->attributes['last_sold_date'] == '0000-00-00')
    	{
    		return 'n/a';
    	}

		$d = new Carbon($this->attributes['last_sold_date']);

    	return $d->format('Y/m/d');    	
    }

    public function getLastSoldPriceOrNaAttribute()
    {
    	$toNum = (float)$this->attributes['last_sold_price'];

    	if(!isset($this->attributes['last_sold_price']) || !$this->attributes['last_sold_price'] || $toNum == 0)
    	{
    		return 'n/a';
    	}

    	return '$ ' . number_format($this->attributes['last_sold_price'] + 0);	
    }  

    public function getEstimatedValueGapAttribute()
    {
        return ($this->getEstimatedValueAttribute() * $this->getGapPercentAttribute());
    }

    public function getEstimatedValueMaxAttribute()
    {
        $gap = $this->getEstimatedValueGapAttribute();

        return $this->getEstimatedValueAttribute() + $gap;
    }

    public function getEstimatedValueMinAttribute()
    {
        $gap = $this->getEstimatedValueGapAttribute();

        return ($this->getEstimatedValueAttribute() - $gap);
    }

    public function getGapPercentAttribute()
    {
        return 0.05;
    }

    /**
     * Get the middle of max and min estimated value
     * @return [type] [description]
     */
    public function getEstimatedValueAttribute()
    {
        return (isset($this->attributes['estimated_value_max']) ? $this->attributes['estimated_value_max'] : null);
    }

    public function getEstimatedValueMaxHumanAttribute()
    {
        $value = (float)$this->getEstimatedValueMaxAttribute();

        if($value < 1)
        {
            return null;
        }

        return '$ ' . number_format($value + 0);  
    }

    public function getEstimatedValueMinHumanAttribute()
    {
        $value = (float)$this->getEstimatedValueMinAttribute();

        if($value < 1)
        {
            return null;
        }

        return '$ ' . number_format($value + 0);  
    }    

    public function getLocationLatitudeAttribute()
    {
    	if(!isset($this->attributes['location']) || !$this->attributes['location'] || $this->attributes['location'] == '')
    	{
    		return null;
    	}    	

    	$exp = explode('|', $this->attributes['location']);

    	return $exp[0];
    }  

    public function getLocationLongtitudeAttribute()
    {
    	if(!isset($this->attributes['location']) || !$this->attributes['location'] || $this->attributes['location'] == '')
    	{
    		return null;
    	}    	

    	$exp = explode('|', $this->attributes['location']);

    	return $exp[1];
    }    

    public function getHouseAreaOrNaAttribute()
    {
        if( !isset($this->attributes['house_area']) )
        {
            return 'N/A';
        }

        return number_format($this->attributes['house_area'] + 0);
    }

    /**
     * Gets the estimated value maximum percent.
     *
     * @return     integer  The estimated value maximum percent attribute.
     */
    public function getEstimatedValMaxPerAttribute()
    {
        $max = config('ciremzy.search-filter.estimated-value.max');
        $val = $this->getEstimatedValueMaxAttribute();

        if(!$val)
        {
            return null;
        }        

        if($val == 0)
        {
            return 0;
        }

        $total = $val / $max;

        return 100 * ($total + 0);
    }

    /**
     * Gets the estimated value minimum percent.
     *
     * @return     integer  The estimated value minimum percent attribute.
     */
    public function getEstimatedValMinPerAttribute()
    {
        $min = config('ciremzy.search-filter.estimated-value.max');
        $val = $this->attributes['estimated_value_min'];

        if(!$val)
        {
            return null;
        }

        if($val == 0)
        {
            return 0;
        }

        $total = $val / $min;

        return 100 * ($total + 0);
    }

    /**
     * Round to nearest multiple of 5.
     *
     * @return     integer  The est value minimum round off attribute.
     */
    public function getEstValMaxRoundOffAttribute()
    {
        $n = $this->getEstimatedValMaxPerAttribute();
        $x = 5;

        if(!$n)
        {
            return 0;
        }
        $n = $n + 0;

        return (round($n)%$x === 0) ? round($n) : round(($n+$x/2)/$x)*$x;
    }

    /**
     * Round to nearest multiple of 5.
     *
     * @return     integer  The est value minimum round off attribute.
     */
    public function getEstValMinRoundOffAttribute()
    {
        $n = $this->getEstimatedValMinPerAttribute();
        $x = 5;

        if(!$n)
        {
            return 0;
        }

        $n = $n + 0;

        return (round($n)%$x === 0) ? round($n) : round(($n+$x/2)/$x)*$x;
    }

    public function getGoogleStreetViewThumbAttribute()
    {
        $apiKey = config('ciremzy.google-map-api.streen_view_key');
        $long = $this->getLocationLongtitudeAttribute();
        $lat = $this->getLocationLatitudeAttribute();
        $metaUrl = 'https://maps.googleapis.com/maps/api/streetview/metadata?size=1920x571&location=' . $lat . ',' . $long . '&heading=34&pitch=0&key=' . $apiKey;

        if(!$long || !$lat) 
        {
            return '';
        }

        $meta = json_decode( file_get_contents($metaUrl) );
    
        //Check if thumb exists.        
        if($meta && isset($meta->status) && $meta->status == 'OK')
        {
            $url = 'https://maps.googleapis.com/maps/api/streetview?size=1920x571&location=' . $lat . ',' . $long . '&heading=34&pitch=0&key=' . $apiKey;
        }else{
            $url = asset('vendors/ciremzy/img/compare/default-bg.jpg');
        }
        
        return $url;
    }

    /**
     * Gets the lot to acres attribute.
     *
     * @return     integer|float  The lot to acres attribute.
     */
    public function getLotToAcresAttribute()
    {
        $lotArea = $this->attributes['lot_area'];

        if(!$lotArea)
        {
            return 0;
        }

        return (($lotArea + 0) * 0.000022957);
    }

    public function getTypeHumanAttribute()
    {
        if($this->property_type == 'SFR' || $this->property_type == 'RSFR')
        {
            return 'Single Family Home';
        }elseif(!$this->property_type)
        {
            return 'n/a';
        }

        return 'Multi Family Home';
    }

    public function getApiIdAttribute()
    {
        return $this->benutech_id;
    }

    /**
     * Gets the address 1 attribute.
     * Get first line of the address.
     * 
     * @return string
     */
    public function getAddress1Attribute()
    {
        $addrParts = explode(',', $this->address);

        return (isset($addrParts[0]) ? trim($addrParts[0]) : null);
    }

    /**
     * Gets the address 2 attribute.
     * Get first line of the address.
     * 
     * @return string
     */
    public function getAddress2Attribute()
    {
        $addrParts = explode(',', $this->address);

        if(!isset($addrParts[1]) || !isset($addrParts[2]))
        {
            return null;
        }

        $city = trim($addrParts[1]);
        $stateZip = explode(' ', trim($addrParts[2]));
        $state = $stateZip[0];


        return $city . ', ' . $state;
    }

    public function getCityAttribute()
    {
        $addr2 = $this->getAddress2Attribute();

        if(!$addr2)
        {
            return null;
        }

        return trim(explode(",", $addr2)[0]);
    }

    public function owner()
    {
        return $this->hasOne("App\Owner", "properties_id", "id");
    }
}