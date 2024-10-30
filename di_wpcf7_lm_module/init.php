<?php
/*
Plugin Name: lm CF7 lead manager Addon
Plugin URI: http://wp.dugi.co.il
Description:  Contact Form 7 lead manager Addon
Version: 0.1.4
Author: Dagan Yaakov
Author URI: http://wp.dugi.co.il
*/

define( 'LM_API_TARGET_URL_V1', 'http://api.leadmanager.co.il/v1/submit' );

add_action( 'wpcf7_init', 'di_wpcf7_add_shortcode_lm' );
function di_wpcf7_add_shortcode_lm() {
	wpcf7_add_shortcode( array( 'lm', 'lm_pixel','lm_iframe' ),
		'di_wpcf7_lm_shortcode_handler', true );
}

function di_wpcf7_lm_shortcode_handler( $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';
	
	if (!isset($tag->options))
		return '';
	
	
	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' wpcf7-not-valid';

	$html = '';
	
	$html .= sprintf( '<input type="hidden" name="lm_post_method" value="%1$s" />', esc_attr( $tag->type ),$validation_error );
	
	foreach ((array)$tag->options as $option){
		$segments = explode(':', $option);
		list($name, $value) = $segments;
		$html .= sprintf( '<input type="hidden" name="%1$s" value="%2$s" />', esc_attr( $name ), esc_attr( $value ),$validation_error );
	}
	
	$di_lm_nonce = wp_create_nonce( 'di_lm_nonce_action' );
	$html .= sprintf('<input type="hidden" name="di_lm_nonce_action" value="%1$s" />',$di_lm_nonce);
	
	$di_lm_get_url_args = di_lm_get_url_args(); //$_GET
	foreach ((array)$di_lm_get_url_args as $key => $value){
		if (substr( $key, 0, 3 ) == "_wp") continue;
		$html .= sprintf( '<input type="hidden" name="%1$s" value="%2$s" />', esc_attr( $key ), esc_attr( $value ) );
		
	}
	return $html;
}


/* Tag generator */
add_action( 'admin_init', 'wpcf7_add_tag_generator_lm', 20 );
function wpcf7_add_tag_generator_lm() {
	if (! class_exists('WPCF7_TagGenerator')) return ;
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'lm', __( 'lm', 'contact-form-7' ),
		'wpcf7_tag_generator_lm' );
}
function wpcf7_tag_generator_lm( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'lm';

	$description = __( "לעזרה ופרטים נוספים פנו ל %s", 'contact-form-7' );
	$desc_link = wpcf7_link( __( 'http://www.leadmanager.co.il/kb', 'contact-form-7' ), __( 'מרכז תמיכה', 'contact-form-7' ) );

?>
<script>
function di_extract_from_string_url(){
	ii = 0;
    var lm_sURL = document.getElementById('di_lm_sURL').value;
	if (lm_sURL.indexOf("?") > 0) {
        var lm_arrURLParams = lm_sURL.split("?")[1].split("&");
        var lm_arrParamNames = new Array(lm_arrURLParams.length);
		for (i = 0; i < lm_arrURLParams.length; i++) {
            if (lm_arrURLParams[i].split("=")[0] == 'lm_form'){
				//js
				document.getElementById('tag-generator-panel-lm-lm_form').value = lm_arrURLParams[i].split("=")[1];
				//jQ
				_wpcf7.taggen.update(jQuery(document.getElementById('tag-generator-panel-lm-lm_form')).parents('form:first'))
				ii++;
			}else if(lm_arrURLParams[i].split("=")[0] == 'lm_key'){
				//js
				document.getElementById('tag-generator-panel-lm-lm_key').value = lm_arrURLParams[i].split("=")[1];
				//jQ
				_wpcf7.taggen.update(jQuery(document.getElementById('tag-generator-panel-lm-lm_key')).parents('form:first'))
				ii++;
			}else{
				
			}
        }
	}
	if(ii < 2) alert("כתובת לא תקינה, חסרים נתונים.");
	
	//document.getElementById('tag_code').value = 'LOL';
}
</script>


<div class="control-box" style="direction: rtl;">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>
<table class="form-table" style="direction: ltr;">
<tbody>
	<tr>
		<td colspan="2"><hr/></td>
	</tr>

	<tr>
	<th scope="row"><label for="lm_URL">lm API URL</label></th>
	<td><input style="float:left;" type="text" name="lm_URL" class="tg-name oneline" id="di_lm_sURL" /> <input style="float:left;" class="button" type="button" value="convert" onclick="di_extract_from_string_url();" ></td>
	</tr>
	<tr>
		<td colspan="2">	<p class="description mail-tag" style="float: right; direction: rtl;">כלי להמרת כתובת API. ניתן גם לדלג ולמלא ידנית את השדות שמתחת.</p></td>
	</tr>	
	<tr>
		<td colspan="2"><hr/></td>
	</tr>
	
	<tr>
		<th scope="row">Post method</th>
		<td>
			<fieldset>
			<legend class="screen-reader-text">Post method</legend>
			<select name="tagtype" style="width: 200px">
				<option selected="selected" value="lm">Default - redirect</option>
				<option value="lm_pixel">Pixel embedded</option>
				<option value="lm_iframe">iFrame embedded</option>
			</select>
			</fieldset>
		</td>
	</tr>	
	<tr>
		<th scope="row">Use email ?</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text">Use email ?</legend>
			<label><input name="alsosendemail:yes" class="option" type="checkbox" value="on"> Yes - also send email</label><br>
		</fieldset>			
		</td>
	</tr>	
	


	
	
	
	<tr>
		<td colspan="2"><hr/></td>
	</tr>
	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>


	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-lm_form' ); ?>"><?php echo esc_html( __( 'lm_form', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="lm_form" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-lm_form' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-lm_key' ); ?>"><?php echo esc_html( __( 'lm_key', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="lm_key" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-lm_key' ); ?>" /></td>
	</tr>

