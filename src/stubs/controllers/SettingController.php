<?php

namespace App\Http\Controllers;

use App\Classes\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Setting::all();
        return view('models.settings.index')->with('settings', $settings);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function edit(Setting $setting)
    {
        return view('models.settings.edit')->with('setting', $setting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting)
    {

        $keys = $request->key;
        $values = $request->value;
        $arr = [];
        for($x=0; $x < count(array_filter($keys)); $x++)
        {
            $arr[$keys[$x]] = $values[$x];
        }
        $setting->fields = $arr;
        $setting->save();
        return redirect('settings')->with('message', 'Setting ' . $setting->model . ' aggiornati');

    }


}
