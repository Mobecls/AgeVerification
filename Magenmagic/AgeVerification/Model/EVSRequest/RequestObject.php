<?php

namespace Magenmagic\AgeVerification\Model\EVSRequest;

use Magento\Framework\DataObject;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class RequestObject extends DataObject
{
    /**
     * @return string
     */
    public function getFirstName()
    {
        return parent::getFirstName();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFirstName($value)
    {
        return parent::setFirstName($value);
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return parent::getMiddleName();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMiddleName($value)
    {
        return parent::setMiddleName($value);
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return parent::getLastName();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLastName($value)
    {
        return parent::setLastName($value);
    }

    /**
     * @return string
     */
    public function getState()
    {
        return parent::getState();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setState($value)
    {
        return parent::setState($value);
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return parent::getCountry();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCountry($value)
    {
        return parent::setCountry($value);
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return parent::getCity();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCity($value)
    {
        return parent::setCity($value);
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return parent::getStreet();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStreet($value)
    {
        return parent::setStreet($value);
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return parent::getZipCode();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setZipCode($value)
    {
        return parent::setZipCode($value);
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return parent::getDateOfBirth();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDateOfBirth($value)
    {
        return parent::setDateOfBirth($value);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return parent::getEmail();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setEmail($value)
    {
        return parent::setEmail($value);
    }
}