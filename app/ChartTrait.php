<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;

trait ChartTrait
{

    public function showChart($type, $name, $labels, $datasets, $options)
    {    

        return Chartjs::build()
            ->name($name)
            ->type($type)
            ->size(["width" => 300, "height" => 200])
            ->labels($labels)
            ->datasets($datasets)
            ->options($options);
    }
}
