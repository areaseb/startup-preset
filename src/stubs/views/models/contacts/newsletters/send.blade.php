<div class="alert alert-primary" role="alert">
    Stai per inviare la newsletter. Una volta inviata non si può tornare indietro. Assicurati che sia tutto corretto!!!
</div>

{!! Form::open(['url' => url('newsletters/'.$newsletter->id.'/send')]) !!}

    <div class="form-group">
        <label class="col-form-label">Indirizzo email di spedizione:</label>
        <input type="text" class="form-control" name="sender['address']" value="{{$sender['address']}}" disabled />
    </div>

    <div class="form-group">
        <label class="col-form-label">Nome indirizzo email:</label>
        <input type="text" class="form-control" name="sender['name']" value="{{$sender['name']}}" disabled />
    </div>

    <div class="form-group">
        <label class="col-form-label">Oggetto:</label>
        <input type="text" class="form-control" name="subject" value="{{$newsletter->oggetto}}" disabled />
    </div>

    <div class="form-group">
        <label class="col-form-label">Anteprima testo:</label>
        <textarea name="descrizione" class="form-control" disabled>{{$newsletter->descrizione}}</textarea>
    </div>

    <input type="hidden" name="template_id" value="{{$newsletter->template_id}}">

    <div class="form-group">
        <label class="col-form-label">Nome template:</label>
        <input type="text" class="form-control" name="template" value="{{$newsletter->template->nome}}" disabled />
        <small class="form-text text-muted">Controlla il template: <a href="{{$newsletter->template->url}}" target="_blank">Anteprima</a>.</small>
    </div>

    <div class="form-group">
        <label class="col-form-label">Liste:</label>
        <ul>
            @foreach($newsletter->lists as $list)
                <li>{{$list->nome}} ({{$list->count_contacts}})</li>
            @endforeach
        </ul>
    </div>


{!! Form::close() !!}
