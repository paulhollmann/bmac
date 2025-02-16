<?php

namespace App\Http\Livewire;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use Livewire\Component;
use App\Enums\BookingStatus;

class Bookings extends Component
{
    public Event $event;
    public int $refreshInSeconds = 0;
    public $bookings;
    public ?string $filter = null;
    public int $total = 0;
    public int $booked = 0;
    public bool $display_eobt;
    public bool $display_ctot ;

    public function filter($filter)
    {
        $this->filter = strtolower($filter);
    }

    public function mount()
    {
        // Only enable polling if event is 'active'
        if (now()->between($this->event->startBooking, $this->event->endEvent)) {
            $this->refreshInSeconds = 60;
        }
    }

    public function render()
    {
        $filter = $this->filter;
        // @TODO Check should actually be in a policy
        if ($this->event->is_online || auth()->check() && auth()->user()->isAdmin) {
            $this->bookings = $this->event->bookings()
                ->with([
                    'event',
                    'user',
                    'flights' => function ($query) use ($filter) {
                        switch ($filter) {
                            case 'departures':
                                $query->where('dep', $this->event->dep)
                                    ->orderBy('ctot');
                                break;
                            case 'arrivals':
                                $query->where('arr', $this->event->arr)
                                    ->orderBy('eta');
                                break;
                            default:
                                $query->orderBy('eta')
                                    ->orderBy('ctot');
                        }
                    },
                    'flights.airportDep',
                    'flights.airportArr',
                ])
                ->withCount('flights')
                ->get();
        } else {
            abort_unless(auth()->check() && auth()->user()->isAdmin, 404);
        }

        $this->booked = $this->bookings->where('status', BookingStatus::BOOKED)->count();

        $this->total = $this->bookings->count();


        $this->display_ctot = $this->event->uses_times;
        $this->display_eobt = $this->event->uses_times;

        return view('livewire.bookings');
    }
}
