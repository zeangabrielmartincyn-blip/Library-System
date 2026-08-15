<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_otp_password_reset_flow(): void
    {
        Cache::flush();

        $user = User::factory()->create([
            'mobile_number' => '09171234567',
            'login_id' => 'STU12345',
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->post(route('password.email'), [
            'method' => 'mobile',
            'mobile' => '09171234567',
            'login_id' => 'STU12345',
        ]);

        $response->assertRedirect(route('password.otp.form', ['mobile' => '09171234567', 'login_id' => 'STU12345']));
        $response->assertSessionHas('status');

        $otp = Cache::get('password_otp:09171234567:STU12345');
        $this->assertNotNull($otp);

        $response = $this->post(route('password.otp.verify'), [
            'mobile' => '09171234567',
            'login_id' => 'STU12345',
            'otp' => (string) $otp,
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/reset-password/', $location);
        $this->assertStringContainsString('phone=09171234567', $location);
        $this->assertStringContainsString('login_id=STU12345', $location);
        $this->assertStringContainsString('phone_token=', $location);

        preg_match('#/reset-password/([^?]+)#', $location, $tokenMatches);
        $this->assertArrayHasKey(1, $tokenMatches);
        $token = $tokenMatches[1];

        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('09171234567', $query['phone']);
        $this->assertSame('STU12345', $query['login_id']);
        $this->assertSame($token, $query['phone_token']);

        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'phone' => $query['phone'],
            'login_id' => $query['login_id'],
            'phone_token' => $query['phone_token'],
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $resetResponse->assertRedirect(route('home'));
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
