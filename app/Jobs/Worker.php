<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Worker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $subscription;
    public $receipt;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subscription,$receipt)
    {
        $this->subscription=$subscription;
        $this->receipt=$receipt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //check ios api
        if ($this->subscription->os="ios"){
            //check ios api vagrant kullandığım için 192.168.10.10 ip sini verdim localhost ipsini verince timeout a düşüyordu
            $response = Http::post('http://192.168.10.10/api/v1/iosMockApi', [
                'receipt' => $this->receipt
            ]);

        }
        else{
            //check google api vagrant kullandığım için 192.168.10.10 ip sini verdim localhost ipsini verince timeout a düşüyordu
            $response = Http::post('http://192.168.10.10/api/v1/googleMockApi', [
                'receipt' => $this->receipt
            ]);
        }
        $mockapi=$response->json();
        if (isset($mockapi['expiredate']))//mockapi lerden gelen değerlerde expiredate set edilmiş ise
        {   //üyelik tablosu günceleniyor
            DB::table('subscription')->where('deviceid',$this->subscription->deviceid)
                ->update(['expiredate'=>$mockapi['expiredate'],
                    'status'=>$mockapi['subscription_status'],
                    'udate'=>date('Y-m-d H:i:s')]);
        }
        if (isset($mockapi['err_code']) && $mockapi['err_code']=="rete_limit"){
            //mockapilerden err_code=rate_limit olarak gelmiş ise uyelik bilgisi tekrar sorgulanmak üzere kuyruğa ekleniyor
            Worker::dispatch($this->subscription,$this->receipt);
        }

    }
}
