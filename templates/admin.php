<h1>Bulk Photos Metadata Editor</h1>
<?php settings_errors(); ?>
<form action="options.php" method="post">
    <?php settings_fields( 'bpme-settings-group' ); ?>
    <?php do_settings_sections( 'bulk_photos_meta_editor' ); ?>
    
    <?php submit_button( ); ?>
</form>