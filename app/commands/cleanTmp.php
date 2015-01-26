<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class cleanTmp extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:cleanTmp';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clean temp files.';

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
	 * @return mixed
	 */
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

}