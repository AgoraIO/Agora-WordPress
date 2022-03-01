<!-- External Inject Url Modal -->
  <div class="modal fade slideInLeft animated" id="add-external-source-modal" tabindex="-1" role="dialog" aria-labelledby="add-external-source-url-label" aria-hidden="true" data-backdrop="false" data-keyboard=true>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="add-external-source-url-label">
            <i class="fas fa-broadcast-tower"></i> [add external url]
          </h5>
          <button id="hide-external-url-modal" type="button" class="close" data-dismiss="modal" data-reset="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="external-inject-config" onsubmit="return false">
            <div class="form-group">
              <label for="input_external_url">External URL</label>
              <input type="url" class="form-control" id="input_external_url" placeholder="Enter the external URL" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <span id="external-url-error" class="error text-danger" style="display: none"><?php _e('Please enter a valid external URL', 'agoraio') ?></span>
          <span id="external-url-too-long" class="error text-danger" style="display: none"><?php _e('The URL is too long. Max length supported is 255', 'agoraio') ?></span>
          <button type="button" id="add-external-stream" class="btn btn-primary">
              <i id="add-rtmp-icon" class="fas fa-plug"></i>  
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- end Modal -->