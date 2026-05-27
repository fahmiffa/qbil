<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendRegistrationEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $email,
        public string $name,
        public string $password
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Mail::to($this->email)->send(
            new \App\Mail\RegistrationPasswordMail(
                $this->name,
                $this->email,
                $this->password
            )
        );
    }
}
