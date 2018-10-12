<?php

namespace CaffeineInteractive\Remzy\App\Boundary;

/**
 * Will hold long and lat.
 */
class Coordinate 
{
	protected $longtitude;

	protected $latitude;

	public static function set($long, $lat)
	{
		$coor = new Coordinate;

		$coor->setLongtitude($long);
		$coor->setLatitude($lat);

		return $coor;
	}

    /**
     * @return mixed
     */
    public function getLongtitude()
    {
        return $this->longtitude;
    }

    /**
     * @param mixed $longtitude
     *
     * @return self
     */
    public function setLongtitude($longtitude)
    {
        $this->longtitude = $longtitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     *
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function toArray()
    {
        return [
            'lat' => $this->getLatitude(),
            'lng' => $this->getLongtitude()
        ];
    }
}