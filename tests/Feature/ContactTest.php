<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_is_displayed(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Welcome'));
    }

    public function test_contact_form_can_be_submitted(): void
    {
        Mail::fake();

        $response = $this->post('/contato', [
            'name' => 'Maria Teste',
            'email' => 'maria@example.com',
            'message' => 'Olá, gostaria de mais informações.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertSent(ContactMail::class, function ($mail) {
            return $mail->name === 'Maria Teste'
                && $mail->email === 'maria@example.com'
                && $mail->message === 'Olá, gostaria de mais informações.';
        });
    }

    public function test_contact_form_requires_valid_data(): void
    {
        Mail::fake();

        $response = $this->from('/')->post('/contato', [
            'name' => '',
            'email' => 'email-invalido',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
        Mail::assertNothingSent();
    }
}
