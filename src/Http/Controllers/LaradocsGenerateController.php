<?php

namespace Ronanflavio\LaradocsGenerate\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class LaradocsGenerateController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        $path = resource_path('routes.json');
        $data = \GuzzleHttp\json_decode(file_get_contents($path));
        $data = array_values( (array) $data );
        return view('laradocs-generate::docs', compact('data'));
    }
}
