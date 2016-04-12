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

            //Skip product with no name
            if (!$row['product_name']) {
                continue;
            }

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
                'ingredients_text' => $row['ingredients_text'],
                'image_url' => $row['image_url'],
                'image_small_url' => $row['image_small_url']
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
        // Send the last batch if it exists
        if (!empty($params['body'])) {
            $responses = $this->client->bulk($params);
        }
        return $responses;
    }

    /**
     * Get search suggestion for term
     *
     * @param string $term
     * 
     * @return array Elastic search response
     */
    public function suggest(string $term)
    {
        $params = [
            'index' => $this->indexName,
            'body' => [

                'query' => [
                    'match' => [
                        '_all' => [
                            'query' => $term,
                            'operator' => 'and'
                        ]
                    ]
                ]
            ]
        ];

        return $this->client->search($params);
    }

    /**
     * Get search results
     * 
     * @param string $term
     * @param array $filters optional filters
     * @param int $from first result offset. By default 0
     * @param int $size result set length. By default 10
     */
    public function search(string $term, array $filters = array(),
                           int $from = 0, int $size = 10)
    {


        $params = [
            'index' => $this->indexName,
            'body' => [
                'from' => $from,
                'size' => $size,
                'query' => [

                    'match' => [
                        '_all' => [
                            'query' => $term,
                            'operator' => 'and'
                        ]
                    ]
                ],
                'aggs' => [
                    'categories' => [
                        'filter' => [
                            'match_all' => []
                        ],
                        'aggs' => [
                            'categories' => [
                                'terms' => [
                                    'field' => 'categories_fr'
                                ]
                            ]
                        ]
                    ],
                    'countries' => [

                        'filter' => [
                            'match_all' => []
                        ],
                        'aggs' => [
                            'countries' => [
                                'terms' => [
                                    'field' => 'countries_fr'
                                ]
                            ]
                        ]
                    ],
                    'brands' => [
                        'filter' => [
                            'match_all' => []
                        ],
                        'aggs' => [
                            'brands' => [
                                'terms' => [
                                    'field' => 'brands'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        // if filtering by categories then filter countries aggregation but not categories one
        if ($filters['categories']) {
            $filter                                        = [
                'terms' => [
                    'categories_fr' => $filters['categories']
                ]
            ];
            //filter countries and brands by categories
            $params['body']['aggs']['countries']['filter'] = $filter;
            $params['body']['aggs']['brands']['filter']    = $filter;
            $params['body']['filter']                      = $filter;
        }
        if ($filters['countries']) {

            //if already filtered by categories add filter
            if (isset($filter['terms'])) {
                $filter_term = $filter['terms'];
                $filter      = [
                    'and' => [
                        ['terms' => $filter_term],
                        ['terms' => [
                            'countries_fr' => $filters['countries']
                        ]]
                    ]
                ];

                $params['body']['aggs']['brands']['filter'] = $filter;
                $params['body']['filter']                      = $filter;


            } else {
                $filter = [
                    'terms' => [
                        'countries_fr' => $filters['countries']
                    ]
                ];
                $params['body']['aggs']['brands']['filter'] = $filter;
                $params['body']['filter']                      = $filter;
            }
        }


       # echo json_encode($params['body']);exit;
        return $this->client->search($params);
    }

    public function createIndex()
    {
        $params = [
            'index' => $this->indexName,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'ngram_filter' => [
                                'type' => 'nGram',
                                'min_gram' => 2,
                                'max_gram' => 20,
                                'token_chars' => [
                                    'letter',
                                    'digit',
                                    'punctuation',
                                    'symbol'
                                ]
                            ]
                        ],
                        'analyzer' => [
                            'ngram_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter' => [
                                    'lowercase',
                                    'asciifolding',
                                    'ngram_filter'
                                ]
                            ],
                            'whitespace_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter' => [
                                    'lowercase',
                                    'asciifolding'
                                ]
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'product' => [
                        '_all' => [
                            'analyzer' => 'ngram_analyzer',
                            'search_analyzer' => 'whitespace_analyzer'
                        ],
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ],
                            'quantity' => [
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'include_in_all' => false
                            ],
                            'brands' => [
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ],
                            'categories_fr' => [
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ],
                            'labels_fr' => [
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ],
                            'countries_fr' => [
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'include_in_all' => false
                            ],
                            'ingredients_text' => [
                                'type' => 'string',
                                'index' => 'not_analyzed'
                            ],
                            'image_url' => [
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'include_in_all' => false
                            ],
                            'image_small_url' => [
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'include_in_all' => false
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