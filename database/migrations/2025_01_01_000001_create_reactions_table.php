<?php

use Fiachehr\Comments\Enums\ReactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_fingerprint', 128)->nullable()->index();
            $table->enum('type', array_column(ReactionType::cases(), 'value'));
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
            $table->unique(['comment_id', 'guest_fingerprint']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
