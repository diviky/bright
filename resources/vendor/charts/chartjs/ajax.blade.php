<script type="text/javascript">
    var {{ $chart->id }}_data = {};

    function {{ $chart->id }}_create(data) {
        {{ $chart->id }}_rendered = true;
        var loader_element = document.getElementById("{{ $chart->id }}_loader");
        loader_element.parentNode.removeChild(loader_element);
        {{ $chart->id }}_data = {{ $chart->id }}_formatChartData(data);
        document.getElementById("{{ $chart->id }}").style.display = 'block';
        window.{{ $chart->id }} = new Chart(document.getElementById("{{ $chart->id }}").getContext("2d"),
            {{ $chart->id }}_data);
    }

    function {{ $chart->id }}_formatChartData(data) {
        var config = {
            type: {!! $chart->type ? "'{$chart->type}'" : 'data[0].type' !!},
            data: {
                labels: data[0].labels,
                datasets: data
            },
            options: {!! $chart->formatOptions(true) !!}
        };

        return config;
    }

    function {{ $chart->id }}_update(data) {
        window.{{ $chart->id }}.data.labels = data[0].labels;
        window.{{ $chart->id }}.data.datasets = data;
        window.{{ $chart->id }}.update();
    }

    let {{ $chart->id }}_rendered = false;
    let {{ $chart->id }}_load = function() {
        if (document.getElementById("{{ $chart->id }}") && !{{ $chart->id }}_rendered) {
            fetch("{{ $chart->api_url }}")
                .then(data => data.json())
                .then(data => {
                    {{ $chart->id }}_create(data)
                });
        }
    };
    window.addEventListener("load", {{ $chart->id }}_load);
    document.addEventListener("turbolinks:load", {{ $chart->id }}_load);

    if (window.jQuery) {
        jQuery(document).ready({{ $chart->id }}_load);
    }
</script>
