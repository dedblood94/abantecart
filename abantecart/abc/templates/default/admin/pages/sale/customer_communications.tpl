<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $summary_form; ?>

<ul class="nav nav-tabs nav-justified nav-profile">
	<?php
	foreach ($tabs as $tab) {
		if ($tab['active']) {
			$classname = 'active';
		} else {
			$classname = '';
		}
		?>
	<li class="nav-item">
		<a class="nav-link <?php echo $classname; ?>" <?php echo($tab['href'] ? 'href="' . $tab['href'] . '" ' : ''); ?>><strong><?php echo $tab['text']; ?></strong></a>
	</li>
	<?php } ?>

	<?php echo $this->getHookVar('extension_tabs'); ?>
</ul>


<div id="content" class="panel panel-default">

	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
			<div class="btn-group mr10 toolbar">
				<?php
				if (!empty($search_form)) {
					?>
				<form id="<?php echo $search_form['form_open']->name; ?>"
				      method="<?php echo $search_form['form_open']->method; ?>"
				      name="<?php echo $search_form['form_open']->name; ?>" class="form-inline" role="form">
					<?php
						foreach ($search_form['fields'] as $f) { ?>
					<div class="form-group">
						<div class="input-group input-group-sm">
							<?php echo $f; ?>
						</div>
					</div>
					<?php }	?>
					<div class="form-group">
						<button type="submit" class="btn btn-xs btn-primary tooltips" title="<?php echo $button_filter; ?>">
							<?php echo $search_form['submit']->text ?>
						</button>
						<button type="reset" class="btn btn-xs btn-default tooltips" title="<?php echo $button_reset; ?>">
							<i class="fa fa-sync"></i>
						</button>

					</div>
				</form>
				<?php
				}
				?>
			</div>
		</div>

		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<?php echo $listing_grid; ?>
	</div>

</div>

<?php echo $this->html->buildElement(
['type' => 'modal',
'id' => 'communication_info_modal',
'modal_type' => 'lg',
'data_source' => 'ajax'
]);
?>

<script type="text/javascript">


	var grid_ready = (function(){
		$('.grid_action_view[data-toggle!="modal"]').each(function(){
			$(this).attr('data-toggle','modal'). attr('data-target','#communication_info_modal');
		});
	});

	$(document).on("hidden.bs.modal", function (e) {
		$('#message_grid').trigger("reloadGrid");
		notifier_updater();
		$('#message_grid').focus();
	});

</script>