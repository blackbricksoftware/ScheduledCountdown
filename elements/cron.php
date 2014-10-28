<?php defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.form.formfield');
 
class JFormFieldCron extends JFormField {
	
		public static $JFormFieldCronAssetsLoaded = false;
		protected $type = 'Cron';
	
		function __construct() {
			
			if (!self::$JFormFieldCronAssetsLoaded) {
			
				JHtml::_('bootstrap.framework');
			
				$doc = JFactory::getDocument();
				
				// cron creater
				$doc->addStyleSheet(JURI::root(true).'/modules/mod_scheduledcountdown/assets/shawnchin-jquery-cron-92167c5/cron/jquery-cron.css');
				$doc->addScript(JURI::root(true).'/modules/mod_scheduledcountdown/assets/shawnchin-jquery-cron-92167c5/cron/jquery-cron-min.js');
				$doc->addStyleSheet(JURI::root(true).'/modules/mod_scheduledcountdown/assets/shawnchin-jquery-cron-92167c5/gentleSelect/jquery-gentleSelect.css');
				$doc->addScript(JURI::root(true).'/modules/mod_scheduledcountdown/assets/shawnchin-jquery-cron-92167c5/gentleSelect/jquery-gentleSelect-min.js');
				
				// cron displayer
				$doc->addScript(JURI::root(true).'/modules/mod_scheduledcountdown/assets/moment.min.js');
				$doc->addScript(JURI::root(true).'/modules/mod_scheduledcountdown/assets/later.min.js');
				//~ $doc->addScript(JURI::root(true).'/modules/mod_scheduledcountdown/assets/prettycron.js');
				
				self::$JFormFieldCronAssetsLoaded = true;
			}
			
			parent::__construct();
		}
 
        //~ public function getLabel() {
			//~ 
			//~ // Initialize variables.
			//~ $label = '';
			//~ $replace = '';
//~ 
			//~ // Get the label text from the XML element, defaulting to the element name.
			//~ $text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
//~ 
			//~ // Build the class for the label.
			//~ $class = !empty($this->description) ? 'hasTip' : '';
			//~ $class = $this->required == true ? $class.' required' : $class;
//~ 
			//~ // Add replace checkbox
			//~ $replace = '<input type="checkbox" name="update['.$this->name.']" value="1" />';
//~ 
			//~ // Add the opening label tag and main attributes attributes.
			//~ $label .= '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="'.$class.'"';
//~ 
			//~ // If a description is specified, use it to build a tooltip.
			//~ if (!empty($this->description)) {
					//~ $label .= ' title="'.htmlspecialchars(trim(JText::_($text), ':').'::' .
									//~ JText::_($this->description), ENT_COMPAT, 'UTF-8').'"';
			//~ }
//~ 
			//~ // Add the label text and closing tag.
			//~ $label .= '>'.$replace.JText::_($text).'</label>';
//~ 
			//~ return $label; 
		//~ }
 
