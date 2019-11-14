<div class="wrap agoraio" id="agoraio-list-table">
  <h1><?php _e('Agora.io - Channels', 'agoraio'); ?></h1>

  <div id="poststuff">
    <div id="post-body">
      <div id="post-body-content">
          <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <?php $this->channels_obj->search_box( __( 'Search Channels', 'agoraio' ), 'agoraio-channel' ); ?>
            <?php $this->channels_obj->display(); ?>
          </form>
      </div>
    </div>
  </div>
</div>
