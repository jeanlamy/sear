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