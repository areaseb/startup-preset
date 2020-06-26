<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;
use App\Mediatypes\{MediaEditor, MediaGeneral};
use App\Classes\Contacts\Editor;

class Media extends Model
{
    protected $guarded = array();

    public function mediable()
    {
        return $this->morphTo();
    }

//images/files getters
    public function getOriginalAttribute()
    {
        return asset('storage/app/public/'.$this->directory.'/original/'.$this->filename);
    }

    public function getDocAttribute()
    {
        return asset('storage/app/public/'.$this->directory.'/docs/'.$this->filename);
    }

    public function getFullAttribute()
    {
        return asset('storage/app/public/'.$this->directory.'/full/'.$this->filename);
    }

    public function getDisplayAttribute()
    {
        return asset('storage/app/public/'.$this->directory.'/display/'.$this->filename);
    }

    public function getThumbAttribute()
    {
        return asset('storage/app/public/'.$this->directory.'/thumb/'.$this->filename);
    }

//model helpers
    public function getDirectoryAttribute()
    {
    	return str_plural( strtolower( str_replace("App\\", "" , $this->mediable_type) ) );
    }

    public function getDimensionAttribute()
    {
        return $this->width.'x'.$this->height;
    }

    public function getIsPortraitAttribute()
    {
        if($this->width > $this->height)
        {
            return false;
        }
        return true;
    }

    public function getKbAttribute()
    {
        return $this->size . ' Kb';
    }

    public function getIsFileAttribute()
    {
        if($this->mime == 'doc')
        {
            return true;
        }
        return false;
    }

    public function getIconAttribute()
    {
        if($this->is_file)
        {
            $arr = explode('.', $this->filename);
            $ext = strtolower($arr[1]);
            if(strpos($ext, 'doc') !== false)
            {
                return '<i class="icon-file-word icon-2x text-primary-300 top-0"></i>';
            }
            elseif(strpos($ext, 'pdf') !== false)
            {
                return '<i class="icon-file-pdf icon-2x text-warning-300 top-0"></i>';
            }
            elseif(strpos($ext, 'xls') !== false)
            {
                return '<i class="icon-file-excel icon-2x text-pink-300 top-0"></i>';
            }
            else
            {
                return '<i class="icon-file-empty icon-2x text-default-300 top-0"></i>';
            }
        }
        return '<i class="icon-picture icon-2x text-primary-300 top-0"></i>';
    }

//FUNCTIONS
//check if is image with valid extension (request->file)
    public static function isImage($file)
    {
        $imageExtensions = ['jpeg','jpg','png'];
        $ext = strtolower($file->getClientOriginalExtension());
        if ( in_array($ext, $imageExtensions) )
        {
            return true;
        }
        return false;
    }


//set numeric order to images of the same model
    public static function getMediaOrder($type, $id)
    {
        if (self::where('mediable_type', $type)->where('mediable_id', $id)->exists())
        {
            return self::where('mediable_type', $type)->where('mediable_id', $id)->orderBy('id', 'DESC')->first()->media_order+1;
        }
        return 1;
    }


//delete all media from model
    public static function deleteAllMedia($model)
    {
        if($model->media()->exists())
        {
            foreach($model->media as $file)
            {
                self::deleteMediaFromId($file->id);
            }
        }
        return 'done';
    }

//delete single media from db and server
    public static function deleteMediaFromId($id)
    {
        $media = self::find($id);
        $folders = ['/thumb/', '/original/'];
        foreach($folders as $folder)
        {
            if( file_exists ( storage_path('app/public/'.$media->directory.$folder.$media->filename) ) )
            {
                unlink( storage_path('app/public/'.$media->directory.$folder.$media->filename) );
            }
        }
        $media->delete();
        return 'done';
    }


    public static function makeNiceName($request, $ext)
    {

        $model = self::findModelFromRequest($request->mediable_type, $request->mediable_id);

        if( is_null($model) )
        {
            $original = $request->file->getClientOriginalName();

            if( file_exists ( storage_path('app/public/'.$request->directory.'/original/'.$original) ) )
            {
                return pathinfo($request->file->getClientOriginalName(), PATHINFO_FILENAME).'-'.str_random(2).'.'.$ext;
            }
            else
            {
                return $original;
            }
        }

        return $model->id . '-' . self::getMediaOrder($request->mediable_type, $request->mediable_id) . '-' .str_random(2).'.'.$ext;
    }

    public static function makeNiceDescription($request)
    {
        $model = self::findModelFromRequest($request->mediable_type, $request->mediable_id);

        if( is_null($model) )
        {
            return $request->file->getClientOriginalName();
        }

        return $model->id . ' ' . self::getMediaOrder($request->mediable_type, $request->mediable_id);
    }

    public static function findModelFromRequest($model, $id = null)
    {
        if( is_null($id) )
        {
            $id = 1;
        }

		if ( class_exists($model) )
		{
			return $model::findOrFail($id);
		}

        return null;
    }

//save file in db and server ($request = upload request)
    public static function saveImageOrFile($request)
    {
        if ( $request->hasFile('file') )
        {
            if ( $request->file->isValid() )
            {
                $filename = self::makeNiceName($request, strtolower($request->file->getClientOriginalExtension()) );

                if ( self::isImage($request->file) )
                {
                    $request->file->storeAs('public/'.$request->directory.'/original', $filename );

                    $temp = explode("\\", $request->mediable_type);
                    $class = '\App\Mediatypes\Media'.end($temp);

                    if (class_exists($class))
                    {
                        $media = new $class;
                    }
                    else
                    {
                        $media = new MediaGeneral;
                    }


                    $media->resizeAndSaveImage($request->file, $filename, $request->directory);

                    $image = \Image::make( $request->file->getRealPath() );
                    if($request->mediable_id)
                    {
                        self::create([
                            'description' => self::makeNiceDescription($request),
                            'mime' => "image",
                            'filename' => $filename,
                            'mediable_id' => $request->mediable_id,
                            'mediable_type' => $request->mediable_type,
                            'media_order' => self::getMediaOrder($request->mediable_type, $request->mediable_id),
                            'width' => $image->width(),
                            'height' => $image->height(),
                            'size' => round($image->filesize()/1000)
                        ]);
                    }

                    return 'image saved';
                }

                $request->file->storeAs('public/'.$request->directory.'/original', $filename );

                self::create([
                    'description' => 'document ' . self::makeNiceDescription($request),
                    'mime' => 'doc',
                    'filename' => $filename,
                    'mediable_id' => $request->mediable_id,
                    'mediable_type' => $request->mediable_type,
                    'media_order' => self::getMediaOrder($request->mediable_type, $request->mediable_id),
                    'size' => round($request->file->getSize()/1000)
                ]);

                return 'doc saved';

            }
            return 'invalid file';
        }
        return 'request has no file';
    }




}
