<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Account\Classes\CommonFunc;
use Modules\Account\Classes\People;
use Modules\Account\Classes\Purchases;

use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{

    /**
     * Get a collection of purchases
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchases(Request $request)
    {
        $purchases = new Purchases();
        $args = [
            'number' => (int) !empty($request['per_page']) ? intval($request['per_page']) : 20,
            'offset' => ($request['per_page'] * ($request['page'] - 1)),
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = __NAMESPACE__;

        $purchase_data = $purchases->getPurchases($args);
        $total_items   = $purchases->getPurchases(
            [
                'count'  => true,
                'number' => -1,
            ]
        );

        foreach ($purchase_data as $item) {
            if (isset($request['include'])) {
                $include_params = explode(',', str_replace(' ', '', $request['include']));

                if (in_array('created_by', $include_params, true)) {
                    $item['created_by'] = $this->get_user($item['created_by']);
                }
            }

            $formatted_items[]              = $this->prepareItemForResponse($item, $request, $additional_fields);
        }

        return response()->json($formatted_items);
    }

    /**
     * Get a collection of purchases with due of a vendor
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function duePurchases(Request $request)
    {
        $id = (int) $request['id'];

        if (empty($id)) {
            config('kernel.messageBag')->add('rest_bill_invalid_id', __('Invalid resource id.'));
            return;
        }

        $args = [];

        $args['number']    = !empty($request['per_page']) ? $request['per_page'] : 20;
        $args['offset']    = ($args['number'] * (intval($request['page']) - 1));
        $args['vendor_id'] = $id;
        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = __NAMESPACE__;

        $puchase_data = $this->getDuePurchasesByVendor($args);
        $total_items  = count($puchase_data);

        foreach ($puchase_data as $item) {
            if (isset($request['include'])) {
                $include_params = explode(',', str_replace(' ', '', $request['include']));

                if (in_array('created_by', $include_params, true)) {
                    $item['created_by'] = $this->get_user($item['created_by']);
                }
            }

            $item['line_items'] = []; // TEST?

            $formatted_items[]              = $this->prepareItemForResponse($item, $request, $additional_fields);
        }

        return response()->json($formatted_items);
    }

    /**
     * Get a purchase
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchase(Request $request)
    {
        $purchases = new Purchases();
        $id = (int) $request['id'];

        if (empty($id)) {
            config('kernel.messageBag')->add('rest_purchase_invalid_id', __('Invalid resource id.'));
            return;
        }

        $item = $purchases->getPurchases($id);

        $additional_fields['namespace'] = __NAMESPACE__;

        $item     = $this->prepareItemForResponse($item, $request, $additional_fields);
        return response()->json($item);
    }

    /**
     * Create a purchase
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function createPurchase(Request $request)
    {
        $purchase_data  = $this->prepareItemFDatabase($request);
        $items          = $request['line_items'];
        $item_total     = [];
        $item_tax_total = [];

        foreach ($items as $key => $item) {
            $item_total[$key]      = $item['item_total'];
            $item_tax_total[$key]  = !empty($item['tax_amount']) ? $item['tax_amount'] : 0;
        }

        $purchase_data['tax']           = array_sum($item_tax_total);
        $purchase_data['amount']        = array_sum($item_total) + $purchase_data['tax'];
        $purchase_data['attachments']   = maybe_serialize($request['attachments']);
        $additional_fields['namespace'] = __NAMESPACE__;

        $purchase_data = $this->insertPurchase($purchase_data);

        $this->addLog($purchase_data, 'add');

        $purchase_data = $this->prepareItemForResponse($purchase_data, $request, $additional_fields);

        return response()->json($purchase_data);
    }

    /**
     * Update a purchase
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePurchase(Request $request)
    {
        $common = new CommonFunc();
        $purchases = new Purchases();

        $id = (int) $request['id'];

        if (empty($id)) {
            config('kernel.messageBag')->add('rest_purchase_invalid_id', __('Invalid resource id.'));
            return;
        }

        $can_edit = $common->checkVoucherEditState($id);

        if (!$can_edit) {
            config('kernel.messageBag')->add('rest_purchase_invalid_edit', __('Invalid edit permission for update.'));
            return;
        }

        $purchase_data = $this->prepareItemFDatabase($request);

        $items      = $request['line_items'];
        $item_total = [];
        $tax_total  = [];

        foreach ($items as $key => $item) {
            $item_total[$key] = $item['item_total'];
            $tax_total[$key]  = $item['tax_amount'];
        }

        $purchase_data['attachments']     = maybe_serialize($purchase_data['attachments']);
        $purchase_data['billing_address'] = isset($purchase_data['billing_address']) ? maybe_serialize($purchase_data['billing_address']) : '';
        $purchase_data['amount']          = array_sum($item_total);
        $purchase_data['tax']             = array_sum($tax_total);

        $old_data = $purchases->getPurchases($id);
        $purchase = $purchases->updatePurchase($purchase_data, $id);

        $this->addLog($purchase_data, 'edit', $old_data);

        $additional_fields['namespace'] = __NAMESPACE__;

        $purchase_data = $this->prepareItemForResponse($purchase, $request, $additional_fields);

        return response()->json($purchase_data);
    }

    /**
     * Void a purchase
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Request
     */
    public function voidPurchase(Request $request)
    {
        $id = (int) $request['id'];

        if (empty($id)) {
            config('kernel.messageBag')->add('rest_purchase_invalid_id', __('Invalid resource id.'));
            return;
        }

        $this->voidPurchase($id);

        return response()->json(['status' => true]);
    }

    /**
     * Log for inventory purchase related actions
     *
     * @param array $data
     * @param string $action
     * @param array $old_data
     *
     * @return void
     */
    public function addLog($data, $action, $old_data = [])
    {
        $common = new CommonFunc();
        switch ($action) {
            case 'edit':
                $operation = 'updated';
                $changes   = !empty($old_data) ? $common->getArrayDiff($data, $old_data) : [];
                break;
            case 'delete':
                $operation = 'deleted';
                break;
            default:
                $operation = 'created';
        }
    }

    /**
     * Prepare a single item for create or update
     *
     * @param \Illuminate\Http\Request $request request object
     *
     * @return array $prepared_item
     */
    protected function prepareItemFDatabase(Request $request)
    {
        $prepared_item = [];

        if (isset($request['vendor_id'])) {
            $prepared_item['vendor_id'] = $request['vendor_id'];
        }

        if (isset($request['vendor_name'])) {
            $prepared_item['vendor_name'] = $request['vendor_name'];
        }

        if (isset($request['ref'])) {
            $prepared_item['ref'] = $request['ref'];
        }

        if (isset($request['trn_date'])) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }

        if (isset($request['due_date'])) {
            $prepared_item['due_date'] = $request['due_date'];
        }

        if (isset($request['particulars'])) {
            $prepared_item['particulars'] = $request['particulars'];
        }

        if (isset($request['type'])) {
            $prepared_item['type'] = $request['type'];
        }

        if (isset($request['status'])) {
            $prepared_item['status'] = $request['status'];
        }

        if (isset($request['purchase_order'])) {
            $prepared_item['purchase_order'] = $request['purchase_order'];
        }

        if (isset($request['line_items'])) {
            $prepared_item['line_items'] = $request['line_items'];
        }

        if (isset($request['tax_rate'])) {
            $prepared_item['tax_rate'] = $request['tax_rate'];
        }

        if (isset($request['attachments'])) {
            $prepared_item['attachments'] = maybe_serialize($request['attachments']);
        }

        if (isset($request['billing_address'])) {
            $prepared_item['billing_address'] = maybe_serialize($request['billing_address']);
        }

        if (isset($request['convert'])) {
            $prepared_item['convert'] = $request['convert'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object    $item
     * @param \Illuminate\Http\Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return \Illuminate\Http\Response $response response data
     */
    public function prepareItemForResponse($item, Request $request, $additional_fields = [])
    {

        $people = new People();
        $purchases = new Purchases();

        $item = (object) $item;

        $data = [
            'id'             => (int) $item->id,
            'editable'       => (int) $item->editable,
            'vendor_id'      => (int) $item->vendor_id,
            'voucher_no'     => (int) $item->voucher_no,
            'vendor_name'    => $item->vendor_name,
            'date'           => $item->trn_date,
            'due_date'       => $item->due_date,
            'line_items'     => $item->line_items,
            'type'           => !empty($item->type) ? $item->type : 'purchase',
            'tax'            => $item->tax,
            'tax_zone_id'    => $item->tax_zone_id,
            'ref'            => $item->ref,
            'billing_address' => $people->formatPeopleAddress($people->getPeopleAddress((int) $item->vendor_id)),
            'pdf_link'       => $item->pdf_link,
            'status'         => $item->status,
            'purchase_order' => $item->purchase_order,
            'amount'         => $item->amount,
            'created_at'     => $item->created_at,
            'due'            => empty($item->due) ? $purchases->getPurchaseDue($item->voucher_no) : $item->due,
            'attachments'    => maybe_unserialize($item->attachments),
            'particulars'    => $item->particulars,
        ];

        $data = array_merge($data, $additional_fields);


        return $data;
    }

    /**
     * Get the User's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function getItemSchema()
    {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'purchase',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __('Unique identifier for the resource.'),
                    'type'        => 'integer',
                    'context'     => ['embed', 'view', 'edit'],
                    'readonly'    => true,
                ],
                'voucher_no'  => [
                    'description' => __('Voucher no. for the resource.'),
                    'type'        => 'integer',
                    'context'     => ['edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'vendor_id'   => [
                    'description' => __('Customer id for the resource.'),
                    'type'        => 'integer',
                    'context'     => ['edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'vendor_name' => [
                    'description' => __('Customer id for the resource.'),
                    'type'        => 'integer',
                    'context'     => ['edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'trn_date'    => [
                    'description' => __('Date for the resource.'),
                    'type'        => 'string',
                    'context'     => ['edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'due_date'    => [
                    'description' => __('Due date for the resource.'),
                    'type'        => 'string',
                    'context'     => ['edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'line_items'  => [
                    'description' => __('List of line items data.'),
                    'type'        => 'array',
                    'context'     => ['view', 'edit'],
                    'properties'  => [
                        'product_id'   => [
                            'description' => __('Product id.'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                        ],
                        'product_type' => [
                            'description' => __('Product type.'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                        ],
                        'qty'          => [
                            'description' => __('Product quantity.'),
                            'type'        => 'integer',
                            'context'     => ['view', 'edit'],
                        ],
                        'unit_price'   => [
                            'description' => __('Unit price.'),
                            'type'        => 'integer',
                            'context'     => ['view', 'edit'],
                        ],
                        'discount'     => [
                            'description' => __('Discount.'),
                            'type'        => 'integer',
                            'context'     => ['view', 'edit'],
                        ],
                        'tax'          => [
                            'description' => __('Tax.'),
                            'type'        => 'integer',
                            'context'     => ['edit'],
                        ],
                        'tax_percent'  => [
                            'description' => __('Tax percent.'),
                            'type'        => 'integer',
                            'context'     => ['view', 'edit'],
                        ],
                        'item_total'   => [
                            'description' => __('Item total.'),
                            'type'        => 'integer',
                            'context'     => ['edit'],
                        ],
                    ],
                ],
                'type'        => [
                    'description' => __('Type for the resource.'),
                    'type'        => 'string',
                    'context'     => ['edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'      => [
                    'description' => __('Status for the resource.'),
                    'type'        => 'string',
                    'context'     => ['edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];

        return $schema;
    }
}
