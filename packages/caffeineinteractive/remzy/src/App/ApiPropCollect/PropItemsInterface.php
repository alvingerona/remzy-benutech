<?php

namespace CaffeineInteractive\Remzy\App\ApiPropCollect;

interface PropItemsInterface
{
	public function processResult();

	public function setProperties($collect);

	public function getProperties();

	public function getProcessProperties();
}