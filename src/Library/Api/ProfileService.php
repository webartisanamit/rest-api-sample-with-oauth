<?php

namespace Library\Api;

use Library\Lib;
use Library\LibException;

class ProfileService extends Service
{

    public $full_url = '';

    public $uri = '/profiles';

    protected $updateVerb = 'patch';

    public $return_key = 'profiles';

    public $slugs = [];

    public function __construct(FrilansInternational $client)
    {
        parent::__construct($client);

        $this->full_url = $client->getBaseUrl().$this->uri;
    }
}
