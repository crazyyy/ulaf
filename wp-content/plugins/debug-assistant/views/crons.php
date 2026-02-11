<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>

<div class=" imlt-err-card card">
<div class="card-body">
<div class="imlt_title"><h2>CRONS</h2></div>
  <?php if ( $data['cronJobs'] ): ?>
    <table class='table table-striped table-bordered datatable'>
      <thead>
        <tr>
            <td>Hook Name</td>
            <td>Callback</td>
            <td>Next Run</td>
            <td>Run interval</td>
            <td>Actions</td>
        </tr>
      </thead>
      <tbody>

        <?php foreach ( $data['cronJobs'] as $cronData ):?>
          <?php $nextRun = (string)((int)$cronData['lastRun'] + (int)$cronData['intervalInSeconds']);?>
          <tr class="<?php echo 'js-' . $cronData['slug'];?>">
              <td><?php echo $cronData['slug'];?></td>
              <td>
                  <?php $functions = getFiltersFor( $cronData['slug'] );?>
                  <?php if ( $functions ): ?>
                      <?php foreach ( $functions as $functionName ):?>
                          <?php if ( is_string($functionName) ) :?>
                              <div><?php echo $functionName;?></div>
                          <?php else :?>
                            Class: <?php echo $functionName[0];?><br/>
                            Method: <?php echo $functionName[1];?>
                          <?php endif;?>
                      <?php endforeach;?>
                  <?php endif;?>
              </td>
              <td><?php echo indeed_print_date_like_wp($nextRun);?></td>
              <td><?php echo $cronData['interval'];?></td>
              <td>
                  <span class="js-do-run-cron imlt-cursor imlt-green" data-cron_name="<?php echo $cronData['slug'];?>" ><i class='imlt-cron icon-control-play icons font-2xl'></i></span>
                  | <span class="imlt-delete-span js-do-delete-cron" data-cron_name="<?php echo $cronData['slug'];?>" ><i class='imlt-cron icon-trash icons font-2xl'></i></span>
              </td>
          </tr>
        <?php endforeach;?>
        </tbody>
    </table>
  <?php else :?>
      <h3>No cron jobs!</h3>
  <?php endif;?>
</div>
</div>

</div>
</main>
</div>
</div>
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
