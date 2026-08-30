<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendPosSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $number;
    protected $message;

    public function __construct($number, $message)
    {
        $this->number = $number;
        $this->message = $message;
    }

    public function handle()
    {
        Http::get("http://bulksmsbd.net/api/smsapi", [
            'api_key'  => 'TCt8U03GtAXtCN0KTKAa',
            'type'     => 'text',
            'number'   => $this->number,
            'senderid' => '8809648904744',
            'message'  => $this->message
        ]);
    }
}