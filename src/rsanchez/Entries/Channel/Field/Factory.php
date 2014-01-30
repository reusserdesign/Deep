<?php

namespace rsanchez\Entries\Channel\Field;

use rsanchez\Entries\Property\Factory as PropertyFactory;
use rsanchez\Entries\Channel\Field;
use stdClass;

class Factory extends PropertyFactory
{
    public function createProperty(stdClass $row)
    {
        return new Field($row);
    }
}
