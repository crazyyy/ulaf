<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
?>

<div style="display:inline-block;width:130px;padding:6px 0 6px 10px">
  <input id="ctcp_blank_filename" placeholder="<?php _e( 'New Blank File' ); ?>" name="ctcp_blank_filename" type="text" value="" class="ctc_text" maxlength="150" />
</div>
<div style="display:inline-block;width:60px;padding:6px 10px 6px 0">
  <select id="ctcp_blank_fileext" name="ctcp_blank_fileext">
    <?php foreach ( apply_filters( 'wp_theme_editor_filetypes', $this->filetypes ) as $ext ): ?>
    <option value="<?php echo $ext; ?>">.<?php echo $ext; ?></option>
    <?php endforeach; ?>
  </select>
</div>
<input id="ctcp_create_file" name="ctcp_create_file" type="submit" value="Create File" class="ctc_submit button button-primary" />
