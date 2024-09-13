<?php

declare(strict_types=1);

namespace Diviky\Bright\Charts;

use Balping\JsonRaw\Encoder;
use ConsoleTVs\Charts\Classes\Chartjs\Chart as BaseChart;
use Illuminate\Support\Collection;

class Chart extends BaseChart
{
    /**
     * Initiates the Chartjs Line Chart.
     *
     * @return self
     */
    public function __construct()
    {
        parent::__construct();

        $this->container = 'charts::chartjs.container';
        $this->script = 'charts::chartjs.script';

        return $this->options([
            'responsive' => true,
            'scales' => [],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
                'plugins' => [
                    'colors' => [
                        'enabled' => false,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Formats the datasets for the output.
     *
     * @return string
     */
    public function formatApiDatasets()
    {
        return Encoder::encode(
            Collection::make($this->datasets)
                ->each(function ($dataset): void {
                    $dataset->matchValues(\count($this->labels));
                })
                ->map(function ($dataset) {
                    $dataset->options(['labels' => $this->labels]);

                    return $dataset->format($this->labels);
                })
                ->toArray()
        );
    }

    /**
     * Alias for the formatDatasets() method.
     */
    public function api()
    {
        return $this->formatApiDatasets();
    }

    /**
     * @param  null|int  $index
     * @return array|string
     */
    public function colors($index = null)
    {
        $colors = config('charts.default.colors');

        if (is_null($index)) {
            return $colors;
        }

        return $colors[$index];
    }

    public function datasets(array $sets)
    {
        foreach ($sets as $index => $set) {
            $this->dataset($set['label'], $set['type'], $set['data'], $index);
        }
    }

    /**
     * Adds a new dataset to the chart.
     *
     * @param  array|Collection  $data
     */
    public function dataset(string $name, string $type, $data, int $index = 0)
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        $dataset = new $this->dataset($name, $type, $data);

        // $dataset->options([
        //     'color' => $this->colors($index),
        // ]);

        // match ($type) {
        //     'pie' => $dataset->backgroundColor($this->colors()),
        //     default => $dataset->backgroundColor($this->colors($index))
        // };

        array_push($this->datasets, $dataset);

        return $dataset;
    }
}
