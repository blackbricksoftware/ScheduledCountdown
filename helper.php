<?php defined( '_JEXEC' ) or die( 'Restricted access' );

class ModScheduledCountdownHelper {

	public $params;
	public $module;
	public $nextInfo;
	public $inSession = false;
	
	public static $cronloaded = false;

	function __construct(&$params,&$module) {

		$this->params =& $params;
		$this->module =& $module;
	}
	
	public function getNextInfo() {
		
		if (!empty($this->nextInfo)) return $this->nextInfo;

		self::loadCron();
		$time = time();
		
		$schedules = (array)json_decode($this->params->get('schedule'));
		if (count($schedules)<=0) return false;
		
		$this->nextInfo = false;
		foreach ($schedules as $schedule) {
			
			list($cron,$duration) = (array)$schedule;
			$cronexp = Cron\CronExpression::factory($cron);
			
			$nexttime = $cronexp->getNextRunDate()->format('U');
			$this->nextInfo = $this->nextInfo===false || $nexttime<$this->nextInfo ? $nexttime : $this->nextInfo;
			
			$duration = (int)$duration;
			if ($duration>0) {
				$prevtime = $cronexp->getPreviousRunDate()->format('U');
				if ( $prevtime<$time && $prevtime+$duration*60>=$time ) $this->inSession = true;
				//~ echo $cron,' ',$duration,' ',$prevtime,' ',$time,' ',$prevtime+$duration*60,'<br />';
			}
		}
		
		return $this->nextInfo;		
	}

	public function display() {
		
		$this->getNextInfo();
		if ($this->nextInfo===false) return;
		
		//~ echo $this->nextInfo,' ',time().'<br />';
		//~ echo '<pre>',print_r($this->params->get('schedule'),true).'</pre><br />';
		
		require JModuleHelper::getLayoutPath('mod_scheduledcountdown',$this->params->get('layout', 'default'));
	}
	
	// this is a retarded function -- but w/e bitches, joomla doesnt support namespaces yet
	static public function loadCron() {
		
		if (self::$cronloaded) return;
		
		JLoader::import('joomla.filesystem.folder');
		$path = __DIR__ .'/helpers/cron-expression-master/src/Cron';
		require_once $path.'/FieldInterface.php';
		require_once $path.'/AbstractField.php';
		$files = JFolder::files($path);
		if (count($files)<=0) return;
		foreach ($files as $file) {
			require_once $path.'/'.$file;
		}
		self::$cronloaded = true;
	}
}
