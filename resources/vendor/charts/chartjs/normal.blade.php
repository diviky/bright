<script type="text/javascript">
    function {{ $chart->id }}_create(data) {
        {{ $chart->id }}_rendered = true;
        var loader_element = document.getElementById("{{ $chart->id }}_loader");
        loader_element.parentNode.removeChild(loader_element);
        document.getElementById("{{ $chart->id }}").style.display = 'block';
        window.{{ $chart->id }} = new Chart(document.getElementById("{{ $chart->id }}").getContext("2d"), {
            types: {!! $chart->type ? "'{$chart->type}'" : "(data[0] !== undefined ? data[0].type : 'bar')" !!},
            data: {
                labels: {!! $chart->formatLabels() !!},
                datasets: data
            },
            options: {!! $chart->formatOptions(true) !!}
        });
    }

        @include('charts::init')
</script>
