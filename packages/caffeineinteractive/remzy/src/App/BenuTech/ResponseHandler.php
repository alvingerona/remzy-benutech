<?php

namespace CaffeineInteractive\Remzy\App\BenuTech;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Cookie\CookieJar;

class ResponseHandler
{    
    protected $response;
    
    public function __construct($response)
    {
        $this->response = $response;
    }

    public function getPaging()
    {
        if(isset($this->response->paging))
        {
            return $this->response->paging;
        }

        return null;
    }

    public function getQuery()
    {
        if(!isset($this->response->data) || !isset($this->response->data->query))
        {
            return null;
        }
        
        return $this->response->data->query;
    }

    public function getRecords()
    {
        if(isset($this->response->data->recs))
        {
            return $this->response->data->recs;
        }

        return null;
    }

    public function hasRecords()
    {
        return ($this->getRecords() ? true : false);
    }

    public function first()
    {
        if(!$this->hasRecords())
        {
            return null;
        }

        return $this->getRecords()[0];
    }

    public function getTotalPage()
    {
      
        return ($this->hasRecords() ? $this->getPaging()->pageCount : 0);
    }

    public function recordsTotal()
    {
        return ($this->hasRecords() ? $this->getPaging()->count : 0);
    }
}