<?php

namespace Code\PhpAnalyzerBundle\Phpcpd;

use Code\AnalyzerBundle\Analyzer\Collector\CollectorInterface;
use Code\AnalyzerBundle\ProcessExecutor;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;

class PhpcpdCollector implements CollectorInterface
{
    /**
     * @var ProcessExecutor
     */
    private $processExecutor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $phpcpdExecutable;

    /**
     * @param ProcessExecutor $processExecutor
     * @param LoggerInterface $logger
     * @param string          $phpcpdExecutable
     */
    public function __construct(ProcessExecutor $processExecutor, LoggerInterface $logger, $phpcpdExecutable)
    {
        $this->processExecutor = $processExecutor;
        $this->logger = $logger;
        $this->phpcpdExecutable = $phpcpdExecutable;
    }

    /**
     * @inheritDoc
     */
    public function collect($sourceDirectory, $workDirectory)
    {
        $phpcpdFilename = $workDirectory . '/phpcpd.xml';
        #return $phpcpdFilename;

        if (file_exists($phpcpdFilename) && !unlink($phpcpdFilename)) {
            throw new \Exception('Can\'t unlink ' . $phpcpdFilename);
        }

        $processBuilder = new ProcessBuilder();
        $processBuilder
            ->add($this->phpcpdExecutable)
            ->add('--log-pmd')
            ->add($phpcpdFilename)
            ->add('--quiet')
            ->add($sourceDirectory);

        $process = $processBuilder->getProcess();

        $this->logger->debug($process->getCommandLine());

        $this->processExecutor->execute($process, 1);

        return $phpcpdFilename;
    }
}
