<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Account\Classes\People;
use Modules\Account\Classes\Company;

use Illuminate\Support\Facades\DB;

class EmployeesController extends Controller
{
    /**
     * Get a collection of employees
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return mixed|object|\Illuminate\Http\Response
     */
    public function getEmployees(Request $request)
    {
        $args = [
            'number'      => $request['per_page'],
            'offset'      => ($request['per_page'] * ($request['page'] - 1)),
            'status'      => ($request['status']) ? $request['status'] : 'active',
            'department'  => ($request['department']) ? $request['department'] : '-1',
            'designation' => ($request['designation']) ? $request['designation'] : '-1',
            'location'    => ($request['location']) ? $request['location'] : '-1',
            's'           => ($request['s']) ? $request['s'] : '',
        ];

        $items = $hr->hrGetEmployees($args);

        $args['count'] = true;
        $total_items   = $hr->hrGetEmployees($args);
        $total_items   = is_array($total_items) ? count($total_items) : $total_items;

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        foreach ($items as $item) {
            $additional_fields['id'] = $item->user_id;

            $data              = $this->prepareItemForResponse($item, $request, $additional_fields);
            $formatted_items[] = $this->prepareResponseForCollection($data);
        }

        return response()->json($formatted_items);
    }

    /**
     * Get a specific employee
     *
     * @param \\Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function getEmployee(Request $request)
    {

        $people = new People();
        $people_id = (int) $request['id'];
        $user_id   = $people->getUserIdByPeopleId($people_id);

        $employee = new \WeDevs\ERP\HRM\Employee($user_id);
        $item     = (array) $people->getPeople($people_id);

        if (empty($item['id'])) {
            messageBag()->add('rest_employee_invalid_id', __('Invalid resource id.'), ['status' => 404]);
            return;
        }

        $item['designation']  = $employee->get_designation('view');
        $item['department']   = $employee->get_department('view');
        $item['reporting_to'] = $employee->get_reporting_to('view');
        $item['avatar']       = $employee->get_avatar();

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_employee_item_for_response($item, $request, $additional_fields);
        return response()->json($item);
    }

    /**
     * Get a collection of transactions
     *
     * @param \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function getTransactions(Request $request)
    {
        $people = new People();
        $args['people_id'] = (int) $request['id'];

        $transactions = $people->getPeopleTransactions($args);

        return response()->json($transactions);
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object          $item
     * @param \\Illuminate\Http\Request|null $request
     * @param array                 $additional_fields
     *
     * @return mixed|object|\Illuminate\Http\Response
     */
    public function prepareItemForResponse($item, Request $request = null, $additional_fields = [])
    {
        $item     = $item->data;
        $employee = new \WeDevs\ERP\HRM\Employee($item['user_id']);

        $data                = array_merge($item['work'], $item['personal'], $additional_fields);
        $data['user_id']     = $item['user_id'];
        $data['email']       = $item['user_email'];
        $data['people_id']   = $this->getPeopleIdByUserId($item['user_id']);
        $data['department']  = $employee->get_department('view');
        $data['designation'] = $employee->get_designation('view');


        return $data;
    }

    /**
     * Prepare a single employee output for response
     *
     * @param array|object          $item
     * @param \\Illuminate\Http\Request|null $request
     * @param array                 $additional_fields
     *
     * @return mixed|object|\Illuminate\Http\Response
     */
    public function prepareEmployeeItemForResponse($item, Request $request = null, $additional_fields = [])
    {
        // Wrap the data in a response object
        return response()->json($item);
    }

