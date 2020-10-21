@if ($message = Session::pull('message'))
    @if (Session::pull('status') == 'OK')
        <div class="alert alert-success">{{ $message }}</div>
    @elseif (Session::pull('status') == 'INFO')
        <div class="alert alert-info">{{ $message }}</div>
    @else
        <div class="alert alert-danger">{{ $message }}</div>
    @endif
@endif
