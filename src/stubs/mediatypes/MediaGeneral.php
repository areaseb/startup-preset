<?php

namespace App\Mediatypes;

class MediaGeneral {

	public function resizeAndSaveImage($file, $filename, $directory)
	{
        $img = \Image::make( $file->getRealPath() );
        $width = $img->width();
        $height = $img->height();
        $img->backup();

        if ($width > $height)
        {

            $img->fit(1900, 1078);
            $img->save( storage_path('app/public/'.$directory.'/full/').$filename );


            $img->reset();

            $img->fit(390, 285);
            $img->save( storage_path('app/public/'.$directory.'/display/').$filename );
        }
        else
        {
            if($height < 811)
            {
               $img->save( storage_path('app/public/'.$directory.'/full/').$filename );
            }
            else
            {
                $img->resize(null, 810, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save( storage_path('app/public/'.$directory.'/full/').$filename );
            }

            $img->reset();

            if($height < 401)
            {
                $img->save( storage_path('app/public/'.$directory.'/display/').$filename );
            }
            else
            {
                $img->resize(390, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save( storage_path('app/public/'.$directory.'/display/').$filename );
            }

        }

        $img->reset();

        $img->fit(70, 70);
        $img->save( storage_path('app/public/'.$directory.'/thumb/').$filename );

        return true;
	}

}
