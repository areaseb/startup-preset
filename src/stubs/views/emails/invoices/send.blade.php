@component('mail::message')
# Fattura di cortesia

Spett. {{$company->rag_soc}},<br>


Grazie,<br>
{{ config('app.name') }}
@endcomponent
