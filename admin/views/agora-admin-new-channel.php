<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}


function agoraio_admin_save_button( $channel_id ) {
  static $button = '';

  if ( ! empty( $button ) ) {
    echo $button;
    return;
  }

  $nonce = wp_create_nonce( 'agoraio-save-channel_' . $channel_id );

  $onclick = sprintf(
      "this.form._wpnonce.value = '%s';"
    . " this.form.action.value = 'save';"
    . " return true;",
    $nonce );

  $button = sprintf(
    '<input type="submit" class="button-primary" name="agoraio-save" value="%1$s" onclick="%2$s" />',
    esc_attr( __( 'Save', 'agoraio' ) ),
    $onclick );

  echo $button;
}
?>

<div class="wrap agoraio" id="agoraio-new-channel">
  <h1 class="wp-heading-inline">
    <?php
    if ( $post->initial() ) {
      echo esc_html( __( 'Add New Channel', 'agoraio' ) );
    } else {
      echo esc_html( __( 'Edit Channel', 'agoraio' ) );
    }
  ?></h1>
  <hr class="wp-header-end">

  <?php
  if ( $post ) :

    if ( current_user_can( 'edit_posts', $post_id ) ) {
      $disabled = '';
    } else {
      $disabled = ' disabled="disabled"';
    }
  ?>

  <form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( 'agoraio', false ) ) ); ?>" id="agoraio-admin-form-element"<?php do_action( 'agoraio_post_edit_form_tag' ); ?>>
  <?php
    if ( current_user_can( 'edit_posts', $post_id ) ) {
      wp_nonce_field( 'agoraio-save-channel_' . $post_id );
    }
  ?>

  <input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_id; ?>" />
  <input type="hidden" id="agoraio-locale" name="agoraio-locale" value="<?php echo esc_attr( $post->locale() ); ?>" />
  <input type="hidden" id="hiddenaction" name="action" value="save" />
  <input type="hidden" id="active-tab" name="active-tab" value="<?php echo isset( $_GET['active-tab'] ) ? (int) $_GET['active-tab'] : '0'; ?>" />


  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
      <div id="post-body-content">
        <div id="titlediv">
          <div id="titlewrap">
            <label class="screen-reader-text" id="title-prompt-text" for="title">
              <?php echo __( 'Channel name', 'agoraio' ); ?>
            </label>
            <input
              type="text"
              name="post_title"
              size=30
              value="<?php echo $post->initial() ? '' : $post->title() ?>"
              id="title"
              spellcheck="true"
              autocomplete="off"
              placeholder="<?php echo __( 'Channel name', 'agoraio' ); ?>"
              <?php echo current_user_can( 'edit_posts', $post_id ) ? '' : 'disabled="disabled"' ?>
            />
          </div><!-- #titlewrap -->
          <div class="inside">
          <?php if ( ! $post->initial() ) { ?>
            <p class="description">
            <label for="agora-shortcode"><?php echo esc_html( __( "Copy this shortcode and paste it into your post, page, or text widget content:", 'agoraio' ) ); ?></label>
            <span class="shortcode wp-ui-highlight">
              <input type="text" id="agora-shortcode" 
                onfocus="this.select();"
                readonly="readonly"
                class="large-text code"
                value="<?php echo esc_attr( $post->shortcode() ); ?>" />
            </span>
            </p>
            <?php if ( $old_shortcode = $post->shortcode( array( 'use_old_format' => true ) ) ) { ?>
              <p class="description">
              <label for="agora-shortcode-old">
                <?php echo esc_html( __( "You can also use this old-style shortcode:", 'agoraio' ) ); ?>
              </label>
              <span class="shortcode old">
                <input type="text" id="agora-shortcode-old"
                  onfocus="this.select();"
                  readonly="readonly"
                  class="large-text code"
                  value="<?php echo esc_attr( $old_shortcode ); ?>"
                />
              </span>
              </p>
            <?php } ?>
          <?php } ?>
          </div>
        </div><!-- #titlediv -->
      </div><!-- #post-body-content -->

      <!--  metabox here  -->
      <div id="postbox-container-1" class="postbox-container">
        <?php do_action( 'agoraio_channel_form_settings', $post ); ?>
      </div>

      <div id="postbox-container-2" class="postbox-container">
        <?php do_action( 'agoraio_channel_form_appearance', $post ); ?>

        <p class="submit"><?php agoraio_admin_save_button( $post_id ); ?></p>
      </div>
    </div>
  </div>

  </form>
  <?php endif; ?>

</div><!-- end wrap -->


<?php
function agora_render_setting_row_select($id, $title, $settings, $options) {
  ?>
  <tr>
    <th scope="row"><label for="<?php echo $id ?>"><?php echo $title; ?></label></th>
    <td>
      <select id="<?php echo $id ?>" name="<?php echo $id ?>">
        <?php foreach ($options as $key => $value) {
          echo '<option value="'.$key.'">'.$value.'</option>';
        } ?>
      </select>
    </td>
  </tr>
  <?php
}
function agora_render_setting_row($id, $title, $settings, $inputType="number") {
  ?>
  <tr>
    <th scope="row"><label for="<?php echo $id ?>"><?php echo $title ?></label></th>
    <td>
      <input
        <?php echo $inputType!=='number' ? 'class="regular-text"' : 'min="0"'; ?>
        id="<?php echo $id ?>"
        name="<?php echo $id ?>"
        type="<?php echo $inputType ?>"
        value="<?php echo $settings[$id] ?>">
    </td>
  </tr>
  <?php
}

