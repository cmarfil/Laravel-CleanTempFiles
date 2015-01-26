# Laravel-CleanTempFiles
Delete temporal files with Laravel Command

Create command from console:
```
php artisan command:make cleanTmp
```

This will create the file app/commands/cleanTmp.php

On fire function paste:
```
	public function fire()
	{
		$this->info('Deleting all temp files');

		//Clean before X hours
		$saveHours = 2;

		//Get timestamp - $saveHours
		$hour_limit = new DateTime('now');
		$hour_limit->modify('-'.$saveHours.' hour');
		$timestamp_limit = $hour_limit->getTimestamp();
		$this->info('hour: '.$timestamp_limit);

		//Get all files - recursive
		$files = File::allFiles( Config::get('settings.system.tmp_path') );
		//Foreach all files from temp folder
		foreach ($files as $file){
			try {
				//If modified is < $saveHours
				if(File::lastModified($file) < $timestamp_limit){
					$this->info('4 Delete' . $this->info((string)$file));
					File::delete($file);
				}
			} catch (Exception $e){
				//Log error
				$msg = 'Delete error' . (string)$file;
				$this->error($msg);

				//Notify to developer contact
				Mail::send(array('text' => 'emails.log'), array('msg'=>$msg), function($message){
					$message->to( Config::get('settings.contacts.developer') )->subject( URL::to('/') . ' - Log error - cleanTmp command');
				});
			}
		}
	}
```

On app/start/artisan.php add:
```
Artisan::add(new cleanTmp);
```

Note that two configuration items are used:
```
//Email log
Config::get('settings.contacts.developer')
//Tmp path
Config::get('settings.system.tmp_path')
```

* The deletion of files is recursive.

###Cronjob###
```
sudo crontab -e
```
And add the following line to run the task once daily at 00h
```
00 00 * * * php /var/www/project/artisan command:cleanTmp
```
