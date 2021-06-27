# API
## ApiController Class
### register(method)
    cihaz&application kaydı yapar
	{
	"uid":"10000",//cihazid
	"appid":"web9878",//applicationid
	"lang":"en",//dil
	"os":"google"//işletim sistemi
	}
### purchase(method)
    uygulama içi satın alma isteği
	{	"token":"eyJpdiI6InM5SFFmd3pHek00Ui9EcmpqditYelE9PSIsInZhbHVlIjoiejRCaGNqbU1HL3d6MWlLSE15Mk0ydVgxOC83Q2c5Zk5ZRENkUFNOWTVTdz0iLCJtYWMiOiI4NGQ4OWJkY2RmY2VmMTVkMGE5ODY5YTA1NjZiMTk2MjI5NTBhOWY0YWU1YjY3NDVjZDJmNDVkNjg2ZmEwZDY5In0=",
		"receipt":"5465465456645654583"
	}
### checkSubscription(method)
    abonelik durumu kontrolü
	{	"token":"eyJpdiI6IjQraVVPcXVWeEVTRWd6Q1BFVDBlWGc9PSIsInZhbHVlIjoibm0rZUEvZngxVkNNSjgwUWRYSEd3Qkx6SUJaeGVnUlA0TFRuNkxuQ2oyZz0iLCJtYWMiOiI4Y2MwZGUyMDE1ZjM0MzA3MzljOTk1ZWYwNTA4OTEyMDBmY2E2YjJmZjFiMDU2NGY3NWZlOWZjMTkwMjVlZDRk0="
	}	
### isoMockApi(method):
    {
        "receipt":"54654654566456545832967"
    }
### googleMockApi(method)
    {
        "receipt":"54654654566456545832967"
    }

# WORKER
## jobs/worker Class
    kuyruk yapısı ile ios veya google apilerine sorgu atar. supervisord kullanıldı
    {
        [program:teknasyon-worker]
        process_name=%(program_name)s_%(process_num)02d
        command=php /home/vagrant/code/teknasyon/artisan queue:work --tries=3 --max-time=3600
        autostart=true
        autorestart=true
        stopasgroup=true
        killasgroup=true
        user=vagrant
        numprocs=8
        redirect_stderr=true
        stdout_logfile=/home/vagrant/code/teknasyon/storage/logs/worker.log
        stopwaitsecs=3600
    }

### handle(method):
### command/GoogleWorkerCommand 
    kayıtlı cihaz ve uygulamalrdan işletim sistemi google olan kayıtları çeker. crontab ile çalıştırılır.
### command/IosWorkerCommand 
    kayıtlı cihaz ve uygulamalrdan işletim sistemi ios olan kayıtları çeker. crontab ile çalıştırılır.
## crontab:
    * * * * * php /home/vagrant/code/teknasyon/artisan schedule:run >> /dev/null 2>&1
