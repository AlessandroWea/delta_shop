<?php

namespace App\Utils;

final class PageSettings
{
    const DEFAULT_NUMBER_OF_PRODUCTS_PER_PAGE = 3;
    const NUMBER_OF_PAGE_LINKS = 4;
    const NUMBER_OF_RANDOM_PRODUCTS_IN_ASIDE_LIST = 3;

    private static $order_options = [
        'Default', 'Newest'
    ];

    private static $count_options = [
        3,6,9,12,20
    ];

    public static function getOrderOptions()
    {
    	return self::$order_options;
    }

    public static function getCountOptions()
    {
    	return self::$count_options;
    }

    public static function validate($array)
    {
    	foreach($array as $key => $value)
    	{
    		$key .= 's';
    		if(!in_array($value, self::$$key))
    			return false;
    	}

    	return true;
    }
}