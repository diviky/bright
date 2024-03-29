let {{ $chart->id }}_rendered = false;
let {{ $chart->id }}_load = function () {
    if (document.getElementById("{{ $chart->id }}") && !{{ $chart->id }}_rendered) {
        @if ($chart->api_url)
            fetch("{{ $chart->api_url }}")
                .then(data => data.json())
                .then(data => { {{ $chart->id }}_create(data) });
        @else
            {{ $chart->id }}_create({!! $chart->formatDatasets() !!})
        @endif
    }
};
window.addEventListener("load", {{ $chart->id }}_load);
document.addEventListener("turbolinks:load", {{ $chart->id }}_load);
document.addEventListener("ajax:loaded", {{ $chart->id }}_load);

if(window.jQuery){
    jQuery(document).ready({{ $chart->id }}_load);
}
