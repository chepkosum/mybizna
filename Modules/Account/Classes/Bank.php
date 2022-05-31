<?php

/**
 * Bank Class
 */

namespace Modules\Account\Classes;

use Modules\Account\Classes\Reports\TrialBalance;
use Modules\Account\Classes\CommonFunc;
use Modules\Account\Classes\LedgerAccounts;
use Modules\Account\Classes\OpeningBalances;

use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\OpeningBalance;

/**
 * Bank
 */
class Bank
{


    /**
     * Get all bank accounts
     *
     * @param boolean $show_balance If to show balance
     * @param boolean $with_cash    If has cash
     * @param boolean $no_bank      No Bank
     *
     * @return array
     */
    public function getBanks($show_balance = false, $with_cash = false, $no_bank = false)
    {
        $trialbal = new TrialBalance();
        $ledger = new LedgerAccounts();
        $opening_balance = new OpeningBalance();

        $args               = [];
        $args['start_date'] = date('Y-m-d');

        $closest_fy_date    = $trialbal->getClosestFnYearDate($args['start_date']);
        $args['start_date'] = $closest_fy_date['start_date'];
        $args['end_date']   = $closest_fy_date['end_date'];

        $ledgers   = 'account_ledger';
        $show_all  = false;
        $cash_only = false;
        $bank_only = false;

        $chart_id    = 7;
        $cash_ledger = '';
        $where       = '';

        if ($with_cash && !$no_bank) {
            $where       = " WHERE chart_id = {$chart_id}";
            $cash_ledger = " OR slug = 'cash' ";
            $show_all    = true;
        }

        if ($with_cash && $no_bank) {
            $where       = ' WHERE';
            $cash_ledger = " slug = 'cash' ";
            $cash_only   = true;
        }

        if (!$with_cash && !$no_bank) {
            $where       = " WHERE chart_id = {$chart_id}";
            $cash_ledger = '';
            $bank_only   = true;
        }

        if (!$show_balance) {
            $query   = "SELECT * FROM $ledgers" . $where . $cash_ledger;
            $results = DB::select($query);

            return $results;
        }

        $sub_query      = "SELECT id FROM $ledgers" . $where . $cash_ledger;
        $ledger_details = 'account_ledger_detail';
        $query          = "Select l.id, ld.ledger_id, l.code, l.name, SUM(ld.debit - ld.credit) as balance
              From $ledger_details as ld
              LEFT JOIN $ledgers as l ON l.id = ld.ledger_id
              Where ld.ledger_id IN ($sub_query)
              Group BY ld.ledger_id";

        $temp_accts = DB::select($query);

        if ($with_cash) {
            // little hack to solve -> opening_balance cash entry with no ledger_details cash entry
            $cash_ledger = '7';
            $no_cash     = true;

            foreach ($temp_accts as $temp_acct) {
                if ($temp_acct['ledger_id'] === $cash_ledger) {
                    $no_cash = false;
                    break;
                }
            }

            if ($no_cash) {
                $temp_accts[] = ['id' => 7];
            }
        }

        $accts      = [];
        $bank_accts = [];
        $uniq_accts = [];

        $ledger_id  = get_ledger_id_by_slug('cash');

        $c_balance = $opening_balance->getLedgerBalanceWithOpeningBalance($ledger_id, $args['start_date'], $args['end_date']);
        $balance   = isset($c_balance->balance) ? $c_balance->balance : 0;

        foreach ($temp_accts as $temp_acct) {
            $bank_accts[] = $opening_balance->getLedgerBalanceWithOpeningBalance($temp_acct['id'], $args['start_date'], $args['end_date']);
        }

        if ($cash_only && !empty($accts)) {
            return $accts;
        }

        $banks = $ledger->getLedgersByChartId(7);

        if ($bank_only && empty($banks)) {
            messageBag('rest_empty_accounts', __('Bank accounts are empty.'));
            return false;
        }

        foreach ($banks as $bank) {
            $bank_accts[] = $opening_balance->getLedgerBalanceWithOpeningBalance($bank['id'], $args['start_date'], $args['end_date']);
        }

        $results = array_merge($accts, $bank_accts);

        foreach ($results as $index => $result) {
            if (!empty($uniq_accts) && in_array($result['id'], $uniq_accts, true)) {
                unset($results[$index]);
                continue;
            }
            $uniq_accts[] = $result['id'];
        }

        return $results;
    }

