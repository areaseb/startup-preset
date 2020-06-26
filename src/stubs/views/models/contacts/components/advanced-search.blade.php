{!! Form::open(['url' => url('contacts'), 'method' => 'get', 'id' => 'formFilter']) !!}
<div class="row @if(!request()->input()) d-none @endif" id="advancedSearchBox">

    @if(request()->input())
        <div style="float: left;width:87px;">
            <div class="form-group">
                <label style="color:#fff">Reset</label>
                <a href="#" class="btn btn-success" id="refresh"><i class="fa fa-redo"></i> Reset</a>
            </div>
        </div>
    @endif

    <div class="col">
        <div class="form-group">
            <label>Regione</label>
            <select class="custom-select" name="region">
                @foreach(\App\Classes\City::uniqueRegions() as $region)
                    @if(request('region') == $region)
                        <option selected>{{$region}}</option>
                    @else
                        <option>{{$region}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label>Provincia</label>
            <select class="custom-select" name="province">
                @foreach(\App\Classes\City::uniqueProvinces(request('region')) as $province)
                    @if(request('province') == $province)
                        <option selected>{{$province}}</option>
                    @else
                        <option>{{$province}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label>Tipo</label>
            <select class="custom-select" name="tipo">
                <option></option>
                <option value="Lead">Lead</option>
                <option value="Prospect">Prospect</option>
                <option value="Client">Client</option>
            </select>
        </div>
    </div>


    <div class="col">
        <div class="form-group">
            <label>Lista</label>
            <select class="custom-select" name="list">
                <option></option>
                @foreach(\App\Classes\Contacts\NewsletterList::orderBy('nome', 'asc')->get() as $list)
                    @if(request('list') == $list->id)
                        <option  value="{{$list->id}}" selected>{{$list->nome}}</option>
                    @else
                        <option value="{{$list->id}}" >{{$list->nome}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>


    <div class="col">
        <div class="form-group">
            <label>Data di creazione</label>
            <select class="custom-select" name="created_at">
                <option></option>
                <option>Oggi</option>
                <option>Ieri</option>
                <option>Ultimi 7 giorni</option>
                <option>Ultimi 30 giorni</option>
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <label>Data di modifica</label>
            <select class="custom-select" name="updated_at">
                <option></option>
                <option>Oggi</option>
                <option>Ieri</option>
                <option>Ultimi 7 giorni</option>
                <option>Ultimi 30 giorni</option>
            </select>
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="testo da cercare" name="search" value="{{request('search')}}">
                <div class="input-group-append" id="button-addon4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cerca</button>
                    <a href="{{url('contacts')}}" class="btn btn-success"><i class="fas fa-redo"></i></a>
                </div>
            </div>
        </div>
    </div>

</div>
{!! Form::close() !!}


@push('scripts')
<script>
    $('#customSwitch1').on('change', function(){
        if($(this).prop('checked') === true)
        {
            $('#advancedSearchBox').removeClass('d-none');
        }
        else
        {
            $('#advancedSearchBox').addClass('d-none');
        }
    });

    $('select.custom-select').on('change', function(){
        $('#formFilter').submit();
    });

    $('#refresh').on('click', function(e){
        e.preventDefault();
        let currentUrl = window.location.href;
        let arr = currentUrl.split('?');
        window.location.href = arr[0];
    });
    @if(request()->get('tipo'))
        $('select.custom-select[name="tipo"] option[value="{{request('tipo')}}"]').prop('selected', true);
    @endif

</script>
@endpush
