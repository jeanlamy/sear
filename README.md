# sear
Search.io : search and recommandation engine for films, series, books and music

* Film and series database : imdb by using http://imdbpy.sourceforge.net/
* Music database : musicBrainz
* Book database : 


The data will be stored in a MySQL database. 



### Why?

To make experiment over technologies like ElasticSearch, Symfony 3, HHVM, Nginx using Docker.

### What can I do with it ?

Search products information from the OpenFoodFacts database from various angles : product name, ingredient, additive,...

<<<<<<< HEAD
### Usage of OpenFoodFacts database

Elastic Indexation is based on the CSV files from OpenFoodFacts.org : 
  http://fr.openfoodfacts.org/data/fr.openfoodfacts.org.products.csv
Field Mapping can be found here : 
  http://world.openfoodfacts.org/data/data-fields.txt


### How can I run it?

```bash
  # create index
  php bin/console sear:elastic create
  # launch indexation
  php bin/console sear:elastic index
  # delete index
  php bin/console sear:elastic delete
```

### Todo

* Change indexation to load data from MongoDB database
* Add pagination logic to search results
* Add product modal with all informations
* Allow user to search product by entering an additive or ingredient name






