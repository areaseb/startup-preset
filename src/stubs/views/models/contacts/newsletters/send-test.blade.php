{!! Form::open(['url' => url('newsletters/'.$newsletter->id.'/send-test')]) !!}
    <div class="form-group">
        <label for="name" class="col-form-label">Indirizzo email test:</label>

        @if(\App\Classes\Setting::defaultTestEmail())
            <input type="text" class="form-control" name="email">
            <small class="form-text text-muted">
                Se lasciato vuoto si userà l'email test di default:<br>
                @foreach(\App\Classes\Setting::defaultTestEmail() as $email)
                    @if($loop->last)
                        {{$email}}
                    @else
                        {{$email}}<br>
                    @endif
                @endforeach
            </small>
        @else
            <input type="text" class="form-control" name="email" required>
            <small class="form-text text-muted">Non hai ancora impostato gli indirizzi di default. Puoi farlo da qui: <a href="{{url('settings')}}" target="_blank">Settings</a></small>
        @endif

    </div>
{!! Form::close() !!}