// Render the content of the metabox with Channel Settings
function render_agoraio_channel_form_settings($channel) {
  // echo "<pre>Settings:".print_r(, true)."</pre>";
  $props = $channel->get_properties();
  $type = $props['type'];
  $userHost = $props['host'];
  $settings = $props['settings'];
  ?>
  <ul class="nav nav-tabs">
    <li class="active">
      <a href="#tab-1">
        <i class="dashicons-before dashicons-admin-plugins"> </i>
        <?php _e('Type and Permissions', 'agoraio') ?>
      </a>
    </li>
    <li><a href="#tab-2">
      <i class="dashicons-before dashicons-share"> </i>
      <?php _e('Push to External Networks', 'agoraio') ?>
    </a></li>
    <li><a href="#tab-3">
      <i class="dashicons-before dashicons-admin-settings"> </i>
      <?php _e('Inject External Streams', 'agoraio') ?>
    </a></li>
  </ul>
  <div class="tab-content">
    <div id="tab-1" class="tab-pane active">
      <table class="form-table">
        <tr>
          <th scope="row"><label for="type"><?php _e('Channel type', 'agoraio'); ?></label></th>
          <td>
            <select name="type" id="type" class="large-dropdown" required>
              <option value=""><?php _e('Select Type', 'agoraio'); ?></option>
              <option value="broadcast"><?php _e('Broadcast', 'agoraio') ?></option>
              <option value="communication"><?php _e('Communication', 'agoraio') ?></option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="host"><?php _e('Broadcaster User', 'agoraio'); ?></label></th>
          <td>
            <?php
            $dropdownParams = array(
              "id" => "host",
              "name" => "host",
              "class" => "large-dropdown",
            );
            wp_dropdown_users($dropdownParams);
            ?>
          </td>
        </tr>
      </table>
    </div>

    <div id="tab-2" class="tab-pane">
      External URLs goes here...
    </div>

    <div id="tab-3" class="tab-pane">
      <?php // echo "<pre>".print_r($settings, true)."</pre>"; ?>
      <table class="form-table">
        <?php
        agora_render_setting_row('width', __('Width', 'agoraio'), $settings);
        agora_render_setting_row('height', __('Height', 'agoraio'), $settings);
        agora_render_setting_row('videoBitrate', __('Video Bitrate', 'agoraio'), $settings);
        agora_render_setting_row('videoFramerate', __('Video Framerate', 'agoraio'), $settings);
        agora_render_setting_row('videoGop', __('Video GOP', 'agoraio'), $settings);
        agora_render_setting_row_select(
          'lowLatency',
          __('Low Latency', 'agoraio'),
          $settings,
          array(
            'false' => __('High latency with assured quality (default)', 'agoraio'),
            'true' => __('Low latency with unassured quality', 'agoraio')
          )
        );
        agora_render_setting_row_select(
          'audioSampleRate',
          __('Audio Sample Rate', 'agoraio'),
          $settings,
          array(
            44100 => __('44.1 kHz (default)', 'agoraio'),
            48000 => __('48 kHz', 'agoraio'),
            32000 => __('32 kHz', 'agoraio'),
          )
        );
        agora_render_setting_row('audioBitrate', __('Audio Bitrate', 'agoraio'), $settings);
        agora_render_setting_row_select(
          'audioChannels',
          __('Audio Channels', 'agoraio'),
          $settings,
          array(1 => __('Mono (default)', 'agoraio'), 2 => __('Dual sound channels', 'agoraio'))
        );
        agora_render_setting_row_select(
          'videoCodecProfile',
          __('Video Codec Profile', 'agoraio'),
          $settings,
          array(
            100 => __('High (default)', 'agoraio'),
            77 => __('Main', 'agoraio'),
            66 => __('Baseline', 'agoraio'),
          )
        );
        ?>
        <tr>
          <th scope="row"><label for="backgroundColor"><?php _e('Background Color', 'agoraio') ?></label></th>
          <td>
            <input
              id="backgroundColor"
              name="backgroundColor"
              type="text"
              class="agora-color-picker"
              value="<?php echo $settings['backgroundColor'] ?>"
              data-default-color="#ffffff">
          </td>
        </tr>
      </table>
    </div>
  </div>
  <?php
}

// Metabox content for Channel Appearance
function render_agoraio_channel_form_appearance($channel) {
  $props = $channel->get_properties();
  $appearance = $props['appearance'];
  ?>
  <table class="form-table">
    <?php
    agora_render_setting_row('splashImageURL', __('Splash Image URL', 'agoraio'), $appearance, 'url');
    agora_render_setting_row('noHostImageURL', __('No-host Image URL', 'agoraio'), $appearance, 'url');
    ?>
    <tr>
      <th scope="row"><label for="activeButtonColor"><?php _e('Active Button Color', 'agoraio') ?></label></th>
      <td>
        <input
          id="activeButtonColor"
          name="activeButtonColor"
          type="text"
          class="agora-color-picker"
          value="<?php echo $appearance['activeButtonColor'] ?>"
          data-default-color="#1E73BE">
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="disabledButtonColor"><?php _e('Disabled Button Color', 'agoraio') ?></label></th>
      <td>
        <input
          id="disabledButtonColor"
          name="disabledButtonColor"
          type="text"
          class="agora-color-picker"
          value="<?php echo $appearance['disabledButtonColor'] ?>"
          data-default-color="#ffffff">
      </td>
    </tr>
  </table>
  <?php
}
?>