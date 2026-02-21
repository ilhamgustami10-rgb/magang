<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AirlineController extends Controller
{
    public function index() {
        return view('admin.airlines.index');
    }
}