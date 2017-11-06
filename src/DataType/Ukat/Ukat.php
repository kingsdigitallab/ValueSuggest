<?php
namespace ValueSuggest\DataType\Ukat;

use ValueSuggest\DataType\AbstractDataType;
use ValueSuggest\Suggester\Ukat\UkatSuggest;

class Ukat extends AbstractDataType
{
    public function getSuggester()
    {
        return new UkatSuggest($this->services->get('Omeka\HttpClient'));
    }

    public function getName()
    {
        return 'valuesuggest:ukat:ukat';
    }

    public function getLabel()
    {
        return 'Ukat: The UKAT subject database'; // @translate
    }
}
