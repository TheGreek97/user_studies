<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function download_info_sheet(Request $request)
    {
        //Storage::disk('local')->put('example.txt', 'Contents');
        $file_name = "information-sheet-for-anonymous-studies.pdf";
        //$file_public_path = asset('storage/'. $file_name);
        try {
            return response()->file("storage/$file_name");
        } catch (FileNotFoundException) {
            abort(404, 'File not found');
        }
    }
}
