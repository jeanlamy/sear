<?php

namespace AppBundle\Services;

use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    protected $client;
    protected $indexName;

    public function __construct($hosts)
    {
        if (!is_array($hosts)) {
            $hosts = (array) $hosts;
        }

        $this->client = ClientBuilder::create()->setHosts($hosts)->build();

        $this->indexName = 'foodfacts';
    }

    /**
     * Index tableau de données
     * @param array $data les données à indexer
     * @param int $bulk_step le nombre de lignes à insérer en mode bulk
     * @return type
     */
    public function index(array $data, int $bulk_step = 1000)
    {
        $i      = 0;
        $params = ['body' => []];
        foreach ($data as $row) {
            if ($row['product_name']) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->indexName,
                        '_type' => 'product',
                        '_id' => $row['code']
                    ]
                ];
                $params['body'][] = [

                    'product_name' => $row['product_name'],
                    'quantity' => $row['quantity'],
                    'brands' => explode(',', $row['brands']),
                    'categories_fr' => explode(',', $row['categories_fr']),
                    'origins_fr' => explode(',', $row['origins']),
                    'labels_fr' => explode(',', $row['labels']),
                    'countries_fr' => explode(',', $row['countries_fr']),
                    'ingredients_text' => $row['ingredients_text']
                ];
                if ($i % 100 == 0) {
                    $responses = $this->client->bulk($params);
                    // erase the old bulk request
                    $params    = ['body' => []];
                    // unset the bulk response when you are done to save memory
                    unset($responses);
                }

                $i++;
            }
        }
        // Send the last batch if it exists
        if (!empty($params['body'])) {
            $responses = $this->client->bulk($params);
        }
        return $responses;
    }

    /**
     * Get search suggestion for term
     * @param string $term
     * @return array Elastic search response
     */
    public function getSuggestions(string $term)
    {
        $params = [
            'index' => $this->indexName,
            'body' => [

                'suggestions' => [
                    'text' => $term,
                    'completion' => [
                        'field' => 'product_name.suggest'
                    ]
                ]
            ]
        ];

        return $this->client->suggest($params);
    }

    public function createIndex()
    {
        $params = [
            'index' => $this->indexName,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'french_elision' => [
                                'type' => 'elision',
                                'articles_case' => true,
                                'articles' => [
                                    'l', 'm', 't', 'qu', 'n', 's', 'j', 'd', 'c',
                                    'jusqu', 'quoiqu', 'lorsqu', 'puisqu'
                                ]
                            ],
                            'french_stop' => [
                                'type' => 'stop',
                                'stopwords' => '_french_'
                            ],
                            /* 'french_keywords' => [
                              'type' => 'keyword_marker',
                              'keywords' => []
                              ], */
                            'french_stemmer' => [
                                'type' => 'stemmer',
                                'language' => 'light_french'
                            ]
                        ],
                        'analyzer' => [
                            'french_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'french_elision',
                                    'lowercase',
                                    'french_stop',
                                    /*    'french_keywords', */
                                    'french_stemmer'
                                ]
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    '_default_' => [
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'fields' => [
                                    'raw' => [
                                        'type' => 'string',
                                        'index' => 'not_analyzed'
                                    ],
                                    'analyzed' => [
                                        'type' => 'string',
                                        'analyzer' => 'french_analyzer'
                                    ],
                                    'suggest' => [
                                        'type' => 'completion',
                                        'payloads' => true
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

    public function deleteIndex()
    {
        $params = ['index' => $this->indexName];
        return $this->client->indices()->delete($params);
    }
}