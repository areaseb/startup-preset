<?php

namespace App\Mediatypes;

class MediaEditor {

	public function resizeAndSaveImage($file, $filename, $directory)
	{
		$img = \Image::make( $file->getRealPath() );
        $img->backup();

        $img->fit(250, 150);
        $img->save( storage_path('app/public/'.$directory.'/250x150/').$filename );
        $img->reset();

        $img->fit(350, 150);
        $img->save( storage_path('app/public/'.$directory.'/350x150/').$filename );
		$img->reset();

		$img->fit(600, 200);
        $img->save( storage_path('app/public/'.$directory.'/600x200/').$filename );
		$img->reset();

		$img->fit(110, 110);
        $img->save( storage_path('app/public/'.$directory.'/thumb/').$filename );
		$img->reset();

        return true;
	}

}
