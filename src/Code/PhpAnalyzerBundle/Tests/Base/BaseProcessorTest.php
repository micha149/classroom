<?php

namespace Code\PhpAnalyzerBundle\Tests\Base;

use Code\AnalyzerBundle\Result\Result;
use Code\PhpAnalyzerBundle\Base\BaseProcessor;
use Code\PhpAnalyzerBundle\Node\PhpClassNode;
use Code\PhpAnalyzerBundle\Pdepend\PdependProcessor;
use org\bovigo\vfs\vfsStream;

class BaseProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        vfsStream::setup('root', 0777, array('file1.php' => 'class1', 'file2.php' => 'class2'));

        $reflectionServiceMock = $this->getMockBuilder('Code\PhpAnalyzerBundle\ReflectionService')
            ->disableOriginalConstructor()
            ->getMock();

        $reflectionServiceMock
            ->expects($this->any())
            ->method('getClassnameForFile')
            ->will($this->returnArgument(0));

        $processor = new BaseProcessor($reflectionServiceMock);

        $result = new Result();

        $data = array(
            vfsStream::url('root/file1.php'),
            vfsStream::url('root/file2.php'),
        );

        $processor->process($result, $data);

        return $result;
    }

    /**
     * @depends testProcess
     * @param Result $result
     */
    public function testFile1(Result $result)
    {
        $this->assertTrue($result->hasNode(vfsStream::url('root/file1.php')));
    }

    /**
     * @depends testProcess
     * @param Result $result
     */
    public function testFile2(Result $result)
    {
        $this->assertTrue($result->hasNode(vfsStream::url('root/file2.php')));
    }
}
