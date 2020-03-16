<?php

namespace Buzzex\Events;

use Illuminate\Queue\SerializesModels;

class DevelopersEvent
{
    use SerializesModels;
    
    /**
     * @var $data
     */
    public $data;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data = array())
    {
        $this->data = $data;
        if (!array_key_exists('subject', $data)) {
            $this->data['subject'] = "Notification for Developers.";
        }
    }
}
