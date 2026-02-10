<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class="imlt-admin-content">
<h1>Queries</h1>
<?php
global $wpdb;
?>
<?php if( SAVEQUERIES == true && current_user_can( 'administrator' ) ):?>
    <table class='imlt-queries-table'>
        <tr>
            <th id='id-query'>Query</th>
            <th>Caller</th>
            <th>Component</th>
            <th>Time</th>
        </tr>

    <?php foreach ( $wpdb->queries as $queryData):?>
        <tr>
            <td><?php echo $queryData[0];?></td>
            <td>
              <?php foreach ($queryData['indeed_backtrace'] as $key => $value):?>
                  <div>File: <?php echo $value['file'] . ' , line: ' . $value['line'];?></div>
              <?php endforeach;?>
            </td>
            <td>
              <?php foreach ($queryData['indeed_backtrace'] as $key => $value):?>
                  <div>Function: <?php echo $value['function'];?></div>
              <?php endforeach;?>
            </td>
            <td><?php echo $queryData[1] , 's';?></td>
        </tr>
    <?php endforeach;?>
    </table>

<?php else :?>
    <h3>In order to see queries please enable 'SAVEQUERIES' option!</h3>
<?php endif;?>
</div>
<?php
