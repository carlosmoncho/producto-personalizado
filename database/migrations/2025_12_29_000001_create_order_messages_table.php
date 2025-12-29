<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Dirección del mensaje
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing');

            // Contenido
            $table->string('subject');
            $table->longText('body');
            $table->string('body_format')->default('html'); // html | plain

            // Emails
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('to_name')->nullable();

            // Threading (para agrupar respuestas)
            $table->string('message_id')->unique()->nullable();
            $table->string('in_reply_to')->nullable()->index();

            // Adjuntos (JSON array)
            $table->json('attachments')->nullable();

            // Estado
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'read'])
                  ->default('pending');
            $table->text('error_message')->nullable();

            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index(['order_id', 'created_at']);
            $table->index(['order_id', 'direction']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_messages');
    }
};