</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input id="tag_code" type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag" style="float: right; direction: rtl;">הוספת תגית זו תשגר את הטופס למערכת בהתאם להגדרות שהזנת מעל.</p>
</div>
<?php
}

add_action( 'wpcf7_before_send_mail', 'di_lm_wpcf7_disabl_email_and_GOAL' );
function di_lm_wpcf7_disabl_email_and_GOAL( $the_contact_form ) {
    $di_lm_nonce_action = (isset($_POST['di_lm_nonce_action'])) ? $_POST['di_lm_nonce_action'] : '';
	if (!wp_verify_nonce( $di_lm_nonce_action, 'di_lm_nonce_action' )) return; 
	$wpcf7 = WPCF7_ContactForm::get_current();
    $get_properties = $wpcf7->get_properties();
	$backup_additional_settings = isset($get_properties['additional_settings']) ? $get_properties['additional_settings'] : '';
	
	
	$submission = WPCF7_Submission::get_instance();
	$data = $submission->get_posted_data();
	
	if (isset($data['your-recipient']) && is_email($data['your-recipient'])){
		//$wpcf7->skip_mail = null;
	}elseif (isset($data['alsosendemail'])){
		//$wpcf7->skip_mail = null;
	}else{
		$wpcf7->skip_mail = true;
	}	
	
	
	$url = LM_API_TARGET_URL_V1 . '?' . http_build_query((array)$_POST);
	if (isset($_POST['lm_post_method']) && $_POST['lm_post_method'] == 'lm_iframe'){
		$wpcf7->set_properties( array(
			'additional_settings' => sprintf('on_sent_ok: "!function(){var e=document.createElement(\'iframe\');e.src=\'%1$s\',e.width=1,e.height=1,document.body.appendChild(e)}();"',$url) . "\r\n" . $backup_additional_settings,
		) );
	}elseif (isset($_POST['lm_post_method']) && $_POST['lm_post_method'] == 'lm_pixel'){
		$wpcf7->set_properties( array(
			'additional_settings' => sprintf('on_sent_ok: "!function(){var e=document.createElement(\'img\');e.src=\'%1$s\',e.width=1,e.height=1,document.body.appendChild(e)}();"',$url) . "\r\n" . $backup_additional_settings,
		) );
	}else{
		$wpcf7->set_properties( array(
			'additional_settings' => sprintf('on_sent_ok: "location.replace(%1$s);"',"'$url'"),
		) );
	}
	
}
/*location.href */


add_action('init','di_lm_register_session');
function di_lm_register_session(){
    if( !session_id() )
        session_start();
	
	$di_lm_url_args = (isset($_SESSION['di_lm_url_args'])) ? (array)$_SESSION['di_lm_url_args'] : array();
	$http_referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
	$di_lm_url_args['lm_refext'] = (isset($di_lm_url_args['lm_refext'])) ? $di_lm_url_args['lm_refext'] : $http_referer ;
	$di_lm_url_args['lm_ref'] = $http_referer;
	$di_lm_url_args['lm_referer'] = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	if (is_admin()){
		$_SESSION['di_lm_url_args'] = $di_lm_url_args;
	}else{
		$_SESSION['di_lm_url_args'] = wp_parse_args( (array)$_GET, $di_lm_url_args );
	}
}
function di_lm_get_url_args(){
	if( !session_id() )
		return false;
	
	return (isset($_SESSION['di_lm_url_args'])) ? (array)$_SESSION['di_lm_url_args'] : array();
}
function di_lm_clear_url_args(){
	if( !session_id() )
		return false;
	$_SESSION['di_lm_url_args'] = array();
}