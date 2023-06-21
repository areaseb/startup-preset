<?php

namespace App\FeiC\Xml;

use \Log;
use \File;
use \Exception;
use \Storage;
use App\Fe\Primitive;
use Areaseb\Core\Models\Media;

class Xml extends Primitive
{

    public function __construct()
    {
        $this->version = config('fe.version');
    }

	public function createXml()
	{
        try
        {
    		return new \SimpleXMLElement(str_replace('$ver$', $this->version, File::get( __DIR__ . '/invoice.xml')));
        }
        catch(Exception $e)
        {
		 	Log::channel('fe')->error($e);
            return false;
        }
	}

    public function createSimpleXml($content)
    {
        try
        {
            libxml_use_internal_errors(true);
    		return new \SimpleXMLElement($content);
        }
        catch(Exception $e)
        {
		 	Log::channel('fe')->error($e);
            return false;
        }
    }

    public function saveXml($template, $folder)
	{
        $dt = $template->FatturaElettronicaHeader->DatiTrasmissione;
        $filename = $dt->IdTrasmittente->IdPaese . $dt->IdTrasmittente->IdCodice . '_' . $dt->ProgressivoInvio . '.xml';
        if(!is_dir(storage_path('app/public/fe/'.$folder.'/'.date('Y')))){
        	mkdir(storage_path('app/public/fe/'.$folder.'/'.date('Y')), 0755);
        }
        $path = storage_path('app/public/fe/'.$folder.'/'.date('Y').'/'.$filename);

        if( file_put_contents($path, $template->asXML()) !== false )
        {
            return $template->asXML();
        }

        Log::channel('fe')->error("Impossibile salvare il file xml in $folder con nome $filename");
        return false;
    }

    public function getXml($input, $filename, $mode)
    {
        $folder = ($mode == 'in') ? 'ricevute' : 'inviate';
        $file = storage_path('app/public/fe/'.$folder.'/'.$filename);

        if ( file_put_contents($file, $input) !== false )
        {
            if( $this->endsWith($file, '.p7m') )
            {
                $file = substr($file, 0, strrpos($file, '.p7m'));

                if (file_exists($file))
                {
                    unlink($file);
                }

                if($mode == 'in')
                {
                    exec( 'perl '. storage_path('app/public/fe/ricevute/extract-p7m.pl') );
                }
                else
                {
                    exec( 'perl '. storage_path('app/public/fe/inviate/extract-p7m.pl') );
                }

                if (file_exists($file))
                {
                    $content = file_get_contents($file);
                    if ($content !== false)
                    {
                        Log::channel('fe')->info("letto file: $file");
                        return $this->createSimpleXml($content);
                    }

                    Log::channel('fe')->error("fallita lettura del file: $file");
                    return false;
                }

                Log::channel('fe')->error("fallita creazione del file: $file");
                return false;
            }
            return $this->createSimpleXml($input);
        }

        Log::channel('fe')->error("fallita scrittura del file: $file");
        return false;
    }

    public function rename($filename, $model, $mode)
    {
        $folder = ($mode == 'in') ? 'ricevute' : 'inviate';
        $file = storage_path('app/public/fe/'.$folder.'/'.$filename);
        if( $this->endsWith($file, '.p7m') )
        {
            $file = substr($file, 0, strrpos($file, '.p7m'));
        }

        if (file_exists($file))
        {
            return $this->saveXmlInDB($filename, $model, $mode);
        }

        Log::channel('fe')->error("file non presente: {$file}");
        return false;
    }

    public function savePdf($xml, $model)
    {
        $content = base64_decode($xml->FatturaElettronicaBody->Allegati->Attachment);
        $filename = $model->numero.'-'.date('Y').'.pdf';
        $mediable_type = $this->fullClass($model);

        $type_path = 'ricevute/';;
        if($model->class == 'Invoice')
        {
            $type_path = 'inviate/';
        }

        if ( \Storage::disk('local')->put('public/fe/pdf/'.$type_path.$filename, $content) === false )
        {
            Log::channel('fe')->error("errore salvando il file {$filename}");
            return false;
        }

        if(!$model->media()->where('filename', $filename)->exists())
        {
            Media::create([
                'description' => 'Fattura PDF '.$model->company->rag_soc,
                'mime' => 'doc',
                'filename' => $filename,
                'mediable_id' => $model->id,
                'mediable_type' => $mediable_type,
                'media_order' => 2,
                'size' => Storage::size('public/fe/pdf/'.$type_path.$filename)
            ]);
        }

        return true;
    }

    public function saveXmlInDB($originalName, $model, $mode)
    {
        if( $this->endsWith($originalName, '.p7m') )
        {
            $originalName = substr($originalName, 0, strrpos($originalName, '.p7m'));
        }

        $arr = explode('.',$originalName);
        $filename = $arr[0]."_".$model->id.".".$arr[1];

        $path = 'public/fe/';
        $path .= ($mode == 'in') ? 'ricevute' : 'inviate';
        $origin_path = $path.'/';
        $path .= '/'.$model->data->format('Y') . '/';

        if (file_exists(storage_path('app/'.$path.$filename)))
        {
            unlink(storage_path('app/'.$path.$filename));
        }
        Storage::move($origin_path.$originalName, $path.$filename);

        if(!$model->media()->where('filename', $model->data->format('Y').'/'.$filename)->exists())
        {
            Media::create([
                'description' => 'Fattura XML '.$model->numero,
                'mime' => 'doc',
                'filename' => $model->data->format('Y').'/'.$filename,
                'mediable_id' => $model->id,
                'mediable_type' => $model->full_class,
                'media_order' => 1,
                'size' => round( Storage::size($path.$filename) / 1000 )
            ]);
        }

        return true;
    }


}
