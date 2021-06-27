<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
class ApiController extends Controller
{
    /*
     * mobil cihazlar ve bu cihazlar üzerinde bulunan aplicationların kayotlarını yapıldığı fonksiyon
     */
    public function register(Request $request){
        $token="";
        $msg="Register OK";
        //device tablosunda uidve appid kontrolleri yapıyoruz
        $device=DB::table('device')
            ->where('uid',$request->uid)
            ->where('appid',$request->appid)
            ->get()->first();

        if (!$device){//eğer kayıtlı değil ise kayıt ediyoruz
            $msg="Register OK";
            $token=Crypt::encryptString($request->uid.$request->appid);//token oluşturuyoruz
            DB::table('device')->insert(['uid'=>$request->uid,'appid'=>$request->appid,
                'lang'=>$request->lang,'os'=>$request->os,
                'cdate'=>date('Y-m-d H:i:s'),'clienttoken'=>$token]);
        }
        else{
            $token=Crypt::encryptString($device->uid.$device->appid);//token oluşturuyoruz
        }
        return response()->json(array('token'=>$token,'msg'=>$msg));
    }
    //purchase uygulama içi satın alma api si
    /*
     *  @token ve @receipt parametrelerini alır
     */
    public function purchase(Request $request){
        $token=$request->token; //gelen token
        $receipt=$request->receipt; //gelen reciept değeri
        //dd($receipt);
        if ($token){//token boşdeğilse devam ediyoruz
            $device=DB::table('device')->where('clienttoken',$token)->get()->first();//device tablsundan ilgi kaydı buluyoruz

            if ($device){
                if ($device->os=="ios"){//device ios ise iosmocapi methodu çağırılıyor
                    $mockapi = Http::post('http://127.0.0.1:8000/iosMockApi', [
                        'receipt' => $receipt
                    ]);
                    dd($mockapi);
                    //$mockapi=$this->iosMockApi($receipt);
                }
                else{
                    $mockapi = Http::post('http://127.0.0.1:8000/googleMockApi', [
                        'receipt' => $receipt
                    ]);
                    //$mockapi=$this->googleMockApi($receipt);
                }
                if ($mockapi){//mockapi değeri set edilmiş ise devam ediyoruz
                    if ($mockapi['status']){//mockapi de doğrulama gerçekleşir ise işleme devam ediyoruz
                        $subscrpt=DB::table('subscription')->where('deviceid',$device->id)->get();//ilgi device ın abonelik bilgileri çekiliyor
                        if ($subscrpt->count()>0){
                            //abonelik tablosu güncelleniyor
                            DB::table('subscription')->where('deviceid',$device->id)
                                ->update(['expiredate'=>$mockapi['expiredate'],
                                    'status'=>$mockapi['subscription_status'],
                                    'udate'=>date('Y-m-d H:i:s')]);
                        }
                        else{
                            //abonelik tablosuna kayıt ekleniyor
                            DB::table('subscription')->insert(['deviceid'=>$device->id,
                                'expiredate'=>$mockapi['expiredate'],'status'=>$mockapi['subscription_status'],
                                'cdate'=>date('Y-m-d H:i:s')]);
                        }

                        return response()->json(array('msg'=>"ok"));
                    }
                }
            }
        }
    }
    //check Subscription
    /*
     * @token parametresini alır. aboneliğin durum bilgisini döner
     */
    public function checkSubscription(Request $request){
        $token=$request->token;
        if ($token){
            //token set edilmiş ise abonelik bilgileri çekilir
            $subscription=DB::table('device')->select(['subscription.status'])
                ->join('subscription','device.id','=','subscription.deviceid')
                ->where('device.clienttoken',$token)->get()->first();
            if ($subscription){//abonelik bilgisi var ise abonelik durumu return edilir
                return response()->json(array('status'=>$subscription->status));
            }
            return response()->json(array('status'=>false));
        }
        return response()->json(array('status'=>false));
    }

    //mock api
    public function googleMockApi(Request $request){
        $receipt=$request->receipt;
        if ($receipt!=""){
            $lastc2=substr($receipt, -2); // son 2 karakteri alıyoruz
            if (is_numeric($lastc2)){
                if ($lastc2 %6==0){// son iki kararter 6 ya bölünüyor ise err_code olarak rate_limit dönüyoruz
                    return response()->json(array('msg'=>'son iki rakam 6 ya bölünüyor','status'=>false,'err_code'=>'rate_limit'));
                }
            }
            $lastc=substr($receipt, -1); // son karakteri alıyoruz
            if(is_numeric($lastc)){
                if ($lastc %2==0){ //son karakerin tekmi çiftmi olduğuna bakıyoruz
                    return array('msg'=>"Sonkarakter Çift",'status'=>false);
                }
                else{
                    $ddd=date('Y-m-d H:i:s', strtotime('UTC-6'));
                    $subscription_status_arr=['started','renewed','canceled'];
                    return array('msg'=>"OK",'status'=>true,'expiredate'=>$ddd,'subscription_status'=>$subscription_status_arr[rand(0,2)]);
                }
            }
            else{
                return array('msg'=>"Son Karakter Sayı Olmalı",'status'=>false);
            }

        }
        return false;
    }
    public function iosMockApi(Request $request){
        $receipt=$request->receipt;

        if ($receipt!=""){
            $lastc2=substr($receipt, -2); // son 2 karakteri alıyoruz
            if (is_numeric($lastc2)){
                if ($lastc2 %6==0){
                    return response()->json(array('msg'=>'son iki rakam 6 ya bölünüyor','status'=>false,'err_code'=>'rate_limit'));
                }
            }
            $lastc=substr($receipt, -1); // son karakteri alıyoruz
            if(is_numeric($lastc)){
                if ($lastc %2==0){//son karakerin tekmi çiftmi olduğuna bakıyoruz
                    return response()->json(array('msg'=>"Sonkarakter Çift",'status'=>false));
                }
                else{
                    $ddd=date('Y-m-d H:i:s', strtotime('UTC-6'));
                    $subscription_status_arr=['started','renewed','canceled'];
                    return response()->json(array('msg'=>"OK",'status'=>true,'expiredate'=>$ddd,'subscription_status'=>$subscription_status_arr[rand(0,2)]));
                }
            }
            else{
                return response()->json(array('msg'=>"Son Karakter Sayı Olmalı",'status'=>false));
            }

        }
        return response()->json(array('status'=>false));
    }
}
