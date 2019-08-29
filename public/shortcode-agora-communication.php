<?php 

// Shortcode [agora-communication]
function renderCommnicationShortcode($parent, $instance) {

  if(isset($instance['formclass'])) {
      $instance['formClass'] = $instance['formclass'];
  }
  if(isset($instance['submitclass'])) {
      $instance['submitClass'] = $instance['submitclass'];
  }

  $instance = shortcode_atts(
      array(
          'formClass' => 'row',
          'submitClass' => 'btn btn-primary'
      ), $instance, 'agora-communication' );

  if(!$instance) { $instance = []; }

  ob_start();

  require_once('views/wp-agora-io-communication.php');

  $out = ob_get_clean();
  return $out;
}