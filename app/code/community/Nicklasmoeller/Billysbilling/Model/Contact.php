<?php

/**
 * Class Nicklasmoeller_Billysbilling_Model_Contact
 *
 * @author Nicklas Møller <hello@nicklasmoeller.com>
 * @version 0.2.0
 */
class Nicklasmoeller_Billysbilling_Model_Contact extends Nicklasmoeller_Billysbilling_Model_Abstract
{
    protected $customer;

    /**
     * @param $billingAddress
     *
     * @return bool|mixed
     */
    public function getCustomer($billingAddress)
    {
        if ($this->customer) {
            return $this->customer;
        }

        $res = $this->client->request("GET", "/contacts?contactNo=" . $billingAddress->getCustomerId());

        if ($res->body->meta->paging->total > 0) {
            $this->customer = $res->body->contacts[0];

            return $this->customer;
        }

        $contact = $this->buildCustomer($billingAddress);

        $res = $this->client->request("POST", "/contacts", array(
            'contact' => $contact
        ));

        if ($res->status !== 200) {
            return false;
        }

        $this->customer = $res->body->contacts[0];

        return $this->customer;
    }

    /**
     * @param $billingAddress
     *
     * @return mixed
     */
    public function buildCustomer($billingAddress)
    {
        $contact = new stdClass();

        $contact->organizationId = Mage::getSingleton('billysbilling/organization')->getOrganizationId();
        $contact->contactNo      = $billingAddress->getCustomerId();
        $contact->countryId      = Mage::getSingleton('billysbilling/country')->getCountry($billingAddress->getCountryId());
        $contact->zipcodeText    = $billingAddress->getPostcode();
        $contact->stateText      = $billingAddress->getRegion();
        $contact->cityText       = $billingAddress->getCity();
        $contact->street         = $billingAddress->getStreetFull();
        $contact->registrationNo = $billingAddress->getVatId();
        $contact->phone          = $billingAddress->getTelephone();
        $contact->isCustomer     = true;

        if ($billingAddress->getCompany()) {
            $contact->type = 'company';
            $contact->name = $billingAddress->getCompany();
        } else {
            $contact->type = 'person';
            $contact->name = $billingAddress->getName();
        }

        return $contact;
    }
}