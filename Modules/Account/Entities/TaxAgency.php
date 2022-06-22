<?php

namespace Modules\Account\Entities;

use Modules\Core\Entities\BaseModel AS Model;
use Illuminate\Database\Schema\Blueprint;

class TaxAgency extends Model
{

    protected $fillable = ['name', 'ecommerce_type'];
    public $migrationDependancy = [];
    protected $table = "account_tax_agency";

    /**
     * List of fields for managing postings.
     *
     * @param Blueprint $table
     * @return void
     */
    public function migration(Blueprint $table)
    {
        $table->increments('id');
        $table->string('name')->nullable();
        $table->string('ecommerce_type')->nullable();
    }
}
