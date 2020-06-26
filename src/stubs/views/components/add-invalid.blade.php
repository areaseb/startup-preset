@if ($errors->any())
    @if($errors->has($element))
        <script>
            document.getElementsByName("{{$element}}")[0].classList.add('is-invalid');
        </script>
    @endif
@endif
