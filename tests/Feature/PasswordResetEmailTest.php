<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeUser(string $email = 'a@test.com'): User
    {
        return User::create([
            'name' => 'A', 'email' => $email, 'password' => 'oldpass1', 'role' => 'faculty',
        ]);
    }

    private function tokenFromSentMail(string $email): string
    {
        $token = null;

        Mail::assertSent(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($email, &$token) {
            if (! $mail->hasTo($email)) {
                return false;
            }

            parse_str((string) parse_url($mail->resetUrl, PHP_URL_QUERY), $query);
            $token = $query['token'] ?? null;

            return true;
        });

        $this->assertNotEmpty($token, 'The reset email did not carry a token.');

        return $token;
    }

    public function test_request_emails_a_reset_link_and_the_token_works(): void
    {
        Mail::fake();
        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email])->assertRedirect();

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
        Mail::assertSent(ResetPasswordMail::class, fn ($mail) => $mail->hasTo($user->email));

        $token = $this->tokenFromSentMail($user->email);

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpass1', $user->fresh()->password));

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_unknown_email_is_not_revealed_and_sends_nothing(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => 'nobody@test.com'])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('info');

        $this->assertDatabaseCount('password_reset_tokens', 0);
        Mail::assertNothingSent();
    }

    public function test_reset_rejects_a_wrong_token(): void
    {
        Mail::fake();
        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email]);

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => 'a-wrong-token',
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('oldpass1', $user->fresh()->password));
    }

    public function test_expired_token_is_refused_and_cleared(): void
    {
        Mail::fake();
        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email]);
        $token = $this->tokenFromSentMail($user->email);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])->assertRedirect(route('password.request'));

        $this->assertTrue(Hash::check('oldpass1', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_a_broken_mailer_does_not_leak_or_error(): void
    {
        $user = $this->makeUser();

        Mail::shouldReceive('to->send')->andThrow(new \RuntimeException('smtp down'));

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('info');
    }
}
