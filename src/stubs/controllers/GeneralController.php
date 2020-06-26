<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Contacts\{Company, Contact, Editor, Newsletter, NewsletterList, Template};
use App\Classes\{City, Country, Role, User};

class GeneralController extends Controller
{
    public function updateField(Request $request)
    {
        $model = $this->findModel($request);
        if($model)
        {
            $model->update([$request->field => $request->value]);
            return 'done';
        }

        return 'not found';
    }

    public function findModel($data)
    {

        if ( class_exists('App\\Classes\\Contacts\\'.$data->model) )
		{
            $class = 'App\\Classes\\Contacts\\'.$data->model;
			return $class::findOrFail($data->id);
		}
        elseif( class_exists('App\\Classes\\'.$data->model) )
        {
            $class = 'App\\Classes\\'.$data->model;
            return $class::findOrFail($data->id);
        }
        return null;

    }

}
