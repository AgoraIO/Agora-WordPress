<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="agora">
  Broadcast plugin content goes here...
  <?php echo "<pre>".print_r($channel, true)."</pre>"; ?>
  <script>
    console.log('Channel ID:', <?php echo $channel->id(); ?>);
  </script>
</div>