    /**
     * Get all accounts to show in dashboard
     *
     * @return mixed
     */
    public function getDashboardBanks()
    {
        $trialbal = new TrialBalance();
        $opening_balance = new OpeningBalance();

        $args               = [];
        $args['start_date'] = date('Y-m-d');

        $closest_fy_date    = $trialbal->getClosestFnYearDate($args['start_date']);
        $args['start_date'] = $closest_fy_date['start_date'];
        $args['end_date']   = $closest_fy_date['end_date'];

        $results = [];

        $ledger_id  = get_ledger_id_by_slug('cash');

        $c_balance = $opening_balance->getLedgerBalanceWithOpeningBalance($ledger_id, $args['start_date'], $args['end_date']);

        $results[] = [
            'name'    => __('Cash'),
            'balance' => isset($c_balance['balance']) ? $c_balance['balance'] : 0,
        ];

        $results[] = [
            'name'       => __('Cash at Bank'),
            'balance'    => $trialbal->cashAtBank($args, 'balance'),
            'additional' => $trialbal->bankBalance($args, 'balance'),
        ];

        $results[] = [
            'name'       => __('Bank Loan'),
            'balance'    => $trialbal->cashAtBank($args, 'loan'),
            'additional' => $trialbal->bankBalance($args, 'loan'),
        ];

        return $results;
    }

    /**
     * Get a single bank account
     *
     * @param int $bank_no Bank No
     *
     * @return mixed
     */
    public function getBank($bank_no)
    {

        $row = DB::select("SELECT * FROM account_cash_at_bank WHERE ledger_id = {$bank_no}");

        return (!empty($row)) ? $row[0] : null;
    }

    /**
     * Insert a bank account
     *
     * @param array $data sPassed Data
     *
     * @return int
     */
    public function insertBank($data)
    {


        $bank_data = $this->getFormattedBankData($data);

        try {
            DB::beginTransaction();

            DB::table('account_cash_at_bank')
                ->insert(
                    [
                        'ledger_id' => $bank_data['ledger_id'],
                    ]
                );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            return messageBag('bank-account-exception', $e->getMessage());
        }

        return $bank_data['ledger_id'];
    }

    /**
     * Delete a bank account
     *
     * @param int $id Bank Id
     *
     * @return int
     */
    public function deleteBank($id)
    {


        try {
            DB::beginTransaction();
            DB::table('account_cash_at_bank')->where([['ledger_id' => $id]])->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            messageBag('bank-account-exception', $e->getMessage());
            return;
        }

        return $id;
    }

    /**
     * Get formatted bank data
     *
     * @param array $bank_data Bank Data
     *
     * @return mixed
     */

    public function getFormattedBankData($bank_data)
    {
        $bank_data['ledger_id'] = !empty($bank_data['ledger_id']) ? $bank_data['ledger_id'] : 0;

        return $bank_data;
    }

    /**
     * Get balance of a single account
     *
     * @param int $ledger_id ledger Id
     *
     * @return mixed
     */
    public function getSingleAccountBalance($ledger_id)
    {


        $result = DB::select("SELECT ledger_id, SUM(credit) - SUM(debit) AS 'balance' FROM account_ledger_detail WHERE ledger_id = {$ledger_id}");

        return (!empty($result)) ? $result[0] : null;
    }

    /**
     * Get account debit credit
     *
     * @param int $ledger_id Ledger Id
     *
     * @return array
     */
    public function getAccountDebitCredit($ledger_id)
    {

        $dr_cr = [];

        $dr_cr['debit']  = DB::scalar("SELECT SUM(debit) FROM account_ledger_detail WHERE ledger_id = {$ledger_id}");
        $dr_cr['credit'] = DB::scalar("SELECT SUM(credit) FROM account_ledger_detail WHERE ledger_id = {$ledger_id}");

        return $dr_cr;
    }

