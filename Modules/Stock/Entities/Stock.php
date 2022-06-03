<?php

namespace Modules\Stock\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Stock extends Model
{

    protected $fillable = ['name'];
    protected $migrationOrder = 10;
    protected $table = "stock";

    /**
     * List of fields for managing postings.
     *
     * @param Blueprint $table
     * @return void
     */
    public function migration(Blueprint $table)
    {
        $table->increments('id');
        $table->string('name');
    }
}
