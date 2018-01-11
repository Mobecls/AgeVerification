<?php

namespace Magenmagic\AgeVerification\Model\EVSRequest;

interface AbstractFactoryInterface
{
    /**
     * @return RequestObject
     */
    public function createRequest();
}
