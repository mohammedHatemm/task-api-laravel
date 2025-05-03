<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Models\Customer;

class OrderPolicy
{

    public function modify(Customer $customer, Order $order): Response
    {
        //
        return $customer->id === $order->customer_id
            ? Response::allow()
            : Response::deny('You do not own this order.');
    }
}
