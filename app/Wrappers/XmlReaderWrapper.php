<?php

namespace App\Wrappers;

use Saloon\XmlWrangler\XmlReader;

class XmlReaderWrapper
{
    public function fromString(string $xml): XmlReader
    {
        return XmlReader::fromString($xml);
    }
}
