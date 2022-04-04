<?php

namespace Modules\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Designation extends Model
{

    protected $fillable = [];
    protected $table = "hrm_designation";

    /**
     * List of fields for managing postings.
     *
     * @param Blueprint $table
     * @return void
     */
    public function migration(Blueprint $table)
    {
        $table->unsignedInteger('id')->primary();
        $table->string('title', 200)->default('');
        $table->text('description')->nullable();
        $table->boolean('status')->default(1);
        $table->timestamps();
    }
}
