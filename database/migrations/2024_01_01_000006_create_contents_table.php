<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('content_type', [
                'article', 
                'announcement', 
                'guide', 
                'faq', 
                'tutorial', 
                'documentation', 
                'template', 
                'resource'
            ])->default('article');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['content_type', 'is_public']);
            $table->index(['is_public', 'created_at']);
            $table->index('views_count');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
};
