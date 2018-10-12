<?php

namespace CaffeineInteractive\Remzy\App\Boundary;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use CaffeineInteractive\Remzy\App\Boundary\Coordinate;

/**
 * Class for boundary. Base wrapper for displaying zip geometry details.
 */
class Boundary
{

//	protected $coordinates;

	protected $zip;

	protected $centerCoordinate;

	protected $coorGroups; // Array of Groups of coordinates.

	public function __construct($zip)
	{
		$this->setZip($zip);
	//	$this->coordinates = collect([]);
		$this->coorGroups = [];

		//Setup zip coordinates.
		$this->load();
	}

	/**
	 * Add group of coordinates.
	 * 
	 * @param $arr coordinates
	 * @return  self
	 */
	public function addCoorGroup($arr)
	{
		$this->coorGroups[] = $arr;

		return $this;
	}

	/**
	 * @return Collection
	 */
	public function groupCoordinates()
	{
		return $this->coorGroups;
	}

	/**
	 * Gets the zip.
	 *
	 * @return     integer  The zip.
	 */
	public function getZip()
	{
		return $this->zip;
	}

	/**
	 * Sets the zip.
	 *
	 * @param      integer  $zip    The zip
	 *
	 * @return     self
	 */
	public function setZip($zip)
	{
		$this->zip = $zip;

		return $this;
	}

	/**
	 * @param      integer  $zip    The zip
	 * 
	 * @return self
	 */
	public static function get($zip)
	{
		$class = get_called_class();
		$areaZip = new $class($zip);

		return $areaZip;
	}

	/**
	 * Returns a array representation of the object.
	 *
	 * @return     array  String representation of the object.
	 */
	public function groupCoordinatesToArray()
	{
		$arr = [];
		foreach ($this->groupCoordinates() as $group) {
			$groupCoorArr = [];
			foreach ($group as $coor) {
				$groupCoorArr[] = $coor->toArray();
			}
			$arr[] = $groupCoorArr;
		}

		return $arr;
	}

	public function getCenterCoor()
	{
		return $this->centerCoordinate;
	}

	public function setCenterCoor($long, $lat)
	{
		$this->centerCoordinate = Coordinate::set($long, $lat);

		return $this;
	}

	public function centerCoorToArray()
	{
		if(!$this->centerCoordinate)
		{
			return null;
		}

		return $this->centerCoordinate->toArray();
	}
}