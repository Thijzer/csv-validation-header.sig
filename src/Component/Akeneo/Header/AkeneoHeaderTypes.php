<?php

namespace Misery\Component\Akeneo\Header;

class AkeneoHeaderTypes implements HeaderTypes
{
    public const SELECT = 'pim_catalog_simpleselect';
    public const MULTISELECT = 'pim_catalog_multiselect';
    public const METRIC = 'pim_catalog_metric';
    public const PRICE = 'pim_catalog_price_collection';
    public const BOOLEAN = 'pim_catalog_boolean';
    public const TEXT = 'pim_catalog_text';
    public const NUMBER = 'pim_catalog_number';
    public const REFDATA_SELECT = 'pim_reference_data_simpleselect';
    public const REFDATA_MULTISELECT = 'pim_reference_data_multiselect';
}