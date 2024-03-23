<?php

namespace App\Http\Controllers\LA;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class StatisticController extends Controller
{
    //
    public function index()
    {
        return View('la.statistics.index');
    }
}
