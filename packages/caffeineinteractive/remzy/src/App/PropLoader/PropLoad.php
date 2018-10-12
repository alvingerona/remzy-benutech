<?php

namespace CaffeineInteractive\Remzy\App\PropLoader;

use CaffeineInteractive\Remzy\App\OnBoardApi\OnBoard;
use CaffeineInteractive\Remzy\App\HomeJunction\HomeJunction;
use CaffeineInteractive\Remzy\App\HomeJuncNative\HomeJuncNative;
use CaffeineInteractive\Remzy\App\BenuTech\BenuTech;
use File;

class PropLoad
{
	/**
	 * Marker on which Action has been use.
	 */
	protected $lastAction;

	public static function load($request = null, $perPage = 10, $currentPage = 0, $action = null)
	{ 
		$propLoad = new PropLoad;
		$action = ($action ? $action : 'loadBenuTech');

		return $propLoad->{$action}($request, $perPage, $currentPage);
	}

	public function loadOnBoard($request = null, $perPage = 10, $currentPage = 0)
	{
		return new OnBoard($request, $perPage, $currentPage);
	}

	public function loadHomeJunction($request = null, $perPage = 10, $currentPage = 0)
	{
		return new HomeJunction($request, $perPage, $currentPage); 
	}

	public function loadHomeJunctionNative($request = null, $perPage = 10, $currentPage = 0)
	{
		return new HomeJuncNative($request, $perPage, $currentPage); 
	}

	public function loadBenuTech($request = null, $perPage = 10, $currentPage = 0)
	{
		return new BenuTech($request, $perPage, $currentPage);
	}

	/**
	 * Look on first $actions value when did not found any then look on next action.
	 *
	 * @param      Request  $request      The request
	 * @param      integer  $perPage      The per page
	 * @param      integer  $currentPage  The current page
	 * @param      array    $actions      The actions
	 */
	public static function findOnApis($request = null, $perPage = 10, $currentPage = 0, $actions = [ 'loadHomeJunctionNative', 'loadHomeJunction'])
	{
		$property = null;

		foreach ($actions as $action)
		{
			$propApiLoader = PropLoad::load($request, $perPage, $currentPage, $action);

			$propApiLoader->setAction('PublicRecords'); // HJ API

			/**
			 * Use express call HJ or OB
			 */
			$propApiLoader->toSnapshot(true);
			/**
			 * Send API request.
			 */
			$propApiLoader->send();	

			/**
			 * Collection property
			 *
			 * @var        Collection
			 */
			$collectProp = $propApiLoader->getFirstProperty();

			/**
			 * Update or create Property Model of the HJ found property.
			 *
			 * @var      Property
			 */

			if($propApiLoader->hasProperty())
			{
				$found = true;
				$property = $propApiLoader->createOrUpdateModelFromProperty($collectProp, true);

				if($property)
				{
					$propApiLoader->saveSearch($property->address, $property);
				}

				break;
			}

		}

		return $property;
	}