    /**
     * Prepare a single item for create or update
     *
     * @param \\Illuminate\Http\Request $request request object
     *
     * @return array $prepared_item
     */
    public function prepareItemFDatabase(Request $request)
    {
        $prepared_item = [];
        $company       = new Company();

        if (isset($request['id'])) {
            $prepared_item['id'] = $request['id'];
        }

        // required arguments.
        if (isset($request['first_name'])) {
            $prepared_item['personal']['first_name'] = $request['first_name'];
        }

        if (isset($request['last_name'])) {
            $prepared_item['personal']['last_name'] = $request['last_name'];
        }

        if (isset($request['employee_id'])) {
            $prepared_item['work']['employee_id'] = $request['employee_id'];
        }

        if (isset($request['email'])) {
            $prepared_item['user_email'] = $request['email'];
        }

        // optional arguments.
        if (isset($request['company'])) {
            $prepared_item['company'] = isset($request['company']) ? $request['company'] : $company->name;
        }

        if (isset($request['user_id'])) {
            $prepared_item['user_id'] = absint($request['user_id']);
        }

        if (isset($request['middle_name'])) {
            $prepared_item['personal']['middle_name'] = $request['middle_name'];
        }

        if (isset($request['designation'])) {
            $prepared_item['work']['designation'] = $request['designation'];
        }

        if (isset($request['department'])) {
            $prepared_item['work']['department'] = $request['department'];
        }

        if (isset($request['reporting_to'])) {
            $prepared_item['work']['reporting_to'] = $request['reporting_to'];
        }

        if (isset($request['location'])) {
            $prepared_item['work']['location'] = $request['location'];
        }

        if (isset($request['hiring_source'])) {
            $prepared_item['work']['hiring_source'] = $request['hiring_source'];
        }

        if (isset($request['hiring_date'])) {
            $prepared_item['work']['hiring_date'] = $request['hiring_date'];
        }

        if (isset($request['date_of_birth'])) {
            $prepared_item['work']['date_of_birth'] = $request['date_of_birth'];
        }

        if (isset($request['pay_rate'])) {
            $prepared_item['work']['pay_rate'] = $request['pay_rate'];
        }

        if (isset($request['pay_type'])) {
            $prepared_item['work']['pay_type'] = $request['pay_type'];
        }

        if (isset($request['type'])) {
            $prepared_item['work']['type'] = $request['type'];
        }

        if (isset($request['status'])) {
            $prepared_item['work']['status'] = $request['status'];
        }

        if (isset($request['other_email'])) {
            $prepared_item['personal']['other_email'] = $request['other_email'];
        }

        if (isset($request['phone'])) {
            $prepared_item['personal']['phone'] = $request['phone'];
        }

        if (isset($request['work_phone'])) {
            $prepared_item['personal']['work_phone'] = $request['work_phone'];
        }

        if (isset($request['mobile'])) {
            $prepared_item['personal']['mobile'] = $request['mobile'];
        }

        if (isset($request['address'])) {
            $prepared_item['personal']['address'] = $request['address'];
        }

        if (isset($request['gender'])) {
            $prepared_item['personal']['gender'] = $request['gender'];
        }

        if (isset($request['marital_status'])) {
            $prepared_item['personal']['marital_status'] = $request['marital_status'];
        }

        if (isset($request['nationality'])) {
            $prepared_item['personal']['nationality'] = $request['nationality'];
        }

        if (isset($request['driving_license'])) {
            $prepared_item['personal']['driving_license'] = $request['driving_license'];
        }

        if (isset($request['hobbies'])) {
            $prepared_item['personal']['hobbies'] = $request['hobbies'];
        }

        if (isset($request['user_url'])) {
            $prepared_item['personal']['user_url'] = $request['user_url'];
        }

        if (isset($request['description'])) {
            $prepared_item['personal']['description'] = $request['description'];
        }

        if (isset($request['street_1'])) {
            $prepared_item['personal']['street_1'] = $request['street_1'];
        }

        if (isset($request['street_2'])) {
            $prepared_item['personal']['street_2'] = $request['street_2'];
        }

        if (isset($request['city'])) {
            $prepared_item['personal']['city'] = $request['city'];
        }

        if (isset($request['country'])) {
            $prepared_item['personal']['country'] = $request['country'];
        }

        if (isset($request['state'])) {
            $prepared_item['personal']['state'] = $request['state'];
        }

        if (isset($request['postal_code'])) {
            $prepared_item['personal']['postal_code'] = $request['postal_code'];
        }

        if (isset($request['photo_id'])) {
            $prepared_item['personal']['photo_id'] = $request['photo_id'];
        }

        return $prepared_item;
    }

    /**
     * Get the query params for collections.
     *
     * @return array
     */
    public function getCollectionParams()
    {
        return [
            'context'  => $this->get_context_param(),
            'page'     => [
                'description'       => __('Current page of the collection.'),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'per_page' => [
                'description'       => __('Maximum number of items to be returned in result set.'),
                'type'              => 'integer',
                'default'           => 20,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'search'   => [
                'description'       => __('Limit results to those matching a string.'),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }
}
