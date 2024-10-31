<?php

namespace App;

use IcehouseVentures\LaravelChartjs\Facades\Chartjs;

trait ChartTrait
{

    public function showChart($type, $name, $size, $labels, $datasets, $options)
    {    

        return Chartjs::build()
            ->name($name)
            ->type($type)
            ->size($size)
            ->labels($labels)
            ->datasets($datasets)
            ->options($options);
    }
}
