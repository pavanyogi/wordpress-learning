<?php
if (!defined('ABSPATH'))
    exit;

$siteurl = site_url();
$siteurl = esc_url($siteurl);
$config = get_option("zcf_crmformswpbuilder_settings");
if ($config == "") {
    $config_data = 'no';
} else {
    $config_data = 'yes';
}
require_once( ZCF_BASE_DIR_URI . "includes/crmoauthentication.php");
zcfcheckAccessToken();

if (isset($_REQUEST['code'])) {
    $code = $_REQUEST['code'];
    $reposnseAuth = zcfgetAuthTokennew($code);
}

?>
<input type="hidden" name="currentpageUrl" id="currentpageUrl" value=""/>

<div class="clearfix"></div>      
<div class="">
    <div class="panel" style="width:99%;">
        <div class="panel-body">
            <input type="hidden" id="get_config" value="<?php echo $config_data ?>" >
            <input type="hidden" id="revert_old_crm" value="crmformswpbuilder">

            <input type="hidden" id="site_url" name="site_url" value="<?php echo esc_attr($siteurl); ?>">

                <input type="hidden" name="crmforms-zoho-settings-form" value="crmforms-zoho-settings-form" />
                <input type="hidden" id="plug_URL" value="<?php echo esc_url(ZCF_PLUGIN_BASE_URL); ?>" />

                <div class="clearfix"></div>

                <div class="clearfix"></div>
                <div class="mt20">   
                    <div class="form-group col-md-12">
                        <?php
                        global $wp;
                        $current_url = home_url(add_query_arg(array(), $wp->request));
                        $current_url = $current_url . '/wp-admin/admin.php?page=crmforms-builder';
                        require_once( ZCF_BASE_DIR_URI . "includes/crmoauthentication.php");
                        zcfcheckAccessToken();
                        $SettingsConfig = get_option("zcf_crmformswpbuilder_settings");
                        $authtokens = $SettingsConfig['authtoken'];

                        if ($authtokens == '') {
                            
                                    if (isset($_POST['zcrm_integ_submit']) && !empty($_POST)) {
                                      $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
                                      if (!wp_verify_nonce($nonce, 'zcrm_integ_settings_nonce')) {
                                            echo '<div class="error"><p><strong>'.esc_html__('Reload the page again').'</strong></p></div>'."\n";
                                        } 
                                        else {
                                        $zcrm_integ_client_id         = sanitize_text_field($_POST['zcrm_integ_client_id']);
                                        $zcrm_integ_client_secret     = sanitize_text_field($_POST['zcrm_integ_client_secret']);
                                        $zcrm_integ_domain_name       = sanitize_text_field($_POST['zcrm_integ_domain_name']);
                                        $zcrm_integ_authorization_uri = sanitize_text_field($_POST['zcrm_integ_authorization_uri']);
                                        update_option('zcrm_integ_client_id',$zcrm_integ_client_id);
                                        update_option('zcrm_integ_client_secret',$zcrm_integ_client_secret);
                                        update_option('zcrm_integ_domain_name',$zcrm_integ_domain_name);
                                        update_option('zcrm_integ_authorization_uri',$zcrm_integ_authorization_uri);
                                        echo '<div class="updated"><p><strong>'.esc_html__('Settings saved.').'</strong></p></div>'."\n";
                                         ?>
                                        
                                         <?php

                                    }
                                  }
                               

                            ?>
                            <span class="f14 mb20 dB"><b>Zoho CRM Form Builder</b></span>
                            <span class="f14 mb20 dB">The form builder allows you to create forms in your wordpress and push the data into your Zoho CRM. Also, you can map the third party forms with Zoho CRM</span>
                            <span class="f14 mb20 dB">You must authenticate Zoho CRM before you start building.</span>


                            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                             <?php wp_nonce_field('zcrm_integ_settings_nonce'); ?>
                            <div class="page">
                        <div  >
            
            <div class="form">
                <div class="form__row">
                    <label class="form--label">Domain</label>
                           
                    <select onchange='authToken()' id='zcrm_integ_domain_name' class="form--input form--input--select" name="zcrm_integ_domain_name" onchange='selectaccount(this)'>
                           
                        <option value="com" <?php if($domainname == 'com'){ echo "selected";} ?> >zoho.com</option>
                        <option value="eu" <?php if($domainname == 'eu'){ echo "selected";} ?>>zoho.eu</option>
                    </select> 

                    <i class="form__row-info">The name of the region the account is configured</i> </div>
    
                        <div class="form__row">
                                <label class="form--label">Client Id</label>
                                <input type="text" value="<?php echo get_option('zcrm_integ_client_id') ?>" name="zcrm_integ_client_id" class="form--input" id="zcrm_integ_client_id" required onchange='authToken()'/> 
                               
                                    <span id='zohocomaccount'><a  href="https://accounts.zoho.com/developerconsole">How to create client id and Screct key</a> </span> 
                           
                                   <span  id='zohoeuaccount' class='dN'> <a href="https://accounts.zoho.eu/developerconsole">How to create client id and Screct key</a> </span> </div>
                           
                            <div class="form__row">
                                <label class="form--label">Client Secret</label>
                                <input type="text" value="<?php echo get_option('zcrm_integ_client_secret') ?>" name="zcrm_integ_client_secret" class="form--input" id="zcrm_integ_client_secret"  required/ onchange='authToken()'> <i class="form__row-info">Created in the developer console</i> </div>
                            <div class="form__row">
                                <input type="hidden"  readonly="readonly" id='stateurl' name="state" class="form--input" value="<?php echo esc_url(admin_url().'admin.php?page=crmforms-builder'); ?>" class="regular-text" readonly="readonly" required onchange='authToken()'/> 

                                <label class="form--label">Authorization Redirect URI</label>
                                <input type="text" id="zcrm_integ_authorization_uri" readonly="readonly" name="zcrm_integ_authorization_uri" class="form--input zcrm_integ_authorization_uri_us" value="https://extensions.zoho.com/plugin/wordpress/callback" class="regular-text" readonly="readonly" required/>

                            
                            <div class="form__row form__row-btn">
                              <a disabled href='javscript:void(0)' id='zcrm_integ_submit' class='primarybtn'>Authenticate Zoho CRM</a>
                              </div>
                              </div>
                            </div>
                           </div>
                         </form>



                           

                           <?php } ?>

                    </div>
                </div>

            <div id="loading-sync" style="display: none; background:url(<?php echo esc_url(WP_PLUGIN_URL); ?>/zoho-crm-forms/assets/images/ajax-loaders.gif) no-repeat cente"><?php echo esc_html__('', 'zoho-crm-form-builder'); ?></div>
            <div id="loading-image" style="display: none; background:url(<?php echo esc_url(WP_PLUGIN_URL); ?>/zoho-crm-forms/assets/images/ajax-loaders.gif) no-repeat center"><?php echo esc_html__("", "zoho-crm-form-builder"); ?></div>
        </div>
    </div>
</div>    
<?php
require_once( ZCF_BASE_DIR_URI . "includes/crmoauthentication.php");
zcfcheckAccessToken();
$SettingsConfig = get_option("zcf_crmformswpbuilder_settings");
$authtokens = $SettingsConfig['authtoken'];
if ($authtokens != '' && isset($_REQUEST['code'])) {
    ?>
    <script>
        jQuery(window).load(function () {
            saveConfig('save', "<php echo $current_url?>");
            window.opener.location.reload();
            self.close();
        });


    </script>
<?php } ?>
<div class="freezelayer"></div>