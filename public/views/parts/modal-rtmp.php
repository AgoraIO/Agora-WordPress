<!-- RTMP Config Modal -->
  <div class="modal fade slideInLeft animated" id="addRtmpConfigModal" tabindex="-1" role="dialog" aria-labelledby="rtmpConfigLabel" aria-hidden="true" data-keyboard=true>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rtmpConfigLabel"><i class="fas fa-sliders-h"></i></h5>
          <button type="button" class="close" data-dismiss="modal" data-reset="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="rtmp-config" action="" method="post" onSubmit="return false;">
            <div class="form-group">
              <label for="input_rtmp_url">RTMP Server URL</label>
              <input type="url" class="form-control" id="input_rtmp_url" placeholder="Enter the RTMP Server URL" value="" required />
            </div>
            <div class="form-group">
              <label for="input_private_key">Stream key</label>
              <input type="text" class="form-control" id="input_private_key" placeholder="Enter stream key" required />
            </div>
            <input type="submit" value="Start RTMP" style="position:fixed; top:-999999px">
          </form>
        </div>
        <div class="modal-footer">
          <span id="rtmp-error-msg" class="error text-danger" style="display: none">Please complete the information!</span>
          <button type="button" id="start-RTMP-broadcast" class="btn btn-primary">
            <i class="fas fa-satellite-dish"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- end Modal -->