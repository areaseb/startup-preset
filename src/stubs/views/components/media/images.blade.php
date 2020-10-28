@if($model->media()->where('mime', 'image')->exists())
    <div class="card card-outline card-success mt-5">
        <div class="card-header">IMMAGINI</div>
        <table class="table table-sm table-bordered image-table mb-0 pb-0">
            <thead class="thead-light">
                <tr>
                    <th class="text-center" data-toggle="tooltip" title="trascina il numero per cambiare l'ordine">#</th>
                    <th data-toggle="tooltip" title="descrzione (alt) molto utile per goolge">descrizione</th>
                    {{-- <th data-toggle="tooltip" title="cambia la tipologia dell'immagine per vederla in sezioni diverse del sito">type</th> --}}
                    <th class="text-center">preview</th>
                    <th data-toggle="tooltip" title="peso e dimensioni dell'immagine originale">size</th>
                    <th></th>
                    <th class="d-none"></th>
                </tr>
            </thead>
            <tbody class="popup-gallery">
                @foreach($model->media()->where('mime','image')->orderBy('media_order', 'ASC')->get() as $file)
                    <tr>
                        <td class="align-middle text-center handler">{{$loop->iteration}}</td>
                        <td class="align-middle text-center">
                            <form method="POST" action="{{url('api/media/update')}}" class="col-sm-12 form-description">
                                {{csrf_field()}}
                                <input type="hidden" name="id" value="{{$file->id}}">
                                <div class="input-group">
                                    <input type="text" name="description" class="form-control" value="{{$file->description}}" />
                                    <button class="btn btn-primary tbr0" id="{{$file->id}}"><i class="fa fa-save"></i></button>
                                </div>
                            </form>
                        </td>
                        {{-- <td>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="type{{$file->id}}"  value="normal" id="{{$file->id}}" @if($file->type=="normal") checked @endif>
                                    normal
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="type{{$file->id}}"  value="sample" id="{{$file->id}}" @if($file->type=="sample") checked @endif>
                                    sample
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="type{{$file->id}}"  value="background" id="{{$file->id}}" @if($file->type=="background") checked @endif>
                                    background
                                </label>
                            </div>


                        </td> --}}
                        <td class="align-middle text-center image-to-pop">
                            <a class="image-popup thumb" href="{{ $file->display }}" title="{{$file->description}}" >
                                <img src="{{ $file->thumb }}">
                            </a>
                        </td>
                        <td><small>{{$file->dimension}}<br>{{$file->kb}}</small></td>
                        <td class="align-middle text-center">
                            <form method="POST" action="{{url('api/media/delete')}}">
                                {{csrf_field()}}
                                {{method_field('DELETE')}}
                                <input type="hidden" name="id" value="{{$file->id}}">
                                <button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash" style="width: 20px;height: 25px;padding-top: 4px;"></i> </button>
                            </form>
                        </td>
                        <td class="d-none">{{$file->id}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endif
