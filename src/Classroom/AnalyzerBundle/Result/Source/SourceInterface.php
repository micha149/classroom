<?php

namespace Classroom\AnalyzerBundle\Result\Source;

use Classroom\AnalyzerBundle\Result\Reference\Referencable;
use Classroom\AnalyzerBundle\Result\Source\Storage\StorageInterface;

interface SourceInterface extends Referencable
{
    /**
     * Set storage
     *
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage);

    /**
     * Return storage
     *
     * @return StorageInterface
     */
    public function getStorage();

    /*+
     * Return content
     *
     * @return string
     */
    public function getContent();

    /*+
     * Return content as array
     *
     * @return string
     */
    public function getContentAsArray();

    /**
     * Return content range
     *
     * @param SourceRange $range
     * @return string
     */
    public function getRange(SourceRange $range);

    /**
     * Return content range as array of lines
     *
     * @param SourceRange $range
     * @return string
     */
    public function getRangeAsArray(SourceRange $range);
}
