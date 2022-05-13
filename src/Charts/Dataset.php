<?php

declare(strict_types=1);

namespace Diviky\Bright\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Dataset as BaseDataset;

class Dataset extends BaseDataset
{
    /**
     * Initializes the chart.
     */
    public function __construct(string $name, string $type, array $values)
    {
        parent::__construct($name, $type, $values);

        $this->options([
            'color' => config('charts.default.color'),
        ]);

        $this->backgroundColor(config('charts.default.colors'));
    }
}
