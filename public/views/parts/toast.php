<div class="toast hide" data-delay="5000">
  <div class="toast-header">
    <strong class="mr-auto" id="agora-toast-title"> </strong>
    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="toast-body" id="agora-toast-body"> </div>
</div>

<!-- Permissions Notification Modal -->
  <div class="modal fade animated" id="permissions-notification-modal" tabindex="-1" role="dialog" aria-labelledby="permissinos-notification-modal-label" aria-hidden="true" data-backdrop="false" data-keyboard=true>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="permissinos-notification-modal-label">
            <?php _e('Permissions Error', 'agoraio'); ?>
          </h5>
          <button id="hide-permissinos-notification-modal" type="button" class="close" data-dismiss="modal" data-reset="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p><?php _e('Please, Enable camera permisssions on your browser to join with video.', 'agoraio'); ?></p>

          <p id="text-permissions-any" class="hidden">
          	<?php _e('Reload this page to be prompted again for permissions') ?>
          </p>

          <p id="text-permissions-URL" class="hidden">
          	<?php _e('Click on the URL bar to enable camera permissions on this website', 'agoraio'); ?>
          </p>
          
          <img id="img-permissions-instructions" alt="permissions instructions" class="img-permissions">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Close', 'agoraio') ?></button>
        </div>
      </div>
    </div>
  </div>
<!-- end Modal -->