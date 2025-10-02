<?php

namespace Fiachehr\Comments\Tests;

use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    public function test_comment_model_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Models\Comment::class));
    }

    public function test_reaction_model_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Models\Reaction::class));
    }

    public function test_comments_service_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Services\CommentsService::class));
    }

    public function test_reaction_service_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Services\ReactionService::class));
    }

    public function test_has_comments_trait_exists()
    {
        $this->assertTrue(trait_exists(\Fiachehr\Comments\Traits\HasComments::class));
    }

    public function test_comment_status_enum_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Enums\CommentStatusType::class));
    }

    public function test_reaction_type_enum_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Enums\ReactionType::class));
    }

    public function test_comment_created_event_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Events\CommentCreated::class));
    }

    public function test_reaction_toggled_event_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Events\ReactionToggled::class));
    }

    public function test_comments_facade_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Facades\Comments::class));
    }

    public function test_reactions_facade_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Facades\Reactions::class));
    }

    public function test_guest_fingerprint_helper_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Helper\GuestFingerprint::class));
    }

    public function test_guest_fingerprint_rule_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Rules\GuestFingerPrintRule::class));
    }

    public function test_recaptcha_verifier_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\Services\RecaptchaVerifier::class));
    }

    public function test_service_provider_exists()
    {
        $this->assertTrue(class_exists(\Fiachehr\Comments\CommentsServiceProvider::class));
    }
}
