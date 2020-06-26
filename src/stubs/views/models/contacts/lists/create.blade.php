{!! Form::open(['url' => url('contacts/lists')]) !!}
    <div class="form-group">
        <label for="nome" class="col-form-label">Nome Lista:</label>
        <input type="text" class="form-control" name="nome" required>
    </div>
    @if(count(request()->input()))
        <div class="form-group">
            <ul>
                @if(request()->get('region'))
                    <li>Regione: {{request()->get('region')}} <i class="fas fa-check-circle text-success"></i></li>
                @endif
                @if(request()->get('province'))
                    <li>Provincia: {{request()->get('province')}}<i class="fas fa-check-circle text-success"></i></li>
                @endif
                @if(request()->get('tipo'))
                    <li>Tipo: {{request()->get('tipo')}} <i class="fas fa-check-circle text-success"></i></li>
                @endif
                @if(request()->get('updated_at'))
                    <li>Data di modifica: {{request()->get('updated_at')}} <i class="fas fa-check-circle text-success"></i></li>
                @endif
                @if(request()->get('created_at'))
                    <li>Data di creazione: {{request()->get('created_at')}} <i class="fas fa-check-circle text-success"></i></li>
                @endif
            </ul>
            <input type="hidden" class="form-control" name="region" value="{{request()->get('region')}}">
            <input type="hidden" class="form-control" name="province" value="{{request()->get('province')}}">
            <input type="hidden" class="form-control" name="tipo" value="{{request()->get('tipo')}}">
            <input type="hidden" class="form-control" name="updated_at" value="{{request()->get('updated_at')}}">
            <input type="hidden" class="form-control" name="created_at" value="{{request()->get('created_at')}}">


        </div>
    @else
        tutti i contatti
    @endif

{!! Form::close() !!}
