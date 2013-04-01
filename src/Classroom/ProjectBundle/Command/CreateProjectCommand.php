<?php

namespace Classroom\ProjectBundle\Command;

use Classroom\ProjectBundle\Entity\Project;
use Classroom\RepositoryBundle\Entity\RepositoryConfig;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProjectCommand extends ContainerAwareCommand
{
    protected $name;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('classroom:project:create-project')
            ->setDescription('Create project')
            ->addArgument('key', InputArgument::REQUIRED)
            ->addArgument('name', InputArgument::REQUIRED)
            ->addArgument('type', InputArgument::REQUIRED)
            ->addArgument('url', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectKey = $input->getArgument('key');
        $projectName = $input->getArgument('name');
        $type = $input->getArgument('type');
        $url = $input->getArgument('url');

        $repositoryConfig = new RepositoryConfig();
        $project = new Project();

        $repositoryConfig
            ->setType($type)
            ->setUrl($url)
            ->setProject($project);

        $project
            ->setKey($projectKey)
            ->setName($projectName)
            ->setRepositoryConfig($repositoryConfig);

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /* @var $entityManager EntityManager */

        $entityManager->persist($repositoryConfig);
        $entityManager->persist($project);

        $entityManager->flush();

        $output->writeln('<info>New project ' . $projectKey . ' created.</info>');
    }
}
