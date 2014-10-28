<?php defined( '_JEXEC' ) or die( 'Restricted access' );

$timeLeft = $this->nextInfo-time();

JHtml::_('bootstrap.framework');

$doc = JFactory::getDocument();
$doc->addScript(JURI::root(true).'/media/mod_scheduledcountdown/assets/jquery.countdown.package-1.6.2/jquery.countdown.min.js');
//~ $doc->addStyleSheet(JURI::root(true).'/modules/mod_scheduledcountdown/assets/jquery.countdown.package-1.6.2/jquery.countdown.css');
//~ $doc->addStyleDeclaration('
	//~ .scheduledCountdown span.module-title { margin-right: 2px; font-family: "OpenSansLight";}
	//~ .scheduledCountdown .date { font-size: 12px; }
//~ ');
$complete = $this->params->get('complete');
if ($this->inSession) {
	echo $complete;
} else {
	ob_start(); ?>
		<script type="text/javascript">
			jQuery(function($){
				$('#scheduledCountdown<?= $this->module->id ?> .countdown').countdown({
					until: <?= $timeLeft ?>,
					<?php if (strlen($complete)>0) { ?>
						onExpiry: function(){
							$('#scheduledCountdown<?= $this->module->id ?>').html(<?= json_encode($complete) ?>);
						},
					<?php } ?>
					layout: '<span class="module-title">{dn}</span>d&nbsp;&nbsp;<span class="module-title">{hn}</span>h&nbsp;&nbsp;<span class="module-title">{mn}</span>m&nbsp;&nbsp;<span class="module-title">{sn}</span>s',
				});
			});
		</script>
	<?php $doc->addCustomTag(ob_get_clean()); ?>
	<div id="scheduledCountdown<?= $this->module->id ?>" class="scheduledCountdown">
		<div class="countdown"></div> 
		<div class="date"><?= JHTML::_('date',$this->nextInfo,'D M j, g:i a') ?></div>
	</div>
<?php } ?>
