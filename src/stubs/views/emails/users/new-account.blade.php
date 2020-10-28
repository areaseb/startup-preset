@component('mail::message')
# Nuove Credenziali

Salve <b>{{$recipient->fullname}}</b>,<br>
Abbiamo creato un account per te. Entra con queste credenziali:<br><br>
<b>email:</b> {{$recipient->email}}<br>
<b>password:</b> {{$pw}}

@component('mail::button', ['url' => url('login')])
Login
@endcomponent


{{ config('app.name') }}
@endcomponent
