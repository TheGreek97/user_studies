<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function download_info_sheet(Request $request)
    {
        $file_name = "information-sheet-for-anonymous-studies.pdf";
        $file_in_storage = "storage/downloads/" . $file_name;
        $file_public_path = "public/downloads/".$file_name;
        error_log("##d####");

        // Ensure the file exists in storage
        if (Storage::exists($file_public_path)) {
            //$file_size = Storage::size($file_public_path);
            error_log("###############");
            return response()->download($file_in_storage, $file_name);
        } else {
            abort(404, 'File not found');
        }
    }
}
