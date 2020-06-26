<html>
<head>
    <title>{{$template->nome}}</title>
</head>

<body>
    {!!$template->contenuto!!}
    <br>
    <br>

                <a href="{{url()->previous()}}">Indietro</a>

    </div>
</body>
</html>
