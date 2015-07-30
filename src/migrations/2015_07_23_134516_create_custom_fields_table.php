<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     * based on JIRA custom fields schema
     * https://developer.atlassian.com/jiradev/jira-architecture/database-schema/database-custom-fields
     * @return void
     */
    public function up()
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('field_name', 255);
            $table->string('parent_type', 255);
            $table->unsignedInteger('parent_id');
            $table->string('stringvalue', 255)->nullable();
            $table->double('numbervalue')->nullable();
            $table->text('textvalue')->nullable();
            $table->timestamp('datevalue')->nullable();

            $table->unique(['field_name', 'parent_type', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->drop();
        });
    }
}
