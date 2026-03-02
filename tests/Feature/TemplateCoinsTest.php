<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ImagePromptTemplate;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TemplateCoinsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $template;
    protected $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        // Create a user
        $this->user = User::factory()->create();
        
        // Create a subscription plan with coins
        $plan = SubscriptionPlan::create([
            'name' => 'Premium Plan',
            'description' => 'Premium features',
            'price' => 29.99,
            'duration_type' => 'month',
            'duration_value' => 1,
            'coins' => 100,
            'features' => ['feature1', 'feature2'],
            'is_active' => true,
        ]);
        
        // Create active subscription for user
        $this->subscription = UserSubscription::create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'status' => 'active',
            'coins_used' => 0,
        ]);
        
        // Create a template with coins requirement
        $this->template = ImagePromptTemplate::create([
            'title' => 'Premium Effect',
            'type' => 'image',
            'description' => 'Premium template',
            'prompt' => 'Apply premium effect',
            'coins_required' => 10,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_shows_coins_required_in_template_list()
    {
        $response = $this->getJson('/api/templates');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'coins_required',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_deducts_coins_when_using_template()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/submissions', [
                'template_id' => $this->template->id,
                'original_image' => $image,
                'output_type' => 'image',
            ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'coins_deducted' => 10,
                'remaining_coins' => 90,
            ]);
        
        // Verify coins were deducted
        $this->subscription->refresh();
        $this->assertEquals(10, $this->subscription->coins_used);
        $this->assertEquals(90, $this->subscription->remaining_coins);
    }

    /** @test */
    public function it_rejects_submission_with_insufficient_coins()
    {
        // Use up most coins
        $this->subscription->update(['coins_used' => 95]);
        
        $image = UploadedFile::fake()->image('test.jpg');
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/submissions', [
                'template_id' => $this->template->id,
                'original_image' => $image,
                'output_type' => 'image',
            ]);
        
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'coins_required' => 10,
                'coins_available' => 5,
            ]);
    }

    /** @test */
    public function it_rejects_submission_without_active_subscription()
    {
        // Deactivate subscription
        $this->subscription->update(['status' => 'cancelled']);
        
        $image = UploadedFile::fake()->image('test.jpg');
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/submissions', [
                'template_id' => $this->template->id,
                'original_image' => $image,
                'output_type' => 'image',
            ]);
        
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You need an active subscription to use templates',
            ]);
    }

    /** @test */
    public function it_allows_free_templates_without_checking_coins()
    {
        // Create a free template
        $freeTemplate = ImagePromptTemplate::create([
            'title' => 'Free Effect',
            'type' => 'image',
            'description' => 'Free template',
            'prompt' => 'Apply free effect',
            'coins_required' => 0,
            'is_active' => true,
        ]);
        
        $image = UploadedFile::fake()->image('test.jpg');
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/submissions', [
                'template_id' => $freeTemplate->id,
                'original_image' => $image,
                'output_type' => 'image',
            ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'coins_deducted' => 0,
            ]);
        
        // Verify no coins were deducted
        $this->subscription->refresh();
        $this->assertEquals(0, $this->subscription->coins_used);
    }

    /** @test */
    public function admin_can_create_template_with_coins()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/templates', [
                'title' => 'New Premium Template',
                'type' => 'image',
                'description' => 'New premium template',
                'prompt' => 'Apply new effect',
                'coins_required' => 25,
                'is_active' => true,
            ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'coins_required' => 25,
                ]
            ]);
        
        $this->assertDatabaseHas('image_prompt_templates', [
            'title' => 'New Premium Template',
            'coins_required' => 25,
        ]);
    }

    /** @test */
    public function admin_can_update_template_coins()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/templates/{$this->template->id}", [
                'coins_required' => 15,
            ]);
        
        $response->assertStatus(200);
        
        $this->template->refresh();
        $this->assertEquals(15, $this->template->coins_required);
    }
}
