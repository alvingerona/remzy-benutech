<?php

namespace CaffeineInteractive\Remzy\App\PropLoader;

use App\Search;

/**
 * Base loader for Property/Properties in an API.
 */
class PropLoader
{
	/**
	 * Contain conditions for filtering/sorting on API.
	 * 
	 * string|array.
	 */
	protected $query;

	protected $response;

	protected $errors;

	protected $pageNumber;

	protected $pageLimit;

	public function __construct($request = null, $limit = 10, $page = 0)
	{
		$this->setPageLimit($limit); 
		$this->setPageNumber($page); 

		$this->setQuery($request, $this->getPageLimit(), $this->getPageNumber());
		
		$this->response = null;
		$this->errors = null;
	}

	public function getPageLimit()
	{
		return $this->pageLimit;
	}

	public function getPageNumber()
	{
		return $this->pageNumber;
	}

	public function setPageLimit($int)
	{
		$this->pageLimit = $int;

		return $this;		
	}

	/**
	 * Sets the page number.
	 *
	 * @param      interger  $int    The page number
	 *
	 * @return     self
	 */
	public function setPageNumber($int)
	{
		$this->pageNumber = $int;

		return $this;
	}

	/**
	 * Sets the errors.
	 *
	 * @param      array  $errors  The errors
	 * 
	 * @return self
	 */
	public function setErrors($errors)
	{
		$this->errors = $errors;

		return $this;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * This should be called after sending API request.
	 */
	public function afterSend()
	{
		$collection = $this->collectionClass()->setProperties($this->getProperties())->processResult();
		$end = $this->pageLimit * $this->pageNumber;

		$response = new \stdClass();
		$response->collection = $collection;
		$response->pageEnd = ( $end > $this->getResponseTotalPage() ? $this->getResponseTotalPage() : $end );

		$response->totalPages = $this->getResponseTotalPage();

		return $response;
	}

    public function getQuery()
    {
    	return $this->query;
    }

    /**
     * Saves a search.
     *
     * @param      string  $address   The address
     * @param      Property|null  $property  The property
     *
     * @return     Search
     */
    public function saveSearch($address, $property){

        $search = new Search;
        $search->address = $address;
        if(is_null($property)){
            $search->report = 'n';
        }else{
            $search->report = 'y';
        }
        $search->save();

        return $search;
    }	    
}