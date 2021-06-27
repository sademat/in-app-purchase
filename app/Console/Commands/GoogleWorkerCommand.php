<?php

namespace App\Console\Commands;

use App\Jobs\Worker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GoogleWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:googleworker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ios api için ilgili kayıtları database den çekiyoruz ve kuyruğa ekliyoruz';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //veritabanı tablosundan üyelik ve os değeri google olan kayıtları çekiyoruz
        $subscriptions=DB::table('subscription')->select(['subscription.*','device.os'])
            ->join('device','subscription.deviceid','=','device.id')
            ->where('subscription.expiredate','<',date('Y-m-d H:i:s'))
            ->where('device.os','google')
            ->where('subscription.status','!=','canceled')->get();
        if ($subscriptions->count()>0){
            foreach ($subscriptions as $subscription){
                $receipt= rand(1,100000);//random bir receipt değeri olşturuyoruz.
                Worker::dispatch($subscription,$receipt);//kuyruğa üyelik ve receipt değerlerini paramatre olarak gönderiyoruz
            }
        }
    }
}
