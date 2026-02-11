<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class="imtl-general-table imlt-err-card card">
<div class="card-body">
    <div class="imlt_title"><h2>DATABASE</h2></div>
    <?php if ( $data['allTables'] ): ?>

        <table id="table_id" class="table table-striped table-bordered datatable">
          <thead>
            <tr>
                <th>Table Name</th>
                <th>Count of rows</th>
                <th>MySql Engine Type</th>
                <th>Table collation</th>
                <th>Create Date</th>
                <th>Update Date</th>
            </tr>
          </thead>
        <tbody>
        <?php foreach ( $data['allTables'] as $tableData ):?>
          <tr>
              <td><a href="<?php echo admin_url('admin.php?page=imlt_manage&tab=database&table=' . $tableData['tableName']);?>"><?php echo $tableData['tableName'];?></a></td>
              <td><?php echo $tableData['count'];?></td>
              <td><?php echo $tableData['engine'];?></td>
              <td><?php echo $tableData['tableCollation'];?></td>
              <td><?php echo $tableData['createTime'];?></td>
              <td><?php echo $tableData['updateTime'];?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
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
