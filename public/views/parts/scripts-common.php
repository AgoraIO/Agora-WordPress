
<script type="text/javascript">
  window.agora_base_url = '<?php echo str_replace("/views/", "/", plugin_dir_url( dirname( __FILE__ ))) ?>';
	// video profile settings
  window.cameraVideoProfile = '<?php echo $instance['videoprofile'] ?>'; // 640x480 @ 30fps & 750kbs
  window.screenVideoProfile = '<?php echo $instance['screenprofile'] ?>';
  window.agoraAppId = '<?php echo $agora->settings['appId'] ?>'; // set app id
  window.channelName = '<?php echo $channel->title() ?>'; // set channel name
  window.channelId = '<?php echo $channel->id() ?>'; // set channel name
  window.userID = parseInt(`${<?php echo $current_user->ID; ?>}`, 10);
  <?php if ($current_user->ID > 0) : ?>
  window.wp_username = '<?php echo $current_user->data->display_name; ?>';
  <?php endif; ?>



	// use tokens for added security
    window.AGORA_TOKEN_UTILS = {
      agoraGenerateToken: function() {
        return <?php
        $appID = $agora->settings['appId'];
        $appCertificate = $agora->settings['appCertificate'];
        $current_user = wp_get_current_user();

        if($appCertificate && strlen($appCertificate)>0) {
          $channelName = $channel->title();
          $uid = $current_user->ID; // Get urrent user id

          // role should be based on the current user host...
          $settings = $channel->get_properties();
          $role = 'Role_Subscriber';
          $privilegeExpireTs = 0;
          if(!class_exists('RtcTokenBuilder')) {
            require_once(__DIR__.'/../../../includes/token-server/RtcTokenBuilder.php');
          }
          echo '"'.AgoraRtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs). '"';
        } else {
          echo 'null';
        }
        ?>;
      }
    };
</script>