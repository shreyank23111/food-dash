<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RestaurantApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ownerName;
    public $restaurantName;

    public function __construct($ownerName, $restaurantName)
    {
        $this->ownerName = $ownerName;
        $this->restaurantName = $restaurantName;
    }

    public function build()
    {
        return $this->subject('✅ Your Restaurant is Approved! - FoodDash')
                    ->view('emails.restaurant_approved');
    }
}