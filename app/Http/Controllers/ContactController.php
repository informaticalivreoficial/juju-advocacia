<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactRequest $request)
    {
        $validated = $request->validated();

        Mail::to(config('mail.contact_address'))
            ->send(new ContactMail(
                $validated['name'],
                $validated['email'],
                $validated['message'],
            ));

        return back()->with('success', 'Mensagem enviada com sucesso! Em breve entraremos em contato.');
    }
}
