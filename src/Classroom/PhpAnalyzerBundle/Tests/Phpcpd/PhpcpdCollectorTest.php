<?php

namespace Classroom\PhpAnalyzerBundle\Tests\Phpcs;

use Classroom\PhpAnalyzerBundle\Phpcpd\PhpcpdCollector;
use org\bovigo\vfs\vfsStream;

class PhpcpdCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        vfsStream::setup('root', 0777, array('sourceDir' => array(), 'workDir' => array()));

        $processExecutorMock = $this->getMockBuilder('Classroom\AnalyzerBundle\ProcessExecutor')
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Log\LoggerInterface')
            ->getMock();

        $collector = new PhpcpdCollector(
            $processExecutorMock,
            $loggerMock,
            'phpcs'
        );

        $processExecutorMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Symfony\Component\Process\Process'));

        $logMock = $this->getMockBuilder('Classroom\AnalyzerBundle\Log\Log')
            ->disableOriginalConstructor()
            ->getMock();

        $filename = $collector->collect($logMock, 'sourceDir', 'workDir');

        $this->assertEquals($filename, 'workDir/phpcpd.xml');
    }
}
