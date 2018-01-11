<?php

namespace Magenmagic\AgeVerification\Model\Upload;

use Magento\Framework\DataObject;

/**
 * Interface StrategyInterface
 */
interface StrategyInterface
{
    /**
     * @return string
     */
    public function getSuccessMessage();

    /**
     * @param DataObject $object
     *
     * @return mixed
     */
    public function prepareEmailParams(DataObject $object);

    /**
     * @return string
     */
    public function getEmailTemplate();

    /**
     * @return boolean
     */
    public function validate();

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @param string $fileName
     *
     * @return void
     */
    public function postUpload($fileName);
}