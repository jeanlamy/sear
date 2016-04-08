<?php
namespace AppBundle\Services;
use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    protected $client;

    public function __construct($hosts)
    {
        if(!is_array($hosts)) {
            $hosts = (array)$hosts;
        }

        $this->client = ClientBuilder::create()->setHosts($hosts)->build();
        
    }

    public function index(array $params)
    {
        return $this->client->index($params);
    }


}

