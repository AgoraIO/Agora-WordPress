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
        <div class="col value" data-masked="true">
          <?php
          $value = isset($agora_options['appId']) ? $agora_options['appId'] : false;
          if ($value) {
            for($i=0;$i<strlen($value)-4;$i++) echo "*";
            echo substr($value, strlen($value)-4);
          }
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change App ID</a>
      </p>
    </div>
  </div>

  <!-- ===== CLOUD RECORDING STORAGE ===== -->
  <!-- <div class="card">
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
        // _e('Agora Cloud Recording is an add-on service to record and save voice calls, video calls and interactive broadcasts on your cloud storage. With Agora Cloud Recording, you can record calls or live broadcasts for your users to watch at their convenience', 'agoraio');
        ?></p>
      <div class="flex app-setting" id="cloudStorageURL">
        <div class="col label">
          Cloud Storage URL
        </div>
        <div class="col value">
          <?php
          $value = isset($agora_options['cloudStorageURL']) ? $agora_options['cloudStorageURL'] : '';
          // $value = "https://4045media-cloudrecordings.s3.amazonaws.com";
          echo $value;
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change Cloud Recording Storage</a>
      </p>
    </div>
  </div> -->


  <!-- ===== Token Server ===== -->
  <div class="card">
    <h2 class="title">App Certificate</h2>
    <div class="infobox">
      agora.io
      <br />
      <a href="https://dashboard.agora.io/projects" target="blank">Agora.io Dashboard</a>
    </div>

    <br class="clear" />

    <div class="inside">
      <p>
        <?php _e('Please enable your App Certificate Server on your Agora Dashboard, then copy here your <b>App Certificate</b>.', 'agoraio');
        ?></p>
      <div class="flex app-setting" id="appCertificate">
        <div class="col label">
          App Certificate
        </div>
        <div class="col value">
          <?php
          $value = isset($agora_options['appCertificate']) ? $agora_options['appCertificate'] : '';
          echo $value;
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change App Certificate</a>
      </p>
    </div>
  </div>


  <div class="card">
    <h2 class="title">RESTFul Customer ID</h2>
    <div class="infobox">
      agora.io
      <br />
      <a href="https://docs.agora.io/en/faq/restful_authentication" target="blank">Agora.io RESTFul Docs</a>
    </div>

    <br class="clear" />

    <div class="inside">
      <p>
        <?php
        _e('You need to use your Customer ID to access to the RESTFul API and enable Cloud Recording', 'agoraio');
        ?></p>
      <div class="flex app-setting" id="customerID">
        <div class="col label">
          RESTFul Customer ID
        </div>
        <div class="col value">
          <?php
          $value = isset($agora_options['customerID']) ? $agora_options['customerID'] : '';
          // $value = "https://4045media-cloudrecordings.s3.amazonaws.com";
          echo $value;
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change Customer ID</a>
      </p>
    </div>
  </div>

  <div class="card">
    <h2 class="title">RESTFul Customer Certificate</h2>
    <div class="infobox">
      agora.io
      <br />
      <a href="https://docs.agora.io/en/faq/restful_authentication" target="blank">Agora.io RESTFul Docs</a>
    </div>

    <br class="clear" />

    <div class="inside">
      <p>
        <?php
        _e('You need to use your Customer Certificate to enable Cloud Recording', 'agoraio');
        ?></p>
      <div class="flex app-setting" id="customerCertificate">
        <div class="col label">
          RESTFul Customer Certificate
        </div>
        <div class="col value">
          <?php
          $value = isset($agora_options['customerCertificate']) ? $agora_options['customerCertificate'] : '';
          // $value = "https://4045media-cloudrecordings.s3.amazonaws.com";
          echo $value;
          ?>
        </div>
      </div>
      <p>
        <a href="#" class="button">Change Customer Certificate</a>
      </p>
    </div>
  </div>

</div>
