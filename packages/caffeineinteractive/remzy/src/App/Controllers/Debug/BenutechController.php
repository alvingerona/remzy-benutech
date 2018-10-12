<?php

namespace CaffeineInteractive\Remzy\App\Controllers\Debug;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

use CaffeineInteractive\Remzy\App\BenuTech\BenuTech;
use CaffeineInteractive\Remzy\App\PropLoader\PropLoad;

/**
 * Query params:
 * - start=60
 * - length=30
 * - estimated-value[min]=150000
 * - estimated-value[max]=9000000
 * 
 * Sample query param:
 * ?search-key=95037&estimated-value%5Bmin%5D=150000&estimated-value%5Bmax%5D=9000000
 *     
 */

class BenutechController extends BaseController
{
	public function index(Request $request)
	{
		$search = trim($request->get('search-key'));
		$request->request->add(['postalcode' => $search]);	
		$propApiLoader = PropLoad::load($request, 30, 0, 'loadBenuTech');
		$send = $propApiLoader->send();

		$results = [
			/**
			 * Query sent to benutech API
			 */
			"query" => $propApiLoader->getQuery(),
			"total" => $propApiLoader->getResponseTotalPage(),
			/**
			 * Manipulated Benutech Data.
			 */
			"default" => $send->collection->getProcessProperties(),
			/**
			 * Non manipulated data from Benutech
			 */
			"allData" => $send->collection->getProcessProperties()->pluck("allData")
		];

		dd($results);
	}
}