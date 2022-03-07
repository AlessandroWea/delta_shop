<?php

namespace App\Utils;

class Utils
{
    public function toPercent($value, $sum)
    {
        return ($sum > 0) ? (100 * $value) / $sum : 0;
    }

    public function convertCategoriesIntoBreadcrumbs(array $items) : array
    {
        $breadcrumbs = array();
        foreach($items as $item)
        {
            array_push($breadcrumbs, [
                'type' => 'category',
                'id' => $item->getId(),
                'name' => $item->getName(),
            ]);
        }

        return $breadcrumbs;
    }
}