        public function getInput() {
			
			$doc = JFactory::getDocument();
			
			ob_start(); ?>
				<script type="text/javascript">

					jQuery(document).ready(function($){
						
						var input = $(<?= json_encode('#'.$this->id) ?>);
						var div = $(<?= json_encode('#'.$this->id.'_div') ?>);
						var duration = $(<?= json_encode('#'.$this->id.'_duration') ?>);
						var button = $(<?= json_encode('#'.$this->id.'_button') ?>);
						var display = $(<?= json_encode('#'.$this->id.'_display') ?>);
						
						div.cron();
						addVal();
						
						button.click(function(){
							
							dur = duration.val().replace(/[^\d\.]+/g,'');
							dur = dur.length>0 ? parseFloat(dur) : 0;
							
							addVal(div.cron('value'),dur);
							
							div.cron('value','* * * * *');
							duration.val('');
						});
						
						function addVal(val,dur) {

							var vals = [], durs = [];
							var rawvals = $.parseJSON(input.val())
							if (typeof rawvals !== 'object' || !rawvals) rawvals = [];
							$.each(rawvals,function(i,v){
								vals.push(v[0]);
								durs.push(v[1]);
							});
							
							if (typeof val !== 'undefined') {
								if ($.inArray(val,vals)<0) {
									vals.push(val);
									durs.push(dur);
								} else {
									alert('Schedule already exists.');
									return;
								}
							}
							
							var newvals = [];
							$(display).html('');
							
							$.each(vals||[],function(i,v){ //~ console.log(v);
							
								newvals.push([v,durs[i]]);
							
								var a = later.parse.cron(v,false).schedules[0]; //~ console.log(a);
								$(display).append(
									$('<li />').data('val',v).append(
										simpleCronFormat(a),
										durs[i]>0?' for '+durs[i]+' minutes ':' ',
										$('<span />').css({cursor:'pointer',}).text('[x]').click(function(){
								
											var li = $(this).closest('li');
											var val = li.data('val');
											console.log(val);
											
											var vals = $.parseJSON(input.val());
											if (typeof vals !== 'object' || !vals) vals = [];
											
											vals = $.grep(vals||[],function(v){ return v[0]!=val; });
											
											input.val(JSON.stringify(vals));
											li.remove();
										})
									)
									.click(function(){ 
										//alert($(this).data('val')); 
									})
								);
							});
							
							input.val(JSON.stringify(newvals));
						}
						
						// simple function to only print simple cron formats with no , or /
						function simpleCronFormat(schedule) {
							
							var hmText = [];
							
							if(schedule['h']&&schedule['m']) { // hour / minute
								hmText.push((schedule['h'][0]==0?'12':schedule['h'][0]%12)+':'+zeroPad(schedule['m'][0])+' '+(schedule['h'][0]>12?'PM':'AM'));
							} else if(schedule['h']) { // hour
								hmText.push(schedule['h'][0]%12+' '+(schedule['h'][0]>12?'PM':'AM'));
							} else if(schedule['m']) { // minute
								hmText.push('Every '+ord(schedule['m'][0])+' minute of every hour');
							} else { // NONE!
								hmText.push('Every minute of every hour');
							}

							if (schedule['D']) { // Day of month
								hmText.push('on the ' + ord(schedule['D'][0]));
							} else {
								if (!schedule['d']) hmText.push('of every day of the month');
							}

							if (schedule['M']) { // Month
							console.log(schedule['M'][0]);
								hmText.push('of ' + moment().month(schedule['M'][0]-1).format('MMMM'));
							} else {
								if (!schedule['d']) hmText.push('of every month');
							}

							if (schedule['d']) { // Day of week
								if (!schedule['D']) hmText.push('on ' + moment().day(schedule['d'][0]-1).format('dddd'));
							} else {
								if (!schedule['D']) hmText.push('of every day of the week');
							}
							
							return hmText.join(' ');
						}
						
						function zeroPad(x) {
							return (x < 10)? '0' + x : x;
						}
						
						function ord(n) {
							var sfx = ["th","st","nd","rd"];
							var val = n%100;
							return n + (sfx[(val-20)%10] || sfx[val] || sfx[0]);
						}
					
					});
				</script>
			<?php $doc->addCustomTag(ob_get_clean());
			
			ob_start(); ?>
				<div id="<?= htmlspecialchars($this->id) ?>_div" style="display: inline-block;"></div>
				<input id="<?= htmlspecialchars($this->id) ?>_duration" type="text" value="" class="input-medium" placeholder="Duration (minutes)" />
				<button type="button" class="btn btn-small" id="<?= htmlspecialchars($this->id) ?>_button" style="margin: 0 0 0 10px;">
					<i class="icon-save-new"></i> Add
				</button>
				<hr class="hr-condensed" />
				<b>Scheduled Times</b>
				<ul id="<?= htmlspecialchars($this->id) ?>_display"></ul>
				<input id="<?= htmlspecialchars($this->id) ?>" name="<?= htmlspecialchars($this->name) ?>" type="hidden" value="<?= htmlspecialchars($this->value) ?>" />
			<?php return ob_get_clean();
        }
}
