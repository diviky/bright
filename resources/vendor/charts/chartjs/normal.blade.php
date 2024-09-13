<script type="text/javascript">
    function {{ $chart->id }}_create(data) {
        {{ $chart->id }}_rendered = true;
        var loader_element = document.getElementById("{{ $chart->id }}_loader");
        loader_element.parentNode.removeChild(loader_element);
        document.getElementById("{{ $chart->id }}").style.display = 'block';

        window.{{ $chart->id }} = new Chart(document.getElementById("{{ $chart->id }}").getContext("2d"), {
            type: {!! $chart->type ? "'{$chart->type}'" : 'data[0].type' !!},
            data: {
                labels: {!! $chart->formatLabels() !!},
                datasets: data
            },
            options: {!! $chart->formatOptions(true) !!}
        });
    }

    let {{ $chart->id }}_rendered = false;
    let {{ $chart->id }}_load = function() {
        if (document.getElementById("{{ $chart->id }}") && !{{ $chart->id }}_rendered) {
            {{ $chart->id }}_create({!! $chart->formatDatasets() !!})
        };
    }

    window.addEventListener("load", {{ $chart->id }}_load);
    document.addEventListener("turbolinks:load", {{ $chart->id }}_load);

    if (window.jQuery) {
        jQuery(document).ready({{ $chart->id }}_load);
    }
</script>
