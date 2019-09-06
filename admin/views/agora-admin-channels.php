<div class="wrap agoraio" id="agoraio">
  <h1>Agora Video - Channels</h1>

  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
          <form method="post">
            <?php
              $this->channels_obj->prepare_items();
              $this->channels_obj->display();
            ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
