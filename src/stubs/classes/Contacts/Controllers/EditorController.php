<?php

namespace App\Classes\Contacts\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\Contacts\{Editor, Template};
use App\Classes\Media;

class EditorController extends Controller
{

    public function editor()
    {
        $elements = asset( 'public/editor/elements-create.json' );
        $update = false;
        $page = asset( 'public/editor/template-load-page.html' );
        return view('extra.editor.template-builder', compact('elements', 'update', 'page'));
    }

    public function editorWithTemplate($id)
    {
        $elements = asset( 'public/editor/elements-edit.json' );
        $update = true;
        $template = Template::find($id);
        $page = $template->url;
        return view('extra.editor.template-builder', compact('elements', 'update', 'template', 'page'));
    }

//editor/elements/{slug} - GET
    public function show($slug)
    {
        return view('extra.editor.elements.'.$slug);
    }

//editor/images - GET
    public function images()
    {

        $display =  \Storage::disk('public')->files('editor/display');
        $full = \Storage::disk('public')->files('editor/full');
        $original = \Storage::disk('public')->files('editor/original');
        $files = []; $captions = [];
        foreach($display as $key => $image)
        {
            $files[] = $image;
            $files[] = $full[$key];
            $files[] = $original[$key];
            $captions[] = '250x150';
            $captions[] = '600x200';
            $captions[] = 'original';
        }

        $response = [];
        $response['code'] = 0;
        $response['files'] = $files;
        $response['captions'] = $captions;
        $response['directory'] = asset('storage/app/public')."/";
        return $response;
    }

//editor/upload - POST
    public function upload(Request $request)
    {
        return Media::saveImageOrFile($request);
    }


}
