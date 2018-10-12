<?php

namespace CaffeineInteractive\Remzy\App\PropLoader;

interface PropLoaderInterface
{
	/**
	 * Set query for pulling API data.
	 *
	 * @param $request Request
	 * @param @pageLimit integer
	 * @param @pageNumber integer
	 *
	 * @return $this
	 */
	public function setQuery($request, $pageLimit, $pageNumber);

	/**
	 * Sets the errors.
	 *
	 * @param      array  $errors  The errors
	 */
	public function setErrors($errors);

	/**
	 * Action for sending request to API.
	 */
	public function send();

	/**
	 * laterQuery()
	 *
	 * @param      integer  $page      The page
	 * @param      integer  $pagesize  The pagesize
	 * 
	 * @return array of query
	 */
	public function laterQuery($page, $pagesize);

	public function collectionClass();

	public function getProperties();

	public function getResponseTotalPage();

	public function getFirstProperty();

	// This is for OnBoard use only.
	public function setAction($str);

	// This is for OnBoard use only.
	public function toSnapshot($bool);
}