<?php

namespace Code\PhpAnalyzerBundle\Pdepend;

use Code\AnalyzerBundle\Analyzer\Processor\ProcessorInterface;
use Code\AnalyzerBundle\Model\MetricModel;
use Code\AnalyzerBundle\ReflectionService;
use Code\AnalyzerBundle\ResultBuilder;

class PdependProcessor implements ProcessorInterface
{
    /**
     * @var ReflectionService
     */
    private $reflectionService;

    /**
     * @param ReflectionService $reflectionService
     */
    public function __construct(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * @inheritDoc
     */
    public function process(ResultBuilder $resultBuilder, $filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception('pdepend summary xml file not found.');
        }

        $xml = simplexml_load_file($filename);

        $metricsAttributes = $xml->attributes();
        $metricsMetrics = array();
        foreach ($metricsAttributes as $metricKey => $metricValue) {
            $metricsMetrics[$metricKey] = (string)$metricValue;
        }
        //$generated = new \DateTime($metricsMetrics['generated']);
        //$pdepend = (string)$metricsMetrics['pdepend'];
        unset($metricsMetrics['generated'], $metricsMetrics['pdepend']);

        foreach ($xml->package as $packageNode) {
            $packageAttributes = $packageNode->attributes();
            $packageMetrics = array();
            foreach ($packageAttributes as $packageKey => $packageValue) {
                $packageMetrics[$packageKey] = (string)$packageValue;
            }
            $packageName = $packageMetrics['name'];
            unset($packageMetrics['name']);

            foreach ($packageNode->class as $classNode) {
                $classAttributes = $classNode->attributes();
                $classMetrics = array();
                foreach ($classAttributes as $classKey => $classValue) {
                    $classMetrics[$classKey] = (string)$classValue;
                }
                $className = $classMetrics['name'];
                unset($classMetrics['name']);

                $fileAttributes = $classNode->file->attributes();
                //$fileName = (string)$fileAttributes['name'];

                $classResultNode = $resultBuilder->getNode($packageName . '\\' . $className);

                $lines = $classMetrics['loc'];
                $linesOfCode = $classMetrics['eloc'];
                $methods = $classMetrics['nom'];
                $linesOfCodePerMethod = $methods ? $linesOfCode / $methods : 0;
                $complexity = $classMetrics['wmcnp'];
                $complexityPerMethod = $methods ? $complexity / $methods : 0;

                $classResultNode->addMetric(new MetricModel('lines', $lines));
                $classResultNode->addMetric(new MetricModel('linesOfCode', $linesOfCode));

                $classResultNode->addMetric(new MetricModel('methods', $methods));
                $classResultNode->addMetric(new MetricModel('linesOfCodePerMethod', $linesOfCodePerMethod));

                $classResultNode->addMetric(new MetricModel('complexity', $complexity));
                $classResultNode->addMetric(new MetricModel('complexityPerMethod', $complexityPerMethod));

                /*
                if ($classMetrics['wmc'] >= 10) {
                    $classSource = $this->reflectionService->getClassSource($fileName, $class->getFullQualifiedName());

                    $smell = new SmellModel('metrics', 'High overall complexity', $classSource, 1);
                    $class->addSmell($smell);
                }
                */

                /*
                foreach ($classNode->method as $methodNode) {
                    $methodAttributes = $methodNode->attributes();
                    $methodMetrics = array();
                    foreach ($methodAttributes as $methodAttributeKey => $methodAttributeValue) {
                        $methodMetrics[$methodAttributeKey] = (string)$methodAttributeValue;
                    }
                    $methodName = $methodMetrics['name'];
                    unset($methodMetrics['name']);
                }
                */
            }
        }
    }
}
