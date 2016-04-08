<?php
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AppBundle\Command\ElasticCommand;


class ElasticCommandTest extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    public function testExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new ElasticCommand());

        $command = $application->find('sear:elastic');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'action' => 'index'
            )
            );

        $this->assertContains('You choose to index', $commandTester->getDisplay());

    }
}


