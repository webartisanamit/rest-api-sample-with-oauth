<?php

namespace Library\Api;

use Library\Lib;
use Library\LibException;

class CustomersService extends Service
{

    public $full_url = '';

    public $uri = '/customers';

    protected $updateVerb = 'patch';

    public $return_key = 'customers';

    public $slugs = [];

    public function __construct(FrilansInternational $client)
    {
        parent::__construct($client);

        $this->full_url = $client->getBaseUrl().$this->uri;
    }

}
