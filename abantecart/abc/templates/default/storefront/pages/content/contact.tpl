<h1 class="heading1">
  <span class="maintext"><i class="fa fa-envelope"></i> <?php echo $heading_title; ?></span>
  <span class="subtext"></span>
</h1>

<?php if ($success) { ?>
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<?php echo $success; ?>
</div>
<?php } ?>

<?php if ($error_warning) { ?>
<div class="alert alert-error alert-danger">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<?php echo $error_warning; ?>
</div>
<?php } ?>

<div class="contentpanel">
	<h4 class="heading4"><?php echo $text_edit_address; ?></h4>

	<div class="content-fluid">
	  <div class="row m-0">
	    <div class="col-md-6 pull-left">
	    <b><?php echo $text_address; ?></b><br />
	      <?php echo $store; ?><br />
	      <address><?php echo $address; ?></address>
	    </div>
	    <div class="col-md-6 pull-right">
	      <?php if ($telephone) { ?>
	      <b><?php echo $text_telephone; ?></b><br />
	      <?php echo $telephone; ?><br />
	      <br />
	      <?php } ?>
	      <?php if ($fax) { ?>
	      <b><?php echo $text_fax; ?></b><br />
	      <?php echo $fax; ?>
	      <?php } ?>
	    </div>
	  </div>

	  <div class="mt20">
	  <?php echo $form_output; ?>
	  </div>
	</div>

</div>