<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Models\User;
use Tests\TestCase;

class PostUnitTest extends TestCase
{
    public function test_category_values_labels_and_options(): void
    {
        $this->assertCount(6, PostCategory::values());
        $this->assertContains('Gaya Hidup', PostCategory::values());

        foreach (PostCategory::cases() as $case) {
            $this->assertNotSame('', $case->label());
        }

        $this->assertSame(['value' => 'Umum', 'label' => 'Umum'], PostCategory::options()[5]);
    }

    public function test_policy_allows_only_owner(): void
    {
        $owner = new User;
        $owner->id = 1;
        $stranger = new User;
        $stranger->id = 2;
        $post = new Post(['user_id' => 1]);

        $policy = new PostPolicy;

        $this->assertTrue($policy->manage($owner, $post));
        $this->assertFalse($policy->manage($stranger, $post));
    }

    public function test_user_role_helpers(): void
    {
        $admin = new User(['role' => User::ROLE_ADMIN]);
        $member = new User(['role' => User::ROLE_MEMBER]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($member->isAdmin());
    }

    public function test_generated_item_defaults(): void
    {
        $item = GeneratedPostItem::fromArray([]);

        $this->assertSame('Artikel Tanpa Judul', $item->title);
        $this->assertSame('Umum', $item->category);
        $this->assertSame('', $item->content);
    }
}
