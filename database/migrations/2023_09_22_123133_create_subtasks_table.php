<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['todo', 'done']);
            $table->integer('priority');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('task_id')->nullable(); // This field will be used to communicate with the parent task
            $table->unsignedBigInteger('parent_subtask_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_id')->references('id')->on('tasks');
            $table->foreign('parent_subtask_id')->references('id')->on('subtasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subtasks');
    }
};
