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
                'action', InputArgument::REQUIRED,
                'Acceptable action values are :index|create|delete'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        $output->writeln('<comment>Start : '.$now->format('d-m-Y G:i:s').' ---</comment>');


        $action = $input->getArgument('action');
        if ($action == 'index') {
            //ini_set('memory_limit', '2048M');
            $this->populateIndexFromCSV($input, $output);
        }
        if ($action == 'create') {

            $this->createIndex($input, $output);
        }
        if ($action == 'delete') {
            $this->deleteIndex($input, $output);
        }


        $now = new \DateTime();
        $output->writeln('<comment>End : '.$now->format('d-m-Y G:i:s').' ---</comment>');
    }

    protected function populateIndexFromCSV(InputInterface $input,
                                            OutputInterface $output)
    {
        $output->writeln('<comment>Starting index population from CSV file</comment>');
        $es     = $this->getContainer()->get('app.elasticsearch');
        $flag   = true;
        $i      = 0;
        $length = 5000;
        while ($flag) {
            $data     = $this->getPartial($input, $output, $i, $length);

            if(count($data)) {
                $response = $es->index($data);
                $output->writeln('<comment>Partial indexation of '.$i.' to '.($i+$length).'</comment>');
                unset($data);
            } else {
                $flag = false;
            }
            $i = $i + $length;
        }
    }

    protected function get(InputInterface $input, OutputInterface $output)
    {
        $fileName = 'web/upload/fr.openfoodfacts.org.products.csv';
        // Using service for converting CSV to PHP Array
        $cta      = $this->getContainer()->get('app.csvtoarray');
        $data     = $cta->convert($fileName, "\t");

        return $data;
    }

    protected function getPartial(InputInterface $input,
                                  OutputInterface $output, $start = 0,
                                  $length = 100)
    {
        $fileName = 'web/upload/fr.openfoodfacts.org.products.csv';
        // Using service for converting CSV to PHP Array
        $cta      = $this->getContainer()->get('app.csvtoarray');
        $data     = $cta->convertPartial($fileName, "\t", $start, $length);

        return $data;
    }

    protected function createIndex(InputInterface $input,
                                   OutputInterface $output)
    {
        $es       = $this->getContainer()->get('app.elasticsearch');
        $response = $es->createIndex();
        $output->writeln('<info> Result : '.json_encode($response).'</info>');
    }

    protected function deleteIndex(InputInterface $input,
                                   OutputInterface $output)
    {
        $es       = $this->getContainer()->get('app.elasticsearch');
        $response = $es->deleteIndex();
        $output->writeln('<info> Result : '.json_encode($response).'</info>');
    }
}