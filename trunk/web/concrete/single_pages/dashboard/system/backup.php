<?php 
$ifHelper = Loader::helper('concrete/interface');
?>
<script type="text/javascript">
dNum = 0;
function confirmDelete(strFn) {
   //   var ele = $('#confirmDelete').clone().attr('id','confirmDelete'+dNum);
   //   $('body').append($('#confirmDelete'+dNum)); 
   $('#confirmDelete').clone().attr('id', 'confirmDelete'+dNum).appendTo('body');
   var alink = $('#confirmDelete' + dNum + ' .confirmActionBtn a').attr('href'); 
   alink += strFn; 
    $('#confirmDelete' + dNum + ' .confirmActionBtn a').attr('href',alink); 
   confirmdlg = $.fn.dialog.open({
            title: 'Are you sure?',
            'element': $('#confirmDelete' + dNum), 
            width: 300,
            modal: false,
            height: 50
      });   
      dNum++;
}

rNum = 0;
function confirmRestore(strFn) {
   //   var ele = $('#confirmDelete').clone().attr('id','confirmDelete'+rNum);
   //   $('body').append($('#confirmDelete'+rNum)); 
   $('#confirmRestore').clone().attr('id', 'confirmRestore'+rNum).appendTo('body');
   var alink = $('#confirmRestore' + rNum + ' .confirmActionBtn a').attr('href');
   alink += strFn;
   $('#confirmRestore' + rNum + ' .confirmActionBtn a').attr('href',alink); 
   confirmdlg = $.fn.dialog.open({
            title: 'Are you sure?',
            'element': $('#confirmRestore' + rNum), 
            width: 300,
            modal: false,
            height: 50
      });   
      rNum++;
}
$(document).ready(function () {
   $('#executeBackup').click( function() { 
      if ($('#useEncryption').is(':checked')) {
         window.location.href = $(this).attr('href')+$('#useEncryption').val();
         return false;
      }
   });


   if ($.cookie('useEncryption') == "1" ) {
      $('#useEncryption').attr('checked','checked');
   }

   $('#useEncryption').change(function() {
      if ($('#useEncryption').is(':checked')) {
         $.cookie('useEncryption','1');
      } else {
         $.cookie('useEncryption','0');

      }
   }); 
});

</script>

<!--Dialog -->
<div id="confirmDelete" style="display:none"><?=t('This action <strong>cannot be undone</strong>. Are you sure?')?>

<div class="ccm-buttons">
<?=$ifHelper->button_js(t('Cancel'),"$.fn.dialog.close(0)", 'left');?>
<span class="confirmActionBtn">
<?=$ifHelper->button('Delete Backup',$this->action('delete_backup'),'right');?></span>
</div> 

</div>

<!-- End of Dialog //-->

<!--Dialog -->
<div id="confirmRestore" style="display:none"><?=t('This action <strong>cannot be undone</strong>. Are you sure?')?>

<div class="ccm-buttons">
<?=$ifHelper->button_js(t('Cancel'),"$.fn.dialog.close(0)", 'left');?>
<span class="confirmActionBtn">
<?=$ifHelper->button('Restore Backup',$this->action('restore_backup'),'right');?></span>
</div> 

</div>

<!-- End of Dialog //-->

<script type="text/javascript">
$(document).ready( function() { 
   $('a.dialog-launch').click( function() {
      $.fn.dialog.open({ href: $(this).attr('href'),modal:false });

      return false;
      
   });
});

</script>

<div style="width: 760px">

<h1><span><?=t('Existing Backups')?></span></h1>
<div class="ccm-dashboard-inner">
<?php 
if (count($backups) > 0) {
?>
<br/>
<table class="grid-list" cellspacing="1" cellpadding="0" border="0">
<tr>
   <td class="subheader"><?=t('Date')?></td>
   <td class="subheader"><?=t('File')?></td>
   <td class="subheader">&nbsp;</td>
   <td class="subheader">&nbsp;</td>
   <td class="subheader">&nbsp;</td>
</tr>
   <?php  foreach ($backups as $arr_bkupInf) { ?>
   <tr> 
      <td style="white-space: nowrap"><?= date('F, j Y \a\t g:i a', strtotime($arr_bkupInf['date'])) ?></td>
      <td width="100%"><?= $arr_bkupInf['file'];?></td>
      <td><?=$ifHelper->button_js(t('Download'), 'window.location.href=\'' . $this->action('download', $arr_bkupInf['file']) . '\''); ?></td>
      <td>
      <? print $ifHelper->button_js(t("Restore"),"confirmRestore('" . $arr_bkupInf['file'] . "')"); ?>
      </td>
      <td>
	   <? print $ifHelper->button_js(t("Delete"),"confirmDelete('" . $arr_bkupInf['file'] . "')"); ?>
      </td>
   </tr>
   <? } ?>
</table>
<?php 
} else { ?>
	<p><?=t('You have no backups available.')?></p>
<? } ?>
</div>

<? 
$crypt = Loader::helper('encryption');
?>

<h1><span><?=t('Create New Backup')?></span></h1>
<div class="ccm-dashboard-inner">

<table border="0" cellspacing="0" cellpadding="0">
<tr>
	<td style="padding-right: 20px">
		<?= $ifHelper->button(t("Run Backup"),$this->action('run_backup'),"left",null,array('id' => 'executeBackup'))?>
	</td>
	<td>
	<? if ($crypt->isAvailable()) { ?>
		<input type="checkbox" name="useEncryption" id="useEncryption" value="1" checked />
		<?=t('Use Encryption? (Note: this is <strong>strongly</strongly> recommended.)')?>
	<? } else { ?>
		<input type="checkbox" value="0" disabled />
		<?=t('Use Encryption? (Note: this is <strong>strongly</strongly> recommended.)')?>
	<? } ?>
	
	</td>
</tr>
</table>

	<div class="ccm-note" style="margin-top:16px; font-size:12px; line-height:16px; margin-bottom:8px;">
	<?=t('This will create a database export file and store it on your server. It tries to encrypt the file if possible, 
	but may not always do so. Leaving these files on your server may introduce a security risk for your site, and running a 
	restore is not guaranteed to always work correctly.')?>
	</div>

</div>
</div>