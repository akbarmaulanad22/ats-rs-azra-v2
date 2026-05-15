<?php

namespace App\Services;

use App\Mail\TemplatedMail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    /**
     * Dispatch an email notification using a stored template.
     *
     * @param  string  $key  Template key (e.g. 'lamaran_diterima')
     * @param  string  $toEmail  Recipient email address
     * @param  array<string, string>  $payload  Placeholder values keyed by placeholder name (e.g. ['nama_kandidat' => 'Budi'])
     */
    public function dispatch(string $key, string $toEmail, array $payload = []): void
    {
        $template = EmailTemplate::where('key', $key)->first();

        if (! $template) {
            return;
        }

        $subject = $this->render($template->subjek, $payload);
        $body = $this->render($template->isi, $payload);

        Mail::to($toEmail)->send(new TemplatedMail($subject, $body, $key));
    }

    /**
     * Replace {placeholder} tokens in a string with payload values.
     *
     * @param  array<string, string>  $payload
     */
    public function render(string $text, array $payload): string
    {
        $search = array_map(fn (string $k) => '{'.$k.'}', array_keys($payload));
        $replace = array_values($payload);

        return str_replace($search, $replace, $text);
    }
}
