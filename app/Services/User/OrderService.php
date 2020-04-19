<?php

namespace App\Services\User;

use Carbon\Carbon;
use App\Models\Order;
use App\Events\Order\Created;
use App\Events\Order\Updated;
use App\Models\CollectionPointTimeSlot;

class OrderService
{
    public function list($filters)
    {
        return Order::with(['collectionPoint', 'collectionPointTimeSlot'])
                    ->where('user_id', auth()->user()->id)
                    ->when(array_key_exists('required_date', $filters), function ($query) use ($filters) {
                        $query->whereDate('required_date', $filters['required_date']);
                    })
                    ->get();
    }

    public function get(Order $order)
    {
        return Order::with(['collectionPoint', 'collectionPointTimeSlot'])
                    ->where('user_id', auth()->user()->id)
                    ->where('id', $order->id)
                    ->first();
    }

    public function create($data)
    {
        $user = auth()->user();

        $data['required_date'] = now();

        $order = $user->orders()->create($data);

        event(new Created($order));

        return $order;
    }

    public function update(Order $order, $data)
    {
        $data['required_date'] = $order->required_date;

        $order->update($data);

        event(new Updated($order));

        return $order->fresh();
    }

    public function delete(Order $order)
    {
        $order->delete();
    }

    public function timeSlotBelongsToCollectionPoint($timeSlotId, $collectionPointId)
    {
        return CollectionPointTimeSlot::where('id', $timeSlotId)
                                      ->where('collection_point_id', $collectionPointId)
                                      ->first();
    }

    public function getFillable($collection)
    {
        return $collection->only(
            with((new Order())->getFillable())
        );
    }

    public function canOrder()
    {
        $result = [
            'user_can_order'             => false,
            'user_has_ordered_today'     => true,
            'time_passed_daily_deadline' => true,
        ];

        $user = auth()->user();

        // check if user has already ordered today
        $todaysOrderCount = $user->orders()
                                 ->whereDate('created_at', Carbon::today())
                                 ->count();

        $result['user_has_ordered_today'] = $todaysOrderCount > 0;

        // check if between 12am and 2pm
        $now   = Carbon::now();
        $start = Carbon::createFromTimeString('00:00');
        $end   = Carbon::createFromTimeString('14:00');

        $result['time_passed_daily_deadline'] = ! $now->between($start, $end);

        // update can order status
        $result['user_can_order'] = ! $result['user_has_ordered_today'] && ! $now->between($start, $end);

        return $result;
    }
}
