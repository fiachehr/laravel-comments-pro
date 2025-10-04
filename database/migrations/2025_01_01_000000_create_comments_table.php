<?php

use Fiachehr\Comments\Enums\CommentStatusType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_ip', 45)->nullable();
            $table->text('body');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedSmallInteger('depth')->default(0);
            $table->enum('status', array_column(CommentStatusType::cases(), 'value'))->default(CommentStatusType::PENDING->value);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
