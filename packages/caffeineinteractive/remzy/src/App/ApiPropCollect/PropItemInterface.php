<?php

namespace CaffeineInteractive\Remzy\App\ApiPropCollect;

interface PropItemInterface
{

    /**
     * @return mixed
     */
    public function getAddress();

    /**
     * @param mixed $address
     *
     * @return self
     */
    public function setAddress($address);

    /**
     * @return mixed
     */
    public function getCity();

    /**
     * @param mixed $city
     *
     * @return self
     */
    public function setCity($city);

    /**
     * @return mixed
     */
    public function getBeds();

    /**
     * @param mixed $beds
     *
     * @return self
     */
    public function setBeds($beds);

    /**
     * @return mixed
     */
    public function getBaths();

    /**
     * @param mixed $baths
     *
     * @return self
     */
    public function setBaths($baths);

    /**
     * @return mixed
     */
    public function getSqft();

    /**
     * @param mixed $sqft
     *
     * @return self
     */
    public function setSqft($sqft);

    /**
     * @return mixed
     */
    public function getLotSize();

    /**
     * @param mixed $lotSize
     *
     * @return self
     */
    public function setLotSize($lotSize);

    /**
     * @return mixed
     */
    public function getLastSold();

    /**
     * @param mixed $lastSold
     *
     * @return self
     */
    public function setLastSold($lastSold);

    /**
     * @return mixed
     */
    public function getAmountLastSold();

    /**
     * @param mixed $amountLastSold
     *
     * @return self
     */
    public function setAmountLastSold($amountLastSold);

    /**
     * @return mixed
     */
    public function getEstimatedValue();

    /**
     * @param mixed $estimatedValue
     *
     * @return self
     */
    public function setEstimatedValue($estimatedValue);

    /**
     * @return mixed
     */
    public function getDwellingType();

    /**
     * @param mixed $dwellingType
     *
     * @return self
     */
    public function setDwellingType($dwellingType);

    public function setFromModel($property);

    public static function createFromModel($property);

    public function setCoordinates($long, $lat);

    public function getCoordinates();

    public function getArrayCoordinates();

    public function getMortgageDt();

    public function getMortgageAmt();

    public function getOwnerOccupied();

    public function setMortgageDt($value);

    public function setMortgageAmt($value);

    public function setOwnerOccupied($value);    

    public function getPreMoverScore();

    public function setPreMoveScore($value);
}