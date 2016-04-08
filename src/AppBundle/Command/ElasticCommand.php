<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sear:elastic')
            ->setDescription('Sear command line')
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'Acceptable action values are :index|create'
            );
        
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');


        $action = $input->getArgument('action');
        if($action == 'index') {
            $this->populateIndexFromCSV($input, $output);
        }
        if($action == 'create') {
            $this->createIndex($input, $output);
        }


        $now = new \DateTime();
        $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');
    }

    protected function populateIndexFromCSV(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Starting index population from CSV file</comment>');
        $data = $this->get($input, $output);

        //Get elasticsearch service
        $es = $this->getContainer()->get('app.elasticsearch');
       

        foreach($data as $row) {
           if($row['product_name']) {
               
               $params = [
                 'index' => 'foodfacts',
                 'type' => 'product',
                 'id' => $row['code'],
                 'body' => [
                     'product_name'     => $row['product_name'],
                     'quantity'         => $row['quantity'],
                     'brands'           => explode(',',$row['brands']),
                     'categories_fr'    => explode(',',$row['categories_fr']),
                     'origins_fr'       => explode(',',$row['origins']),
                     'labels_fr'        => explode(',',$row['labels']),
                     'countries_fr'     => explode(',',$row['countries_fr']),
                     'ingredients_text' => $row['ingredients_text']
                     
                 ]
               ];
               $response = $es->index($params);
               $output->writeln('<info>Indexation de : '.json_encode($params).'</info>');
               $output->writeln('<info>Indexation, résultat : '.json_encode($response).'</info>');
           }
        }
        //$output->writeln(print_r($data, true));

    }

    protected function get(InputInterface $input, OutputInterface $output)
    {
        $fileName = 'web/upload/fr.openfoodfacts.org.products.10.csv';
         // Using service for converting CSV to PHP Array
        $cta = $this->getContainer()->get('app.csvtoarray');
        $data = $cta->convert($fileName, "\t");

        return $data;
    }

    protected function createIndex(InputInterface $input, OutputInterface $output)
    {
        $es = $this->getContainer()->get('app.elasticsearch');
        $response = $es->createIndex();
        $output->writeln('<info> Result : '.json_encode($response).'</info>');
    }


}


