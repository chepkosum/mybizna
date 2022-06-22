<?php

namespace Modules\Taxonomy\Entities;

use Modules\Core\Entities\BaseModel AS Model;
use Illuminate\Database\Schema\Blueprint;

class Tag extends Model
{

    protected $fillable = ['name'];
    public $migrationDependancy = [];
    protected $table = "taxonomy_tag";

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
