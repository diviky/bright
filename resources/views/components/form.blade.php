<form method="{{ $spoofMethod ? 'POST' : $method }}" {!! $attributes->merge($attribs)->class([
    'form-floated' => $style == 'floated' || $attributes->has('floated'),
]) !!}
    @if ($attributes->has('reset')) data-reset="true" @endif
    @if ($attributes->has('render')) data-render="true" @endif easyrender
    @if ($hasFiles) enctype="multipart/form-data" @endif
    @unless ($spellcheck)
        spellcheck="false"
    @endunless>

    @unless (in_array($method, ['HEAD', 'GET', 'OPTIONS']))
        @csrf
    @endunless

    @if ($spoofMethod)
        @method($method)
    @endif

    {!! $slot !!}
</form>