	/**
	 * Load list of suffixes from json file then 
	 * Covert to valid array.
	 *
	 * @param      boolean  $labelAsKey  The label as key
	 *
	 * @return     Array
	 */
	public static function addrSuffixes($labelAsKey = false)
	{
		$filePath = base_path('etc/jsons/address-suffixes.json');

		if(!File::exists($filePath))
		{ 	
			/**
			 * This is workaround for production bug.
			 *
			 * @var        string
			 */
			$suffixesJsonFile = '[{"ALLEY":"ANEX","ALY":"ANX"},{"ALLEY":"ARCADE","ALY":"ARC"},{"ALLEY":"AVENUE","ALY":"AVE"},{"ALLEY":"BAYOU","ALY":"BYU"},{"ALLEY":"BEACH","ALY":"BCH"},{"ALLEY":"BEND","ALY":"BND"},{"ALLEY":"BLUFF","ALY":"BLF"},{"ALLEY":"BLUFFS","ALY":"BLFS"},{"ALLEY":"BOTTOM","ALY":"BTM"},{"ALLEY":"BOULEVARD","ALY":"BLVD"},{"ALLEY":"BRANCH","ALY":"BR"},{"ALLEY":"BRIDGE","ALY":"BRG"},{"ALLEY":"BROOK","ALY":"BRK"},{"ALLEY":"BROOKS","ALY":"BRKS"},{"ALLEY":"BURG","ALY":"BG"},{"ALLEY":"BURGS","ALY":"BGS"},{"ALLEY":"BYPASS","ALY":"BYP"},{"ALLEY":"CAMP","ALY":"CP"},{"ALLEY":"CANYON","ALY":"CYN"},{"ALLEY":"CAPE","ALY":"CPE"},{"ALLEY":"CAUSEWAY","ALY":"CSWY"},{"ALLEY":"CENTER","ALY":"CTR"},{"ALLEY":"CENTERS","ALY":"CTRS"},{"ALLEY":"CIRCLE","ALY":"CIR"},{"ALLEY":"CIRCLES","ALY":"CIRS"},{"ALLEY":"CLIFF","ALY":"CLF"},{"ALLEY":"CLIFFS","ALY":"CLFS"},{"ALLEY":"CLUB","ALY":"CLB"},{"ALLEY":"COMMON","ALY":"CMN"},{"ALLEY":"COMMONS","ALY":"CMNS"},{"ALLEY":"CORNER","ALY":"COR"},{"ALLEY":"CORNERS","ALY":"CORS"},{"ALLEY":"COURSE","ALY":"CRSE"},{"ALLEY":"COURT","ALY":"CT"},{"ALLEY":"COURTS","ALY":"CTS"},{"ALLEY":"COVE","ALY":"CV"},{"ALLEY":"COVES","ALY":"CVS"},{"ALLEY":"CREEK","ALY":"CRK"},{"ALLEY":"CRESCENT","ALY":"CRES"},{"ALLEY":"CREST","ALY":"CRST"},{"ALLEY":"CROSSING","ALY":"XING"},{"ALLEY":"CROSSROAD","ALY":"XRD"},{"ALLEY":"CROSSROADS","ALY":"XRDS"},{"ALLEY":"CURVE","ALY":"CURV"},{"ALLEY":"DALE","ALY":"DL"},{"ALLEY":"DAM","ALY":"DM"},{"ALLEY":"DIVIDE","ALY":"DV"},{"ALLEY":"DRIVE","ALY":"DR"},{"ALLEY":"DRIVES","ALY":"DRS"},{"ALLEY":"ESTATE","ALY":"EST"},{"ALLEY":"ESTATES","ALY":"ESTS"},{"ALLEY":"EXPRESSWAY","ALY":"EXPY"},{"ALLEY":"EXTENSION","ALY":"EXT"},{"ALLEY":"EXTENSIONS","ALY":"EXTS"},{"ALLEY":"FALL","ALY":"FALL"},{"ALLEY":"FALLS","ALY":"FLS"},{"ALLEY":"FERRY","ALY":"FRY"},{"ALLEY":"FIELD","ALY":"FLD"},{"ALLEY":"FIELDS","ALY":"FLDS"},{"ALLEY":"FLAT","ALY":"FLT"},{"ALLEY":"FLATS","ALY":"FLTS"},{"ALLEY":"FORD","ALY":"FRD"},{"ALLEY":"FORDS","ALY":"FRDS"},{"ALLEY":"FOREST","ALY":"FRST"},{"ALLEY":"FORGE","ALY":"FRG"},{"ALLEY":"FORGES","ALY":"FRGS"},{"ALLEY":"FORK","ALY":"FRK"},{"ALLEY":"FORKS","ALY":"FRKS"},{"ALLEY":"FORT","ALY":"FT"},{"ALLEY":"FREEWAY","ALY":"FWY"},{"ALLEY":"GARDEN","ALY":"GDN"},{"ALLEY":"GARDENS","ALY":"GDNS"},{"ALLEY":"GATEWAY","ALY":"GTWY"},{"ALLEY":"GLEN","ALY":"GLN"},{"ALLEY":"GLENS","ALY":"GLNS"},{"ALLEY":"GREEN","ALY":"GRN"},{"ALLEY":"GREENS","ALY":"GRNS"},{"ALLEY":"GROVE","ALY":"GRV"},{"ALLEY":"GROVES","ALY":"GRVS"},{"ALLEY":"HARBOR","ALY":"HBR"},{"ALLEY":"HARBORS","ALY":"HBRS"},{"ALLEY":"HAVEN","ALY":"HVN"},{"ALLEY":"HEIGHTS","ALY":"HTS"},{"ALLEY":"HIGHWAY","ALY":"HWY"},{"ALLEY":"HILL","ALY":"HL"},{"ALLEY":"HILLS","ALY":"HLS"},{"ALLEY":"HOLLOW","ALY":"HOLW"},{"ALLEY":"INLET","ALY":"INLT"},{"ALLEY":"ISLAND","ALY":"IS"},{"ALLEY":"ISLANDS","ALY":"ISS"},{"ALLEY":"ISLE","ALY":"ISLE"},{"ALLEY":"JUNCTION","ALY":"JCT"},{"ALLEY":"JUNCTIONS","ALY":"JCTS"},{"ALLEY":"KEY","ALY":"KY"},{"ALLEY":"KEYS","ALY":"KYS"},{"ALLEY":"KNOLL","ALY":"KNL"},{"ALLEY":"KNOLLS","ALY":"KNLS"},{"ALLEY":"LAKE","ALY":"LK"},{"ALLEY":"LAKES","ALY":"LKS"},{"ALLEY":"LAND","ALY":"LAND"},{"ALLEY":"LANDING","ALY":"LNDG"},{"ALLEY":"LANE","ALY":"LN"},{"ALLEY":"LIGHT","ALY":"LGT"},{"ALLEY":"LIGHTS","ALY":"LGTS"},{"ALLEY":"LOAF","ALY":"LF"},{"ALLEY":"LOCK","ALY":"LCK"},{"ALLEY":"LOCKS","ALY":"LCKS"},{"ALLEY":"LODGE","ALY":"LDG"},{"ALLEY":"LOOP","ALY":"LOOP"},{"ALLEY":"MALL","ALY":"MALL"},{"ALLEY":"MANOR","ALY":"MNR"},{"ALLEY":"MANORS","ALY":"MNRS"},{"ALLEY":"MEADOW","ALY":"MDW"},{"ALLEY":"MEADOWS","ALY":"MDWS"},{"ALLEY":"MEWS","ALY":"MEWS"},{"ALLEY":"MILL","ALY":"ML"},{"ALLEY":"MILLS","ALY":"MLS"},{"ALLEY":"MISSION","ALY":"MSN"},{"ALLEY":"MOTORWAY","ALY":"MTWY"},{"ALLEY":"MOUNT","ALY":"MT"},{"ALLEY":"MOUNTAIN","ALY":"MTN"},{"ALLEY":"MOUNTAINS","ALY":"MTNS"},{"ALLEY":"NECK","ALY":"NCK"},{"ALLEY":"ORCHARD","ALY":"ORCH"},{"ALLEY":"OVAL","ALY":"OVAL"},{"ALLEY":"OVERPASS","ALY":"OPAS"},{"ALLEY":"PARK","ALY":"PARK"},{"ALLEY":"PARKS","ALY":"PARK"},{"ALLEY":"PARKWAY","ALY":"PKWY"},{"ALLEY":"PARKWAYS","ALY":"PKWY"},{"ALLEY":"PASS","ALY":"PASS"},{"ALLEY":"PASSAGE","ALY":"PSGE"},{"ALLEY":"PATH","ALY":"PATH"},{"ALLEY":"PIKE","ALY":"PIKE"},{"ALLEY":"PINE","ALY":"PNE"},{"ALLEY":"PINES","ALY":"PNES"},{"ALLEY":"PLACE","ALY":"PL"},{"ALLEY":"PLAIN","ALY":"PLN"},{"ALLEY":"PLAINS","ALY":"PLNS"},{"ALLEY":"PLAZA","ALY":"PLZ"},{"ALLEY":"POINT","ALY":"PT"},{"ALLEY":"POINTS","ALY":"PTS"},{"ALLEY":"PORT","ALY":"PRT"},{"ALLEY":"PORTS","ALY":"PRTS"},{"ALLEY":"PRAIRIE","ALY":"PR"},{"ALLEY":"RADIAL","ALY":"RADL"},{"ALLEY":"RAMP","ALY":"RAMP"},{"ALLEY":"RANCH","ALY":"RNCH"},{"ALLEY":"RAPID","ALY":"RPD"},{"ALLEY":"RAPIDS","ALY":"RPDS"},{"ALLEY":"REST","ALY":"RST"},{"ALLEY":"RIDGE","ALY":"RDG"},{"ALLEY":"RIDGES","ALY":"RDGS"},{"ALLEY":"RIVER","ALY":"RIV"},{"ALLEY":"ROAD","ALY":"RD"},{"ALLEY":"ROADS","ALY":"RDS"},{"ALLEY":"ROUTE","ALY":"RTE"},{"ALLEY":"ROW","ALY":"ROW"},{"ALLEY":"RUE","ALY":"RUE"},{"ALLEY":"RUN","ALY":"RUN"},{"ALLEY":"SHOAL","ALY":"SHL"},{"ALLEY":"SHOALS","ALY":"SHLS"},{"ALLEY":"SHORE","ALY":"SHR"},{"ALLEY":"SHORES","ALY":"SHRS"},{"ALLEY":"SKYWAY","ALY":"SKWY"},{"ALLEY":"SPRING","ALY":"SPG"},{"ALLEY":"SPRINGS","ALY":"SPGS"},{"ALLEY":"SPUR","ALY":"SPUR"},{"ALLEY":"SPURS","ALY":"SPUR"},{"ALLEY":"SQUARE","ALY":"SQ"},{"ALLEY":"SQUARES","ALY":"SQS"},{"ALLEY":"STATION","ALY":"STA"},{"ALLEY":"STRAVENUE","ALY":"STRA"},{"ALLEY":"STREAM","ALY":"STRM"},{"ALLEY":"STREET","ALY":"ST"},{"ALLEY":"STREETS","ALY":"STS"},{"ALLEY":"SUMMIT","ALY":"SMT"},{"ALLEY":"TERRACE","ALY":"TER"},{"ALLEY":"THROUGHWAY","ALY":"TRWY"},{"ALLEY":"TRACE","ALY":"TRCE"},{"ALLEY":"TRACK","ALY":"TRAK"},{"ALLEY":"TRAFFICWAY","ALY":"TRFY"},{"ALLEY":"TRAIL","ALY":"TRL"},{"ALLEY":"TRAILER","ALY":"TRLR"},{"ALLEY":"TUNNEL","ALY":"TUNL"},{"ALLEY":"TURNPIKE","ALY":"TPKE"},{"ALLEY":"UNDERPASS","ALY":"UPAS"},{"ALLEY":"UNION","ALY":"UN"},{"ALLEY":"UNIONS","ALY":"UNS"},{"ALLEY":"VALLEY","ALY":"VLY"},{"ALLEY":"VALLEYS","ALY":"VLYS"},{"ALLEY":"VIADUCT","ALY":"VIA"},{"ALLEY":"VIEW","ALY":"VW"},{"ALLEY":"VIEWS","ALY":"VWS"},{"ALLEY":"VILLAGE","ALY":"VLG"},{"ALLEY":"VILLAGES","ALY":"VLGS"},{"ALLEY":"VILLE","ALY":"VL"},{"ALLEY":"VISTA","ALY":"VIS"},{"ALLEY":"WALK","ALY":"WALK"},{"ALLEY":"WALKS","ALY":"WALK"},{"ALLEY":"WALL","ALY":"WALL"},{"ALLEY":"WAY","ALY":"WAY"},{"ALLEY":"WAYS","ALY":"WAYS"},{"ALLEY":"WELL","ALY":"WL"},{"ALLEY":"WELLS","ALY":"WLS"}]';
		}else
		{
			$suffixesJsonFile = file_get_contents($filePath);
		}

       	$suffixesJson = json_decode($suffixesJsonFile);
       	$array = [];

       	foreach ($suffixesJson as $row) 
       	{
       		$lbl = ucfirst(strtolower($row->ALLEY));
       		$key = ucfirst(strtolower($row->ALY));

       		if($labelAsKey)
       		{
       			$array[$lbl] = $key;
       		}else
       		{
       			$array[$key] = $lbl;
       		}
       	}

       	return $array;
	}

	public static function centerAddrSuffixes($address)
	{
		$arr = [
			"N" =>	"North",
			"E" =>	"East",
			"S" =>	"South",
			"W" =>	"West",
			"NE" => "Northeast",
			"NW" => "Northwest",
			"SE" => "Southeast",
			"SW" => "Southwest"];

		foreach($arr as $k => $v)
		{
			$address = str_ireplace($v . " ", $k . " ", $address);
			$address = str_ireplace(" " . $v, " ". $k, $address);
		}

		return $address;
	}
}