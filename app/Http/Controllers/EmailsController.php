<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

class EmailsController extends Controller
{
    public function welcomeEmail()
    {
        Mail::to('japzdiapana@gmail.com')->send(new WelcomeMail);

        return 'Email Sent';
    }
}
