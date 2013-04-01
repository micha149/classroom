<?php

namespace Classroom\ProjectBundle\Command;

use Classroom\ProjectBundle\Builder;
use Classroom\ProjectBundle\Entity\Revision;
use JMS\JobQueueBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends ContainerAwareCommand
{
    protected $name;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('classroom:project:build')
            ->setDescription('Build revision')
            ->addArgument('revisionId', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $revisionId = $input->getArgument('revisionId');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /* @var $entityManager \Doctrine\ORM\EntityManager */

        $builder = $this->getContainer()->get('classroom.project.builder');
        /* @var $builder Builder */

        $revisionRepository = $entityManager->getRepository('Classroom\ProjectBundle\Entity\Revision');
        $revision = $revisionRepository->find($revisionId);

        $builder->build($revision);

        $entityManager->flush($revision);

        $output->writeln('Revision id ' . $revisionId . ' built.');
    }
}
