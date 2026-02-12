<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class="imlt-err-card card">
<div class="card-body">

    <div class="imlt_title"><h2>SPEED TEST</h2></div>
    <div class="imlt-logged-alert alert alert-primary" role="alert">To see  the website loading time for each visited URL enable the option below.</div>
    <form method="post" action="">

            <label>Enable</label>
            <label class='switch switch-label switch-pill switch-primary'>
                <input class="switch-input" type='checkbox' <?php echo $data['enabled'] ? 'checked' : '';?> onChange="imlt_checkbox_write_value( this, '[name=imtl_test_speed_enabled]', 1, 0 );" >
                <span class='switch-slider' data-checked="On" data-unchecked="Off"></span>
            </label>
            <input type="hidden" name='imtl_test_speed_enabled' value="<?php echo $data['enabled'];?>" />

        <div style="margin: 15px;">
            <input type='submit' value='Save' name='save' class='imlt-btn imlt-speed btn btn-info active' />
            <div class="imlt-btn  imlt-speed btn btn-info active js-speed-test-clear-history">Clear History</div>
        </div>
    </form>
    <div class="imlt-logged-alert alert alert-warning" role="alert">This action consuming resources. Keep it OFF when is not necessary.</div>

  <?php if ( $data['items'] ):?>
      <table class="imlt-default-table table table-striped table-bordered datatable">
        <thead>
          <tr>
              <th>URL</th>
              <th>Loading time</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $data['items'] as $item ):?>
              <tr>
                  <td><?php echo $item->url;?></td>
                  <td><?php echo $item->loading_time;?></td>

              </tr>
          <?php endforeach;?>
        <tbody>
      </table>
      <?php if ( $data['pagination'] ): ?>
          <?php echo $data['pagination'];?>
      <?php endif;?>
    <?php else: ?>
        <div class="imlt-logged-alert alert alert-primary" role="alert">No information available.</div>
    <?php endif;?>


 </div>
</div>
</div>
</main>
</div>
</div>
<div class="imlt-pos"></div>
<footer class="app-footer">
  <div class="imlt-footer">
    <a href="https://wpindeed.com/">WPIndeed</a>
    <span>© <?php echo date("Y");?>. WPIndeed Development</span>
  </div>
  <div class="ml-auto imlt-footer-right">
    <span>Powered by</span>
    <a href="https://wpindeed.com/">WPIndeed</a>
  </div>
</footer>
