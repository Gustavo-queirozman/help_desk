<?php

namespace App\Http\Controllers;

use App\Services\GetMail;
use Illuminate\Http\Request;

class GetMailController extends Controller
{

    public function index(){
        $emails = new GetMail;
        return $emails;
    }
}
