<?php

namespace AppBundle\Services;

use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    protected $client;

    public function __construct($hosts)
    {
        if (!is_array($hosts)) {
            $hosts = (array) $hosts;
        }

        $this->client = ClientBuilder::create()->setHosts($hosts)->build();
    }

    public function index(array $params)
    {
        return $this->client->index($params);
    }

    /**
     * Get search suggestion for term
     * @param string $term
     * @return array Elastic search response
     */
    public function getSuggestions(string $term)
    {
        $params = [
            'index' => 'foodfacts',
            'type' => 'product',
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'match' => [
                                'product_name.ngrams' => [
                                    'query' => $term,
                                    'operator' => 'and'
                                ]
                            ],
                            'match' => [
                                'desc_produit' => [
                                    'query' => $term
                                ]
                            ]
                        ]
                    ]
                ],
                'suggest' => [
                    'text' => $term,
                    'title-suggest' => [
                        'phrase' => [
                            'field' => 'product_name.shingles',
                            'size' => 1
                        ]
                    ]
                ],
                'aggs' => [
                    'search_suggestions' => [
                        'terms' => [
                            'field' => 'product_name.shingles',
                            'include' => '*'.$term.'*'
                        ]
                    ]
                ]
            ]
        ];
        echo json_encode($params);
        exit;
        return $this->client->search($params);
    }

    public function createIndex()
    {
        $params = [
            'index' => 'foodfacts',
            'body' => [
                'mappings' => [
                    '_default_' => [
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'fields' => [
                                    'raw' => [
                                        'type' => 'string',
                                        'index' => 'not_analyzed'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $this->client->indices()->create($params);
    }
}