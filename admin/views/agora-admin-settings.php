<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap agoraio" id="agoraio">
  <h1><?php echo esc_html( __( 'Agora Video Settings', 'agoraio' ) ); ?></h1>

  <?php do_action( 'agoraio_admin_notices', 'agoraio-settings', agora_current_action() ); ?>

  <div class="card">
    <h2 class="title">App ID</h2>
    <div class="infobox">
      agora.io
      <br />
      <a href="https://docs.agora.io" target="blank">Agora.io Docs</a>
    </div>

    <br class="clear" />

    <div class="inside">
      <p>
        <?php
        $desc = __('App ID are issued to app developers by Agora to identify the projects and organizations. After signing up at [AGORA_DASH_LINK]Agora Dashboard[/AGORA_DASH_LINK] you can create multiple projects, and each project will have a unique App ID. See [HELP_LINK]Getting an App ID[/HELP_LINK]', 'agoraio');
        echo str_replace(
          array("[AGORA_DASH_LINK]", "[/AGORA_DASH_LINK]", "[HELP_LINK]", "[/HELP_LINK]"),
          array(
            "<a href='https://dashboard.agora.io/' target='blank'>", "</a>",
            "<a href='https://docs.agora.io/en/Agora%20Platform/channel_key?platform=All%20Platforms#step-1-get-an-app-id' target='blank'>", "</a>",),
          $desc);
        ?>
      </p>
      <div class="flex app-setting" id="appId">
        <div class="col label">
          App ID
        </div>
        <div class="col value">
          <?php
          $value = "skfJHBfg38754hrPnf973q4hjf97834hOiUH9";
          for($i=0;$i<strlen($value)-4;$i++) echo "*";
          echo substr($value, strlen($value)-4);
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change App ID</a>
      </p>
    </div>
  </div>

  <!-- ===== CLOUD RECORDING STORAGE ===== -->
  <div class="card">
    <h2 class="title">Cloud Recording Storage</h2>
    <div class="infobox">
      agora.io
      <br />
      <a href="https://docs.agora.io" target="blank">Agora.io Docs</a>
    </div>

    <br class="clear" />

    <div class="inside">
      <p>
        <?php
        _e('Agora Cloud Recording is an add-on service to record and save voice calls, video calls and interactive broadcasts on your cloud storage. With Agora Cloud Recording, you can record calls or live broadcasts for your users to watch at their convenience', 'agoraio');
        ?></p>
      <div class="flex app-setting" id="cloudStorageURL">
        <div class="col label">
          Cloud Storage URL
        </div>
        <div class="col value">
          <?php
          $value = "https://4045media-cloudrecordings.s3.amazonaws.com";
          echo $value;
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change Cloud Recording Storage</a>
      </p>
    </div>
  </div>


  <!-- ===== Token Server ===== -->
  <div class="card">
    <h2 class="title">Token Server</h2>
    <div class="infobox">
      agora.io
      <br />
      <a href="https://docs.agora.io" target="blank">Agora.io Docs</a>
    </div>

    <br class="clear" />

    <div class="inside">
      <p>
        <?php
        _e('Your token needs to be generated on your own server, hence you are required to first deploy  a token generator on the server. In our [AGORA_GITHUB]Github Repository[/AGORA_GITHUB] we provide source codes and token generator demos for several programming languages.', 'agoraio');
        ?></p>
      <div class="flex app-setting" id="tokenServerURL">
        <div class="col label">
          Tokern Server URL
        </div>
        <div class="col value">
          <?php
          $value = "https://tokenserver.4045media.com";
          echo $value;
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change Token Server</a>
      </p>
    </div>
  </div>
</div>
