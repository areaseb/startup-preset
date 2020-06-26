<?php

namespace App\Mediatypes;

class MediaEditor {

	public function resizeAndSaveImage($file, $filename, $directory)
	{
        $img = \Image::make( $file->getRealPath() );
        $img->backup();

        $img->fit(600, 200);
        $img->save( storage_path('app/public/'.$directory.'/full/').$filename );

        $img->reset();

        $img->fit(250, 150);
        $img->save( storage_path('app/public/'.$directory.'/display/').$filename );

        return true;
	}

}
