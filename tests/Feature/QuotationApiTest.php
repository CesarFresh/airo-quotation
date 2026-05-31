<?php
namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuotationApiTest extends TestCase
{
    use RefreshDatabase;
    public function test_quotation_requires_authentication(): void
    {
        $response = $this->postJson('/api/quotation', [
            'age'         => '28,35',
            'currency_id' => 'EUR',
            'start_date'  => '2020-10-01',
            'end_date'    => '2020-10-30',
        ]);
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_quotation(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/quotation', [
            'age'         => '28,35',
            'currency_id' => 'EUR',
            'start_date'  => '2020-10-01',
            'end_date'    => '2020-10-30',
        ]);

        $response->assertCreated()->assertJson([
            'total'        => 117,
            'currency_id'  => 'EUR',
            'quotation_id' => 1,
        ]);

        $this->assertDatabaseHas('quotations', [
            'ages'        => '28,35',
            'currency_id' => 'EUR',
            'trip_days'   => 30,
        ]);
    }

    public function test_rejects_age_outside_supported_range(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/quotation', [
            'age'         => '75',
            'currency_id' => 'EUR',
            'start_date'  => '2020-10-01',
            'end_date'    => '2020-10-30',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['age']);
    }
}