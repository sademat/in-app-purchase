    IOS or Google mobile applications will be able to make in-app-purchase purchase / verification 
    and current subscription control using this API.
    On the worker side, the expire-dates of the existing active subscriptions in the database 
    will be queryed again via iOS or Google and their status and expire-dates     will be updated.
    This system can support multiple mobile applications at the same time.
## Database
    The following update should be done in the .env file
    QUEUE_CONNECTION=database
# API
## ApiController Class
### register(method)
    device & application registration
	{
	"uid":"10000",//deviceid
	"appid":"web9878",//applicationid
	"lang":"en",//lang
	"os":"google"//OS
	}
### purchase(method)
    in-app purchase request
	{	"token":"eyJpdiI6InM5SFFmd3pHek00Ui9EcmpqditYelE9PSIsInZhbHVlIjoiejRCaGNqbU1HL3d6MWlLSE15Mk0ydVgxOC83Q2c5Zk5ZRENkUFNOWTVTdz0iLCJtYWMiOiI4NGQ4OWJkY2RmY2VmMTVkMGE5ODY5YTA1NjZiMTk2MjI5NTBhOWY0YWU1YjY3NDVjZDJmNDVkNjg2ZmEwZDY5In0=",
		"receipt":"5465465456645654583"
	}
### checkSubscription(method)
    subscription status check
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
    Queries the ios or google APIs with the queue structure. used supervisord
    {
        [program:tech-worker]
        process_name=%(program_name)s_%(process_num)02d
        command=php /home/vagrant/code/in-app-purchase/artisan queue:work --tries=3 --max-time=3600
        autostart=true
        autorestart=true
        stopasgroup=true
        killasgroup=true
        user=vagrant
        numprocs=8
        redirect_stderr=true
        stdout_logfile=/home/vagrant/code/in-app-purchase/storage/logs/worker.log
        stopwaitsecs=3600
    }

### handle(method):
### command/GoogleWorkerCommand 
    It pulls records with Google operating system from registered devices and applications. It is run with crontab.
### command/IosWorkerCommand 
    It pulls records with operating system iOS from registered devices and applications. It is run with crontab.
## crontab:
    * * * * * php /home/vagrant/code/in-app-purchase/artisan schedule:run >> /dev/null 2>&1
