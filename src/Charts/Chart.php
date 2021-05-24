<?php

declare(strict_types=1);

namespace Diviky\Bright\Charts;

use Balping\JsonRaw\Encoder;
use ConsoleTVs\Charts\Classes\Chartjs\Chart as BaseChart;
use Illuminate\Support\Collection;

class Chart extends BaseChart
{
    /**
     * Chartjs dataset class.
     *
     * @var object
     */
    public $dataset = Dataset::class;

    /**
     * Initiates the Chartjs Line Chart.
     *
     * @return self
     */
    public function __construct()
    {
        parent::__construct();

        return $this->options([
            'responsive' => true,
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
}
