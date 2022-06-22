<?php

namespace Modules\Payment\Entities;

use Modules\Core\Entities\BaseModel AS Model;
use Illuminate\Database\Schema\Blueprint;

class PayBillDetail extends Model
{

    protected $fillable = ['voucher_no', 'bill_no', 'amount'];
    public $migrationDependancy = [];
    protected $table = "payment_pay_bill_detail";

    /**
     * List of fields for managing postings.
     *
     * @param Blueprint $table
     * @return void
     */
    public function migration(Blueprint $table)
    {
        $table->increments('id');
        $table->integer('voucher_no')->nullable();
        $table->integer('bill_no')->nullable();
        $table->decimal('amount', 20, 2)->default(0.00);
    }
}
