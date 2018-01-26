<?php

namespace App\Inspections;

use Exception;

class InvalidKeywords
{
    protected $invalidKeywords = [
        'yahoo customer support'
    ];

    public function detect($body)
    {
        foreach ($this->invalidKeywords as $invalidKeyword) {
            if (stripos($body, $invalidKeyword) !== false) {
                throw new Exception('Your reply contains spam.');
            }
        }
    }
}
