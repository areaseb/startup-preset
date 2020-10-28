@if($model->media()->where('mime', 'doc')->exists())
    <div class="card card-outline card-warning mt-5">
        <div class="card-header">FILES</div>
        <table class="table table-sm table-bordered doc-table mb-0 pb-0">
            <thead class="thead-light">
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">descrizione file</th>
                    <th class="text-center">preview</th>
                    <th class="text-center">size</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($model->media()->where('mime','doc')->get() as $file)
                    <tr>
                        <td class="align-middle text-center" style="min-width: 50px;">
                            {{$loop->index+1}}
                        </td>
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
                        <td class="align-middle text-center">
                            <a class="btn btn-sm btn-primary" target="_BLANK" href="{{$file->doc}}" >
                                <i class="fa fa-disk"></i>{{$file->filename}}
                            </a>
                        </td>
                        <td class="align-middle text-center">
                            <small>{{$file->kb}}</small>
                        </td>

                        <td class="align-middle text-center">
                            <form method="POST" action="{{url('api/media/delete')}}">
                                {{csrf_field()}}
                                {{method_field('DELETE')}}
                                <input type="hidden" name="id" value="{{$file->id}}">
                                <button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash" style="width: 20px;height: 25px;padding-top: 4px;"></i> </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
