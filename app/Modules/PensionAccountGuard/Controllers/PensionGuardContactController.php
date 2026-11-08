<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PensionGuardContactController extends Controller
{
    public function show()
    {
        return view('pension-guard.public.contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        // Do not tell visitors that a message was delivered
        // when Laravel is only writing emails to its log.
        if (config('mail.default') === 'log') {
            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'The contact form is temporarily unavailable. Please email support@tekbiserv.co.uk directly.',
                ]);
        }

        $body = "New Pension Account Guard contact enquiry\n\n"
            . "Name: {$validated['name']}\n"
            . "Email: {$validated['email']}\n"
            . "Subject: {$validated['subject']}\n\n"
            . "Message:\n{$validated['message']}\n";

        try {
            Mail::raw($body, function ($mail) use ($validated) {
                $mail->to('support@tekbiserv.co.uk')
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('Pension Account Guard: ' . $validated['subject']);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'We could not send your message. Please email support@tekbiserv.co.uk directly.',
                ]);
        }

        return redirect()
            ->route('contact')
            ->with('status', 'Thank you. Your message has been sent successfully.');
    }
}
