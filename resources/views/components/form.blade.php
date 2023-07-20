<form {!! $attributes->merge($attribs)->merge(['method' => 'post', 'class' => 'form']) !!}>
    @csrf

    {!! $slot !!}
</form>
