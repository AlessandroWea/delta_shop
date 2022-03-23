<?php

namespace App\Utils;

final class ProductListFilter
{
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

	public static function applyFilters($builder, array $options = [])
	{
        self::applyOrderFilter($builder, $options['order']);
	}

	private static function applyOrderFilter($builder, string $order)
	{
        if($order == 'Default')
            return;
        else if($order == 'Newest')
        {
            $builder->orderBy('p.created', 'DESC');
        }
        else if($order == 'High price')
        {
            $builder->orderBy('p.price', 'DESC');
        }
	}

}