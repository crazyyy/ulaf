<?php
if ( !current_user_can('manage_options') || !defined( 'ABSPATH' ) ) {
    exit;
}
echo $data['header'];?>
<div class="imlt-err-card card">
<div class="card-body">
    <h1>Tabel <?php echo $data['tableName'];?></h1>
    <div class="row">
      <div class="col-sm-7">
    <form class="imlt-inline form-group" action="<?php echo admin_url( 'admin.php?page=imlt_manage&tab=database&table=' . $data['tableName'] . '&query=true' );?>" method="post" >

        <div id="imlt_simple_query_trigger" class="imlt-btn btn btn-info active">Simple Query</div>
        <div id="imlt_custom_query_trigger" class="imlt-btn btn btn-info active">Custom Query</div>
        <div id="imlt_tabel_details_trigger" class="imlt-btn btn btn-info active">Tabel Details</div>
        <div class="imlt-inline"><a class="imlt-btn imlt-btn-refresh-db btn btn-success" href="<?php echo admin_url('admin.php?page=imlt_manage&tab=database&table=' . $data['tableName']); ?>">Reload Table</a></div>

        <!-- Simple query content-->
        <div id="imlt_simple_query_content" class="imlt-hide">
          <fieldset class="form-group">
            <textarea class="imlt-basic-textarea form-control " name="single_query"><?php echo @$data['query'];?></textarea>
        </fieldset>
            <div>
                <input class="imlt-btn btn btn-warning" type="submit" name="submit" value="Submit"/>
            </div>
        </div>
        <!---->

        <!-- Custom query content-->
        <div id="imlt_custom_query_content" class="imlt-hide">
          <div class="alert alert-secondary" role="alert">Search for data</div>
          <fieldset class="form-group">
          <label class="imlt-lbl">Select:</label>
            <div class="imlt-inpt input-group">

              <?php if($data['items']) : ?>
              <select name="imlt-select">
                <option value="*">*</option>
                <?php  foreach($data['items'][0] as $items_select_keys=>$items_select_values) : ?>
              <option value="<?php echo $items_select_keys; ?>"><?php echo $items_select_keys; ?></option>
                <?php  endforeach; ?>
              </select>
            <?php endif; ?>
            </div>
          </fieldset>

          <fieldset class="form-group">
            <label class="imlt-lbl">Where:</label>
              <div class="imlt-inpt input-group">
                <?php if($data['items']) : ?>
                <select name="imlt-where">

                  <?php  foreach($data['items'][0] as $items_keys=>$items_values) : ?>
                <option value="<?php echo $items_keys; ?>"><?php echo $items_keys; ?></option>
                  <?php  endforeach; ?>
                </select>
              <?php endif; ?>
              </div>
          </fieldset>
            <fieldset class="form-group">
              <label class="imlt-lbl">Operator:</label>
                <div class="imlt-inpt input-group">
                  <select name="imlt-operator">
                  <option value="="> = </option>
                  <option value=">"> > </option>
                  <option value=">="> >= </option>
                  <option value="<"> < </option>
                  <option value="<="> <= </option>
                  <option value="!="> != </option>
                  <option value="LIKE"> LIKE </option>
                  <option value="NOT LIKE"> NOT LIKE </option>
                  <option value="IN(...)"> IN (...) </option>
                  <option value="NOT IN (...)"> NOT IN (...) </option>
                  <option value="BETWEEN"> BETWEEN </option>
                  <option value="NOT BETWEEN"> NOT BETWEEN </option>
                  <option value="IS NULL"> IS NULL </option>
                  <option value="IS NOT NULL"> IS NOT NULL </option>
                </select>
                </div>
            </fieldset>
            <fieldset class="form-group">
              <label class="imlt-lbl">Value:</label>
                <div class="imlt-inpt input-group">
                  <input type="text"  name="imlt-value" />
                </div>
            </fieldset>
            <fieldset class="form-group">
              <label class="imlt-lbl">Order by:</label>
                <div class="imlt-inpt input-group">
                  <?php if($data['items']) : ?>
                    <select name="imlt-order-by">
                      <?php  foreach($data['items'][0] as $items_ord_by_keys=>$items_ord_by_val) : ?>
                      <option value='<?php echo $items_ord_by_keys; ?>'><?php echo $items_ord_by_keys;?></option>
                      <?php  endforeach; ?>
                    </select>
                  <?php endif; ?>
                </div>
            </fieldset>
            <fieldset class="form-group">
              <label class="imlt-lbl">Sorting:</label>
                <div class="imlt-inpt input-group">
                  <select name="imlt-sorting">
                    <option value="ASC">ASC</option>
                    <option value="DESC">DESC</option>
                  </select>
                </div>
            </fieldset>
            <div>
                <input class="imlt-btn btn btn-warning" type="submit" name="submit" value="Submit"/>
            </div>
            </div>
          <!---->
    </form>

    <!-- Table details -->
    <div id="imlt_table_details_content">
      <?php if ( $data['tableDetails'] ):?>
    <h4>Table Details</h4>
    <table class="imlt-table-details table table-striped table-bordered datatable">
      <thead>
        <tr class="imlt-table-header">
            <th>Field</th>
            <th>Type</th>
            <th>Key</th>
            <th>Default Value</th>
        </tr>
      </thead>
  <?php endif;?>
      <tbody>
  <?php
  foreach ( $data['tableDetails'] as $itemData ):?>
        <tr class="imlt-table-body">
            <td><?php echo $itemData->Field;?></td>
            <td><?php echo $itemData->Type;?></td>
            <td><?php echo $itemData->Key;?></td>
            <td><?php echo $itemData->Default;?></td>
        </tr>
  <?php endforeach;?>
    </tbody>
  </table>
    </div>
    </div>

      <div class="col-sm-5">
        <?php if ( $data['allTablesList'] ):?>
        <form class="imlt-dtb-form-select" action="" method="post">
        <fieldset class="form-group">
          <label>Select table</label>
          <select id="imlt_table_names" class="form-control select2-single">
              <option>select...</option>
            <?php  foreach($data['allTablesList'] as $keys=>$itemDatas) : ?>
               <option  value="<?php echo admin_url('admin.php?page=imlt_manage&tab=database&table=' .$itemDatas['tableName']); ?>"><?php echo $itemDatas['tableName'] . " -> " .$itemDatas['count'] . "</b>" ; ?></option>

            <?php endforeach; ?>
          </select>
        </fieldset>
      </form>
      <?php endif; ?>
      </div>
  </div>

    <?php if ( $data['items'] ):?>

      <div class="imlt-scrollmenu">
        <table class="table table-striped table-bordered datatable">
          <thead>
          <tr>
              <?php foreach ( $data['items'][0] as $key=>$itemData ):?>
                  <th><?php echo $key;?></th>
              <?php endforeach;?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ( $data['items'] as $itemData ):?>
          <tr>
              <?php foreach ( $itemData as $columnValue ):?>
                  <td><?php echo $columnValue;?></td>
              <?php endforeach;?>
          </tr>
        <?php endforeach;?>
      </tbody>
        </table>
      </div>
        <?php if ( $data['pagination'] ):?>
            <div class="pagination">
                <?php echo $data['pagination'];?>
            </div>
        <?php endif;?>


    <?php elseif(isset($_POST['imlt-value'])) : ?>
        <?php if($_POST['imlt-value'] !== $itemData) :?>
          <div class="alert alert-warning" role="alert">Value does not exist!</div>
        <?php endif; ?>
      <?php else : ?>
        <div class="alert alert-warning" role="alert">This table does not have any data!</div>
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