    /**
     * Perform transfer amount between two account
     *
     * @param array $item Record to transfer
     *
     * @return mixed
     */
    public function performTransfer($item)
    {


        $common = new CommonFunc();
        $created_by = auth()->user()->id;
        $created_at = date('Y-m-d');
        $updated_at = date('Y-m-d');
        $updated_by = $created_by;
        $currency   = $common->getCurrency(true);

        try {
            DB::beginTransaction();

            $voucher_no =  DB::table('purchase_voucher_no')
                ->insertGetId(
                    [
                        'type'       => 'transfer_voucher',
                        'currency'   => $currency,
                        'created_at' => $created_at,
                        'created_by' => $created_by,
                        'updated_at' => $updated_at,
                        'updated_by' => $updated_by,
                    ]
                );


            // Inset transfer amount in ledger_details
            DB::table('account_ledger_detail')
                ->insert(
                    [
                        'ledger_id'   => $item['from_account_id'],
                        'trn_no'      => $voucher_no,
                        'particulars' => $item['particulars'],
                        'debit'       => 0,
                        'credit'      => $item['amount'],
                        'trn_date'    => $item['date'],
                        'created_at'  => $created_at,
                        'created_by'  => $created_by,
                        'updated_at'  => $updated_at,
                        'updated_by'  => $updated_by,
                    ]
                );

            DB::table('account_ledger_detail')
                ->insert(
                    [
                        'ledger_id'   => $item['to_account_id'],
                        'trn_no'      => $voucher_no,
                        'particulars' => $item['particulars'],
                        'debit'       => $item['amount'],
                        'credit'      => 0,
                        'trn_date'    => $item['date'],
                        'created_at'  => $created_at,
                        'created_by'  => $created_by,
                        'updated_at'  => $updated_at,
                        'updated_by'  => $updated_by,
                    ]
                );

            DB::table('purchase_transfer_voucher')
                ->insert(
                    [
                        'voucher_no'  => $voucher_no,
                        'amount'      => $item['amount'],
                        'ac_from'     => $item['from_account_id'],
                        'ac_to'       => $item['to_account_id'],
                        'particulars' => $item['particulars'],
                        'trn_date'    => $item['date'],
                        'created_at'  => $created_at,
                        'created_by'  => $created_by,
                        'updated_at'  => $updated_at,
                        'updated_by'  => $updated_by,
                    ]
                );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            messageBag('transfer-exception', $e->getMessage());
            return;
        }
    }

    /**
     * Sync dashboard account on transfer
     *
     * @return mixed
     */
    public function syncDashboardAccounts()
    {


        $accounts = $this->GetBanks(true, true, false);

        foreach ($accounts as $account) {
            DB::table('account_cash_at_bank')

                ->where(
                    [
                        'ledger_id' => $account['ledger_id'],
                    ]
                )
                ->update(
                    [
                        'balance' => $account['balance'],
                    ]
                );
        }
    }

    /**
     * Get transferrable accounts
     *
     * @param boolean $show_balance Show Balance
     *
     * @return array
     */
    public function getTransferAccounts($show_balance = false)
    {
        $results = $this->GetBanks(true, true, false);

        return $results;
    }

    /**
     * Get created Transfer voucher list
     *
     * @param array $args Vourchers Filters
     *
     * @return array
     */
    public function getTransferVouchers($args = [])
    {


        $defaults = [
            'number'   => 20,
            'offset'   => 0,
            'order_by' => 'id',
            'order'    => 'DESC',
            'count'    => false,
            's'        => '',
        ];

        $args = array_merge($defaults, $args);

        $limit = '';

        if (-1 !== $args['number']) {
            $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
        }

        $result = DB::select("SELECT * FROM purchase_transfer_voucher ORDER BY {$args['order_by']} {$args['order']} {$limit}");

        return $result;
    }

    /**
     * Get single voucher
     *
     * @param int $id Voucher id
     *
     * @return object Single voucher
     */
    public function getSingleVoucher($id)
    {


        if (!$id) {
            return;
        }

        $result = DB::select("SELECT * FROM purchase_transfer_voucher WHERE id = {$id}");

        return (!empty($result)) ? $result[0] : null;
    }

    /**
     * Get balance by Ledger ID
     *
     * @param int $id Ledger Id
     *
     * @return array
     */
    public function getBalanceByLedger($id)
    {
        if (is_array($id)) {
            $id = "'" . implode("','", $id) . "'";
        }


        $table_name = 'account_ledger_detail';
        $query      = "Select ld.ledger_id,SUM(ld.debit - ld.credit) as balance From $table_name as ld Where ld.ledger_id IN ($id) Group BY ld.ledger_id ";
        $result     = DB::select($query);

        return $result;
    }

    /**
     * Get bank accounts dropdown with cash
     *
     * @return array
     */
    public function getBankDropdown()
    {
        $accounts = [];
        $banks    = $this->GetBanks(true, true, false);

        if ($banks) {
            foreach ($banks as $bank) {
                $accounts[$bank['id']] = sprintf('%s', $bank['name']);
            }
        }

        return $accounts;
    }
}
