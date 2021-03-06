<div class="blocks-manager">
  <div class="modal-header">
    <h4 class="modal-title"><?php echo $text_add_block; ?></h4>
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo $text_close; ?></span></button>
  </div>
  <div class="modal-body">
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item active"><a href="#blocks" class="nav-link active" role="tab" data-toggle="tab"><?php echo $text_available_block; ?></a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="blocks">
        <div class="row fluid">
          <?php foreach ($blocks as $block) { ?>
          <div class="col-md-6">
            <a class="block-item" data-id="<?php echo $block['id']; ?>" data-add-url="<?php echo $addBlock; ?>">
              <i class="fa fa-square-o pull-left"></i>
              <?php if ($block['custom_block_id'] > 0) { ?>
              <span class="title"><?php echo $block['block_name']; ?></span>
              <span class="info">(<?php echo $block['block_txt_id']; ?>)</span>
              <?php } else { ?>
              <span class="title"><?php echo $block['block_txt_id']; ?></span>
              <span class="info"></span>
              <?php } ?>
            </a>
          </div>
          <?php } ?>
        </div>
      </div>
      <!-- <div class="tab-pane" id="create-block">...</div> -->
    </div>
  </div>
</div>
