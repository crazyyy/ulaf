<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class=" imlt-err-card card">
<div class="card-body">
<div class="imlt_title"><h2>HOOKS AND ACTIONS</h2></div>

<?php if ( $data['components'] ):?>
    <?php if ( $data['sourceList'] ):?>
        <div style="margin-bottom: 10px;">
            Source:
            <select class="js-hooks-and-actions-change-source" data-base_url="<?php echo admin_url( 'admin.php?page=imlt_manage&tab=hooks_and_actions' );?>" >
                <option value="0" <?php if ( $data['source'] == 0 ) echo 'selected';?> >All</option>
                <?php foreach ( $data['sourceList'] as $source ):?>
                <option value="<?php echo $source;?>" <?php if ( $data['source'] === $source ) echo 'selected';?> ><?php echo $source;?></option>
                <?php endforeach;?>
            </select>
        </div>
    <?php endif;?>
    <table class="table table-striped table-bordered datatable">
        <tr>
            <td>Hook Name</td>
            <td>Callbacks</td>
        </tr>
        <?php foreach ( $data['components'] as $hookName => $callbacks ):?>
                <tr>
                    <td rowspan="" ><?php echo $hookName;?></td>
                            <td>
            <?php foreach ( $callbacks as $key => $callbackComponent ):?>
                <div>
                            <?php if ($callbackComponent['class']):?>
                                <?php echo $callbackComponent['class'] . '->' . $callbackComponent['function'] . '()';?>
                            <?php else :?>
                                <?php echo $callbackComponent['function'] . '()';?>
                            <?php endif;?>
                            <div>File: <?php echo $callbackComponent['file'];?></div>
                            <div><i>Source</i>: <?php echo $callbackComponent['source'];?></div>
                </div>
            <?php endforeach;?>
            </td>
        </tr>
        <?php endforeach;?>
    </table>
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
