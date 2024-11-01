<?php
/*
Plugin Name: TemplateHelp Featured Templates
Description: Displays Featured Templates from TemplateHelp.com collection via AJAX
Author: MyTemplateStorage
Version: 4.1.2
Author URI: http://www.mytemplatestorage.com/
*/
include_once('ssga.class.php');
add_action('wp_ajax_get_url', 'get_url');
add_action('wp_ajax_nopriv_get_url', 'get_url');
add_action('wp_ajax_ga_click', 'ga_click');
define('GA_ID', 'UA-1578076-8');
define('DEFAULT_AFF', 'wpincome');
define('DEFAULT_PASS', 'd98c52ec04d5ce98f6f000a6d2b65160');
define('TH_WIDGET_VERSION', '4.1.2');
add_action('admin_menu', 'th_ft_init');
add_action('activate_template-help-featured-templates/template-help_wordpress.php', 'th_alter_table');
add_filter('plugin_action_links', 'add_settings_link', 10, 2 );
global $th_ft_widget_scripts;
$th_ft_widget_scripts=0;
add_action('wp_head', '_th_ft_css');
function _th_ft_css() {
?>
    <link rel="stylesheet" type="text/css" href="<?php echo get_option('home')?>/wp-content/plugins/<?php echo plugin_basename(dirname(__FILE__))?>/css/style.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_option('home')?>/wp-content/plugins/<?php echo plugin_basename(dirname(__FILE__))?>/css/preview.css" />
    <script type="text/javascript">
        function html_entity_decode(str) {
            var ta = document.createElement("textarea");
            ta.innerHTML=str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
            toReturn = ta.value;
            ta = null;
            return toReturn
        }
    </script>
    <?php
    $options = get_option('widget_template_help');
    $options['css'] = str_replace('._th_ft_', '.widget-area ._th_ft_',$options['css']);
    $options_post = get_option('widget_template_help_post');
    $custom_css = trim($options['css']." ".$options_post['css']);
    if ($custom_css) {
        echo "<style>$custom_css</style>";
    }
}
function add_settings_link($links, $file) {
    static $this_plugin;
    if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
    if ($file == $this_plugin){
        $settings_link = '<a href="admin.php?page=th-featured-templates">'.__("Settings", "th-featured-templates").'</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

function th_ft_init() {
    if (function_exists('add_options_page')) {
        add_options_page('TH Featured Templates', 'TH Featured Templates', 10, 'th-featured-templates', 'th_featured_templates');
    }
}

function th_alter_table() {
    global $wpdb;
    if (!$wpdb->query("ALTER TABLE $wpdb->posts ADD `wpinc_update` DATETIME NOT NULL")) {
        //$wpdb->print_error();
    }
}

function th_featured_templates() {
    $options = $newoptions = get_option('widget_template_help_post');
    if ($options['aff'] == '') {
        $newoptions['aff'] = DEFAULT_AFF;
        $newoptions['wap'] = DEFAULT_PASS;
    }
    if ( $_POST['template_help-submit'] ) {
        $newoptions['sell'] = isset($_POST['sell_tm']) ? 'tm' : strip_tags(stripslashes($_POST['sell']));
        /*title*/
        $newoptions['title'] = strip_tags(stripslashes($_POST['template_help-title']));
        /*css*/
        $newoptions['css'] = strip_tags(stripslashes($_POST['template_help-css']));
        /*randomize*/
        $newoptions['randomize'] = intval($_POST['template_help-randomize']);
        /*randomize_depth*/
        $newoptions['randomize_depth'] = (int) $_POST['template_help-randomize_depth'];
        if(($newoptions['randomize_depth']<1)||($newoptions['randomize_depth']>300))
            $newoptions['randomize_depth']=10;
        /*rel*/
        $newoptions['rel'] = intval(isset($_POST['template_help-rel']));
        /*aff*/
        $newoptions['aff'] = strip_tags(stripslashes($_POST['template_help-aff']));
        /*wap*/
        $newoptions['wap'] = strip_tags(stripslashes($_POST['template_help-wap']));
        /*pr_code*/
        $newoptions['pr_code'] = strip_tags(stripslashes($_POST['template_help-pr_code']));
        /*shop_url*/
        $newoptions['shop_url'] = strip_tags(stripslashes($_POST['template_help-shop_url']));
        /*count*/
        $newoptions['count'] = (int) $_POST['template_help-count'];
        if(($newoptions['count']<1)||($newoptions['count']>10))
            $newoptions['count']=3;
        /*fullview*/
        $newoptions['fullview'] = intval($_POST['template_help-fullview']);
        /*cat*/
        $newoptions['cat'] = strip_tags(stripslashes($_POST['template_help-cat']));
        /*lang*/
        $newoptions['lang'] = strip_tags(stripslashes($_POST['template_help-lang']));
        /*curr*/
        $newoptions['curr'] = strip_tags(stripslashes($_POST['template_help-curr']));
        /*type*/
        $newoptions['type'] = strip_tags(stripslashes($_POST['template_help-type']));
        /*keywords*/
        $newoptions['keywords'] = strip_tags(stripslashes($_POST['template_help-keywords']));
        /*vaturl*/
        $newoptions['vaturl'] = strip_tags(stripslashes($_POST['view-all-templates-url']));
        /*vattitle*/
        $newoptions['vattitle'] = strip_tags(stripslashes($_POST['view-all-templates-title']));
        /*vattarget*/
        $newoptions['vattarget'] = strip_tags(stripslashes($_POST['view-all-templates-target']));
    }
    if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('widget_template_help_post', $options);
    }
    ?>
    <div align="center" class="widget-inside" style="display:block !important">
        <h2>TH Featured Templates Options</h2>
    <form name="th_ft_form" method="post" style="width:400px;">
        <?php show_th_ft_form($options, 'left');?>
        <input type="submit" value="Update">
    </form>
    </div><?php
}
function th_ft( $atts, $content = null ) {
    $options = (array) get_option('widget_template_help_post');
    extract(
        shortcode_atts(
            array(
                'count' => intval($options['count']),
                'type' => intval($options['type']),
                'cat' => intval($options['cat']),
                'lang' => intval($options['lang']),
                'curr' => intval($options['curr']),
                'title' => wp_specialchars($options['title'], true),
                'keywords' => wp_specialchars($options['keywords'], true),
            ),
        $atts)
    );
    $options['count'] = $count;
    $options['type'] = $type;
    $options['cat'] = $cat;
    $options['title'] = $title;
    $options['keywords'] = $keywords;
    $options['lang'] = $lang;
    $options['curr'] = $curr;
    return (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/feed/') ? '' : show_th_ft_widget($options, 0);
}
add_shortcode('th_ft', 'th_ft');
function get_categories_list() {
    $cats = array();
    $file = @fopen("http://api.templatemonster.com/wpinc/categories.txt", "r");
    if ($file) {
        while ($fr = fgets($file, 1024)) {
            $fr = explode("\t", trim($fr));
            $cats[$fr[0]] = $fr[1];
        }
    }
    return $cats;
}
function get_curr_list() {
    $curr = array();
    $file = @fopen("http://www.templatemonster.com/webapi/currency.php?login=" . DEFAULT_AFF . "&webapipassword=" . DEFAULT_PASS, "r");
    if ($file) {
        while ($fr = fgets($file, 1024)) {
            $fr = explode("\t", trim($fr));
            $curr[$fr[2]] = $fr[1];
        }
    }
    return $curr;
}

function get_types_list() {
    $types = array();
    $file = @fopen("http://api.templatemonster.com/wpinc/types.txt", "r");
    if ($file) {
        while ($fr = fgets($file, 1024)) {
            $fr = explode("\t", trim($fr));
            $types[$fr[0]] = $fr[1];
        }
    }
    return $types;
}

function show_th_ft_form($options, $align='right') {
    echo '<div style="text-align:' . $align . '">
    <label for="template_help-title" style="line-height:35px;display:block;">';
    _e('Widget title:', 'widgets');
    echo '<input style="width:99%;" type="text" id="template_help-title" name="template_help-title" value="' . wp_specialchars($options['title'], true) . '" />
    </label>

<label for="template_help-lang" style="line-height:35px;display:block;">';
    _e('Language:', 'widgets');
    echo '</label><select style="width:170px;font-size:11px;" id="template_help-lang" name="template_help-lang">
        <option value="English" ' . ("English" == $options['lang'] ? "selected=true" : "" ) . '>English</option>' .
        '<option value="German" ' . ("German" == $options['lang'] ? "selected=true" : "" ) . '>German</option>' .
        '<option value="Spanish" ' . ("Spanish" == $options['lang'] ? "selected=true" : "" ) . '>Spanish</option>' .
        '<option value="French" ' . ("French" == $options['lang'] ? "selected=true" : "" ) . '>French</option>' .
        '<option value="Russian" ' . ("Russian" == $options['lang'] ? "selected=true" : "" ) . '>Russian</option>' .
        '<option value="Polish" ' . ("Polish" == $options['lang'] ? "selected=true" : "" ) . '>Polish</option>' .
        '<option value="Turkish" ' . ("Turkish" == $options['lang'] ? "selected=true" : "" ) . '>Turkish</option>' .
        '<option value="Portuguese" ' . ("Portuguese" == $options['lang'] ? "selected=true" : "" ) . '>Portuguese</option>' .
        '<option value="Italian" ' . ("Italian" == $options['lang'] ? "selected=true" : "" ) . '>Italian</option>';
       echo '</select>

<label for="template_help-curr" style="line-height:35px;display:block;">';
    _e('Currency:', 'widgets');
    echo '</label><select style="width:170px;font-size:11px;" id="template_help-curr" name="template_help-curr">';
$curr = get_curr_list();
foreach ($curr as $id => $name)
{
echo '<option value="' . $id . '" ' . ($id == $options['curr'] ? "selected=true" : "" ) . '>' . $name . '</option>';
}
    echo '</select>


    <label for="template_help-aff" style="line-height:35px;display:block;">';
    _e('Affiliate ID:', 'widgets');
    echo '</label><input style="width:99%;" type="text" id="template_help-aff" name="template_help-aff" value="' . wp_specialchars($options['aff'], true) . '" />
    <label for="template_help-wap" style="line-height:35px;display:block;">';
    _e('WebAPI Password:', 'widgets');
    echo '<input style="width:99%;" type="text" id="template_help-wap" name="template_help-wap" value="' . wp_specialchars($options['wap'], true) . '" />
    </label><br/>';
    echo '<div style="text-align:' . $align . ';">
    <label for="sell_tm" style="text-align:' . $align . ';width:190px;"><input type="checkbox" id="sell_tm" name="sell_tm">&nbsp;';
    _e('I want to sell through TemplateMonster.com', 'widgets');
    echo '</label><br/>
    <fieldset id="my_tools" style="border:1px solid #ccc;padding:3px;text-align:' . $align . '">
        <legend style="color:#777;">My affiliates tools:</legend>
        <label for="sell_aff" style="width:190px;"><input type="radio" name="sell" value="aff" id="sell_aff" class="sell">
        I want to sell through my<br/>affiliates shop';
        echo '</label><br/><br/>
        <label for="template_help-pr_code">';
        _e('My Preset code:', 'widgets');
        echo '<input type="text" id="template_help-pr_code" name="template_help-pr_code" value="' . wp_specialchars($options['pr_code'], true) . '" />
        </label><br/><br/>
        <label for="sell_rms" style="width:190px;"><input type="radio" name="sell" value="rms" id="sell_rms" class="sell">
        I want to sell through my<br/>Ready Made Shop';
        echo '</label><br/><br/>
        <label for="template_help-shop_url">';
        _e('Shop URL:', 'widgets');
        echo '<input type="text" id="template_help-shop_url" name="template_help-shop_url" value="' . wp_specialchars($options['shop_url'], true) . '" />
        </label><br/>
    </fieldset></div>';

    echo '<label for="template_help-count" style="line-height:35px;display:block;">';
    _e('Number of templates to display: (1-10)', 'widgets');
    echo '<input type="text" id="template_help-count" name="template_help-count" value="' . $options['count'] . '" style="width:22px" />
    </label>

    <label for="template_help-fullview" style="line-height:35px;display:block;">';
    _e('Display template\'s information:', 'widgets');
    $fullview = wp_specialchars($options['fullview'], true);
    echo '<br/>
    <input type="radio"    name="template_help-fullview" value="1"' . ($fullview == 1 ? " checked" : "") . '/> Full Details
    <input type="radio"    name="template_help-fullview" value="0"' . ($fullview == 0 ? " checked" : "") . '/> Shorten Preview
    </label>
    <!--<label for="template_help-rel" style="line-height:35px;display:block;">';
    _e('Show relevant templates:', 'widgets');
    $ckeck_rel = wp_specialchars($options['rel'], 0) ? ' checked' : '';
    echo ' <input type="checkbox" id="template_help-rel" name="template_help-rel"' . $ckeck_rel . ' value="1">
    </label>-->

    <label for="template_help-randomize" style="line-height:35px;display:block;">';
    _e('Randomize: ', 'widgets');
    $randomize = wp_specialchars($options['randomize'], 1);
    echo '<br/>
    <input type="radio"    name="template_help-randomize" value="1"' . ($randomize == 1 ? " checked" : "") . '/> Yes
    <input type="radio"    name="template_help-randomize" value="0"' . ($randomize == 0 ? " checked" : "") . '/> No
    </label>

    <label for="template_help-randomize_depth" style="line-height:35px;display:block;">';
    _e('Randomize Depth: ', 'widgets');
    if ($options['randomize_depth']<1 || $options['randomize_depth'] > 300)
        $randomize_depth = 10;
    else
        $randomize_depth = $options['randomize_depth'];
    echo '<input type="text" id="template_help-randomize_depth" name="template_help-randomize_depth" value="'.$randomize_depth.'" style="width:32px" />
    </label>

<div class="no_rel">
    <label for="template_help-cats" style="line-height:35px;display:block;">';
    _e('Categories:', 'widgets');
    echo '</label>
    <select style="width:170px;font-size:11px;" id="template_help-cats" name="template_help-cat">
            <option value="All" ' . ("All" == $options['cat'] ? "selected=true" : "" ) . '>Show all</option>';
    $cats = get_categories_list();
        foreach ($cats as $id => $name) {
            echo '<option value="' . $id . '" ' . ($id == $options['cat'] ? "selected=true" : "" ) . '>' . $name . '</option>';
        }
     echo '</select>

    <label for="template_help-types" style="line-height:35px;display:block;">';
    _e('Types:', 'widgets');
    echo '</label>
    <select style="width:170px;font-size:11px;" id="template_help-types" name="template_help-type">
        <option value="All" ' . ("All" == $options['type'] ? "selected=true" : "" ) . '>Show all</option>';
    $types = get_types_list();
        foreach ($types as $id => $name) {
            echo '<option value="' . $id . '" ' . ($id == $options['type'] ? "selected=true" : "" ) . '>' . $name . '</option>';
        }
    echo '
    </select>

    <label for="template_help-types" style="line-height:35px;display:block;">';
    _e('Keywords:', 'widgets');
    echo '</label>
    <textarea style="width:100%" name="template_help-keywords">' . wp_specialchars($options['keywords'], true) . '</textarea>
</div>
    <label for="template_help-css" style="line-height:35px;display:block;">';
    _e('Custom CSS:', 'widgets');
    echo '</label>
    <textarea style="width:100%;height:150px;" name="template_help-css">' . wp_specialchars($options['css'], true) . '</textarea>

    <fieldset style="border:1px solid #ccc;padding:3px;margin:5px 0" >
    <legend style="color:#777;">View All Templates Button:</legend>
    <label for="view-all-templates-url" style="line-height:35px;display:block;">';
        _e('URL (<em>optional</em>):', 'widgets');
        echo '<input type="text" id="view-all-templates-url" name="view-all-templates-url" value="' . wp_specialchars($options['vaturl'], true) . '" />
    </label>
    <label for="view-all-templates-title" style="line-height:35px;display:block;">';
        _e('Title (<em>optional</em>):', 'widgets');
        echo '<input type="text" id="view-all-templates-title" name="view-all-templates-title" value="' . wp_specialchars($options['vattitle'], true) . '" />
    </label>
    <label for="view-all-templates-title" style="line-height:35px;display:block;">';
        _e('Link target (<em>optional</em>):', 'widgets');
        echo '<input type="text" id="view-all-templates-target" name="view-all-templates-target" value="' . wp_specialchars($options['vattarget'], true) . '" />
    </label>
  </fieldset>
    <input type="hidden" name="template_help-submit" id="template_help-submit" value="1" />
    </div>';
    ?>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script type="text/javascript">
        function aff_tool_check() {
            if (jQuery('.widget-inside #sell_aff').is(':checked')) {
                jQuery('.widget-inside #template_help-pr_code').removeAttr('disabled');
                jQuery('.widget-inside #template_help-shop_url').attr('disabled', 1);
            } else {
                jQuery('.widget-inside #template_help-pr_code').attr('disabled', 1);
                jQuery('.widget-inside #template_help-shop_url').removeAttr('disabled');
            }
        }
        function pars_check() {
            if (jQuery('input[name=template_help-rel]').is(':checked')) {
                jQuery('.no_rel').css('display', 'none');
            } else {
                jQuery('.no_rel').css('display', 'block');
            }
        }
        jQuery(function(){
            <?php
            $sell = wp_specialchars($options['sell'], true);
            if ($sell != 'aff' && $sell != 'rms') {
                $sell = 'tm';
            }
            ?>
            <?php if ($sell == 'aff') { ?>
            jQuery('.widget-inside #sell_aff').attr('checked',1);
            <?php } elseif ($sell == 'rms') { ?>
            jQuery('.widget-inside #sell_rms').attr('checked',1);
            <?php } ?>
            aff_tool_check();
            pars_check();
            <?php if ($sell == 'tm') { ?>
            jQuery('.widget-inside #sell_tm').attr('checked',1);
            jQuery('.widget-inside #my_tools').css('display','none');
            <?php } ?>
            jQuery('.widget-inside #sell_tm').change(function(){
                var my_tools = jQuery(this).attr('checked') ? 'none' : 'block';
                jQuery('.widget-inside #my_tools').css('display', my_tools);
            });
            jQuery('.widget-inside .sell').change(function(){
                aff_tool_check();
            });
            jQuery('input[name=template_help-rel]').change(function(){
                pars_check();
            });
        });
    </script><?php
}
// This gets called at the plugins_loaded action
function widget_template_help_init() {
    // Check for the required API functions
    if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
    return;
    // This saves options and prints the widget's config form.
    function widget_template_help_control() {
        $options = $newoptions = get_option('widget_template_help');
        if ( $_POST['template_help-submit'] ) {
            $newoptions['sell'] = isset($_POST['sell_tm']) ? 'tm' : strip_tags(stripslashes($_POST['sell']));
            /*title*/
            $newoptions['title'] = strip_tags(stripslashes($_POST['template_help-title']));
            /*css*/
            $newoptions['css'] = strip_tags(stripslashes($_POST['template_help-css']));
            /*randomize*/
            $newoptions['randomize'] = intval($_POST['template_help-randomize']);
            /*randomize_depth*/
            $newoptions['randomize_depth'] = (int) $_POST['template_help-randomize_depth'];
            if(($newoptions['randomize_depth']<1)||($newoptions['randomize_depth']>300))
                $newoptions['randomize_depth']=10;
            /*rel*/
            $newoptions['rel'] = intval(isset($_POST['template_help-rel']));
            /*aff*/
            $newoptions['aff'] = strip_tags(stripslashes($_POST['template_help-aff']));
            /*wap*/
            $newoptions['wap'] = strip_tags(stripslashes($_POST['template_help-wap']));
            /*pr_code*/
            $newoptions['pr_code'] = strip_tags(stripslashes($_POST['template_help-pr_code']));
            /*shop_url*/
            $newoptions['shop_url'] = strip_tags(stripslashes($_POST['template_help-shop_url']));
            /*count*/
            $newoptions['count'] = (int) $_POST['template_help-count'];
            if(($newoptions['count']<1)||($newoptions['count']>10))
                $newoptions['count']=3;
            /*fullview*/
            $newoptions['fullview'] = intval($_POST['template_help-fullview']);
            /*cat*/
            $newoptions['cat'] = strip_tags(stripslashes($_POST['template_help-cat']));
            /*lang*/
            $newoptions['lang'] = strip_tags(stripslashes($_POST['template_help-lang']));
            /*curr*/
            $newoptions['curr'] = strip_tags(stripslashes($_POST['template_help-curr']));
            /*type*/
            $newoptions['type'] = strip_tags(stripslashes($_POST['template_help-type']));
            /*keywords*/
            $newoptions['keywords'] = strip_tags(stripslashes($_POST['template_help-keywords']));
            /*vaturl*/
            $newoptions['vaturl'] = strip_tags(stripslashes($_POST['view-all-templates-url']));
            /*vattitle*/
            $newoptions['vattitle'] = strip_tags(stripslashes($_POST['view-all-templates-title']));
            /*vattarget*/
            $newoptions['vattarget'] = strip_tags(stripslashes($_POST['view-all-templates-target']));
        }
        if ($options['aff'] == '') {
            $newoptions['aff'] = DEFAULT_AFF;
            $newoptions['wap'] = DEFAULT_PASS;
        }
        if ( $options != $newoptions ) {
            $options = $newoptions;
            update_option('widget_template_help', $options);
        }
        show_th_ft_form($options);
    }
    function th_ft_widget_scripts() {
        global $post;
        ?>
        <script type="text/javascript"> if (window.jQuery == undefined) document.write( unescape('%3Cscript src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"%3E%3C/script%3E') );</script>
        <script type="text/javascript" src="<?php echo get_option('home')?>/wp-content/plugins/<?php echo plugin_basename(dirname(__FILE__))?>/js/preview_templates.js"></script>
        <script type="text/javascript">
            jQuery(function() {
                // jQuery('._th_ft_view_all_button').live('click',function(){
                jQuery('._th_ft_view_all_button').on('click', 'a', function(){
                    jQuery.post("<?php echo get_option('home')?>/wp-admin/admin-ajax.php",
                        {action: "ga_click", event: "Click-view-all-templates"}
                    );
                });
                // jQuery('._th_ft_details, ._th_ft_image_link').live('click',function(){
                jQuery('._th_ft_details, ._th_ft_image_link').on('click', 'a' ,function(){
                    var $obj = jQuery(this).parent();
                    if (!$obj.hasClass('_th_ft_item'))
                        $obj = $obj.parent();
                    jQuery.post("<?php echo get_option('home')?>/wp-admin/admin-ajax.php",
                        {action: "ga_click", event: "Click-view-template", item: $obj.attr('t_id'), title: "<?php echo addslashes($post->post_title);?>"}
                    );
                });
            });
        </script>
        <?php
    }

    function show_th_ft_widget($options, $echo=true) {
        global $th_ft_widget_scripts, $post, $wpdb;
         if (isset($post->wpinc_update) && $post->wpinc_update<$post->post_modified) {
            $wpdb->query("UPDATE $wpdb->posts SET wpinc_update = post_modified WHERE ID={$post->ID}");
            $wpinc_update = 1;
        } else {
            $wpinc_update = 0;
        }
        $tag_list = wp_get_post_terms($post->ID, 'post_tag', array());
        $tags = array();
        if ($tag_list) {
            foreach ($tag_list as $tag) {
                $tags[] = $tag->name;
            }
        }
        $categories = get_the_category();
        $cats = array();
        if ($categories) {
            foreach ($categories as $cat) {
                $cats[] = $cat->name;
            }
        }
        $keywords = isset($options['keywords']) ? $options['keywords'] : '';
        if($options['lang'] == 'German') {$viewtemplate = 'Ansicht Vorlage';}
        elseif($options['lang'] == 'Spanish'){$viewtemplate = 'Ver la Plantilla';}
        elseif($options['lang'] == 'French'){$viewtemplate = 'Voir Mod&egrave;le';}
        elseif($options['lang'] == 'Russian'){$viewtemplate = '&#1044;&#1077;&#1090;&#1072;&#1083;&#1080; &#1064;&#1072;&#1073;&#1083;&#1086;&#1085;&#1072;';}
        elseif($options['lang'] == 'Polish'){$viewtemplate = 'Zobacz Szablon';}
        elseif($options['lang'] == 'Turkish'){$viewtemplate = 'G&oumlr&uumln&uumlm &#350;ablonu';}
        elseif($options['lang'] == 'Portuguese'){$viewtemplate = 'Vis&atilde;o Modelo';}
        elseif($options['lang'] == 'Italian'){$viewtemplate = 'Vedere Modello';}
        else{$viewtemplate = 'View Template';}
        $result = '<div class="_th_ft_block clear2"><h4 class="_th_ft_title">' . $options['title'] .'</h4><div id="templates_'.$th_ft_widget_scripts.'" class="_th_ft_templates clear2">';
            for ($i=1; $i<=$options['count']; $i++) {
                //<img class="_th_ft_image" src="'.get_option('home').'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/img/ajax-loader.gif" alt="template #"/>
                $result .= '<div class="_th_ft_item">
                    <a class="_th_ft_image_link" onmouseout="hidetrail()" target="_blank" href="//www.templatemonster.com/?aff=' . trim($options['aff']) . '">
                        <img class="_th_ft_image" alt=""/>
                    </a>
                    <div class="_th_ft_bottext">
                        <a target="_blank" class="_th_ft_details" href="#">'.$viewtemplate.'</a>
                    </div>
                </div>';
            }
            $result .= '</div>
            <div class="clear2"></div>
        </div>';
        if($options['vaturl'] != '') {
      $result .= '<div class="_th_ft_view_all_button">'
          .'<a target="'.$options['vattarget'].'" href="'.$options['vaturl'].'" title="'.$options['vattitle'].'" id="view_all_templates" class="button_lbg"><span class="button_rbg"><span class="button_bg">'.$options['vattitle'].'</span></span></a>'
          .'</div>';
    }
    $result .='<div class="clear2"></div>';
    if (!$th_ft_widget_scripts)
            $result .= th_ft_widget_scripts();

$sell= isset($options['sell']) ? trim($options['sell']) : 'tm';
$rel= (int)isset($options['rel']) && $options['rel'];
$bottext = $options['fullview'] ? '$obj.find("._th_ft_bottext").html(html_entity_decode("&lt;div class=\'_th_ft_type\'&gt;"+data.templates[i].type+"&lt;/div&gt;&lt;div class=\'_th_ft_info\'&gt;&lt;a class=\'_th_ft_price\' href=\'"+data.templates[i].cart+"\' target=\'_blank\'&gt;"+data.templates[i].price+"&lt;/a&gt; &lt;a class=\'_th_ft_details\' href=\'"+data.templates[i].href+"\' target=\'_blank\'&gt;"+data.templates[i].view+"&lt;/a&gt;&lt;/div&gt;&lt;div class=\'_th_ft_downloads\'&gt;Downloads: &lt;span class=\'_th_ft_qtydown\'&gt;"+data.templates[i].downloads+"&lt;span&gt;&lt;/div&gt;"));' : '$obj.find("._th_ft_bottext a").attr("href",data.templates[i].href);';
$widget = $echo ? 'sidebar' : 'post';
$ajax_loader = get_option('home').'/wp-content/plugins/' . plugin_basename(dirname(__FILE__)) . '/img/ajax-loader.gif';
$request_url = isset($post->ID) && $post->ID ? get_option('home') . '/?p=' . $post->ID : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$result .= '<script type="text/javascript">
            jQuery(function() {
                 jQuery("._th_ft_image").attr("src","' . $ajax_loader . '");
                jQuery.getJSON("' . get_option('home') . '/wp-admin/admin-ajax.php",
                {action:"get_url",
                request_url:"' . $request_url . '",
                count:' . $options['count'] . ', type:' . intval($options['type']) . ', cat:' . intval($options['cat']) . ',
                title:"' . addslashes($post->post_title) . '",
                widget:"' . $widget . '",
                keywords:"' . $keywords . '",
                rel:"' . $rel . '",
                wpinc_update:' . $wpinc_update . ',
                sell:"' . $sell . '", shop_url:"' . $options['shop_url'] . '", pr_code:"' . $options['pr_code'] . '"},
                function(data){
                    if (typeof(data.error) != "undefined" && !data.error) {
                        var imgs = new Array();
                        jQuery.each(jQuery("#templates_' . $th_ft_widget_scripts . ' ._th_ft_item"), function(i, item) {
                            if (data.templates[i]) {
                                var $obj = jQuery(this);
                                $obj.attr("t_id", data.templates[i].tid);
                                imgs[i] = new Image();
                                jQuery(imgs[i]).load(function(){
                                  $obj.find("a img").fadeOut();
                                  if (typeof(imgs[i]) != "undefined") {
                                      $obj.find("a:has(\'img\')").animate({width:imgs[i].width, height:imgs[i].height, marginTop:154-imgs[i].height}, 400, function(){
                                          jQuery(this).find("img").css("padding", "0px").attr({src:data.templates[i].src, alt:"template #"+data.templates[i].tid}).fadeIn();
                                        jQuery(this).mouseover(function(){
                                              showtrail(\'"\'+data.templates[i].big.src,"Template "+data.templates[i].tid,parseInt(data.templates[i].big.width),parseInt(data.templates[i].big.height));
                                        });
                                      }).attr("href", data.templates[i].href);
                                      ' . $bottext . '
                                  } else {
                                    $obj.remove();
                                }
                              }).attr("src",data.templates[i].src);
                            } else {
                                jQuery(this).remove();
                            }
                        });
                        jQuery("#templates_' . $th_ft_widget_scripts . ' ._th_ft_bottext").fadeIn();
                    } else {
                        jQuery.each(jQuery("#templates_' . $th_ft_widget_scripts . ' ._th_ft_item"), function(i, item) {
                            jQuery(this).find("a").css({width:"145px", height:"156px", background:"url(' . get_option('home') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__)) . '/img/preload-template.jpg)", border: "0px"}).attr("href","//www.templatemonster.com/?aff=' . trim($options['aff']) . '").html("");
                        });
                    }
                });
            });
        </script>';
        $th_ft_widget_scripts++;
        if ($echo)
            echo $result;
        else
            return $result;
    }
    // This prints the widget
    function widget_template_help($args) {
        extract($args);
        $options = (array) get_option('widget_template_help');
        show_th_ft_widget($options);
    }
    // Tell Dynamic Sidebar about our new widget and its control
    register_sidebar_widget(array('TemplateHelp Featured Templates', 'widgets'), 'widget_template_help');
    register_widget_control(array('TemplateHelp Featured Templates', 'widgets'), 'widget_template_help_control');
}
function ga_click() {
    $event = isset($_REQUEST['event']) ? trim($_REQUEST['event']) : '';
    $title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : '';
    $item = isset($_REQUEST['item']) ? intval($_REQUEST['item']) : 0;
    $host = "http://".$_SERVER['HTTP_HOST'];
    $ga = new Elements_Tools_Serversideanalytics();
    $ga->setAccountId(GA_ID);
    $ga->setCharset("UTF-8");
    $ga->setHostName($_SERVER['HTTP_HOST']);
    $ga->setPageTitle($title);
    $ga->setLanguage("en");
    if ($item)
        $ga->setEvent($event, $host, $item);
    else
        $ga->setEvent($event, $host);
    $ga->createEvent();
}
function get_url() {
    header('Cache-control: no-cache');
    $types = get_types_list();
    $wpinc_update = intval($_REQUEST['wpinc_update']);
    $sell = trim($_REQUEST['sell']);
    $widget = trim($_REQUEST['widget']);
    $options = ($widget == 'sidebar') ? (array) get_option('widget_template_help') : (array) get_option('widget_template_help_post');
    $randomize = isset($options['randomize']) && $options['randomize'];
    $randomize_depth = $randomize ? (isset($options['randomize_depth']) ? $options['randomize_depth'] : 10) : 0;
    $type = intval($_REQUEST['type']);
    $cat = intval($_REQUEST['cat']);
    $rel = intval($_REQUEST['rel']);
    $keywords = trim($_REQUEST['keywords']);
    $title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : '';
    /*
    $tags = trim($_REQUEST['tags']);
    $cats = trim($_REQUEST['cats']);
    $excerpt = trim($_REQUEST['excerpt']);
    */
    $count = intval($_REQUEST['count']);
    if (!$count)
        $count=4;
    $aff = trim($options['aff']);
    $wap = trim($options['wap']);
    if ($aff=='') {
        $aff = DEFAULT_AFF;
        $wap = DEFAULT_PASS;
    }
    switch ($sell) {
        case 'tm':  $pr_code = $shop_url = '';
        break;
        case 'aff': $pr_code = trim($_GET['pr_code']);
            $shop_url = '';
        break;
        case 'rms': $pr_code = '';
            $shop_url = trim($_GET['shop_url']);
        break;
    }
    $context = stream_context_create(
        array(
            'http' => array(
                'timeout' => 10    // Timeout in seconds
            )
        )
    );
    $data_url = array(
        'login'=>$aff,
        'webapipassword'=>$wap,
        'type'=>$type,
        'cat'=>$cat,
        'rel'=>$rel,
        'count'=>$count,
        'pr_code'=>$pr_code,
        'request_url'=>$_REQUEST['request_url'],
        'wpinc_update'=>$wpinc_update,
        'widget_version'=>TH_WIDGET_VERSION,
        'randomize_depth'=>$randomize_depth
    );
    if ($wpinc_update) {
        $data_url = array_merge(
            $data_url, array(
                'keywords'=>$keywords,
                /*
                'tags'=>$tags,
                'cats'=>$cats,
                'title'=>$title,
                'excerpt'=>$excerpt
                */
            )
        );
    }
    $contents = trim(@file_get_contents('http://api.templatemonster.com/wpinc.php?'.http_build_query($data_url), 0, $context));
    if (!empty($contents)) {
        $items = (strpos($contents, 'Unauthorized usage')!==false) ? array() : explode("\n", $contents);
        $templates = array();
        if (!empty($items) || count($items)>$count) {
            $ga = new Elements_Tools_Serversideanalytics();
            $ga->setAccountId(GA_ID);
            $ga->setCharset("UTF-8");
            $ga->setHostName($_SERVER['HTTP_HOST']);
            $ga->setPageTitle($title);
            $ga->setLanguage("en");
            foreach ($items as $i=>$item) {
                if (!empty($item)) {
                    $template=explode("|", $item);
                    if (!intval($template[2])) {
                        $templates = array();
                        break;
                    }
                    $templates[$i]['src'] = $template[0];
                    $size = @getimagesize($templates[$i]['src']);
                    if (!$size || $size['mime'] != 'image/jpeg')
                    $templates[$i]['src'] = get_option('home')."/wp-content/plugins/".plugin_basename(dirname(__FILE__))."/img/preload-template.jpg";
                    $templates[$i]['cart'] = $template[4];
                    $templates[$i]['big'] = array('src'=>$template[5], 'width'=>$template[6], 'height'=>$template[7]);
                    $templates[$i]['type'] = isset($types[$template[8]]) ? $types[$template[8]] : '';
                    if($options['lang'] != 'English') {
                        if($options['lang'] == 'German') {
                            $suffix = '/de/vorlagen/';
                            $cartsuf = '/de/';
                            $pricet = 'Preis: ';
                            $templates[$i]['view'] = 'Sehen';
                        }
                        if($options['lang'] == 'Spanish') {
                            $suffix = '/es/plantillas/';
                            $cartsuf = '/es/';
                            $pricet = 'Precio: ';
                            $templates[$i]['view'] = 'Ver';
                        }
                        if($options['lang'] == 'French') {
                            $suffix = '/fr/modeles/';
                            $cartsuf = '/fr/';
                            $pricet = 'Prix: ';
                            $templates[$i]['view'] = 'Vour';
                        }
                        if($options['lang'] == 'Russian') {
                            $suffix = '/ru/search/';
                            $cartsuf = '/ru/';
                            $pricet = '&#1062;&#1077;&#1085;&#1072;: ';
                            $templates[$i]['view'] = '&#1044;&#1077;&#1090;&#1072;&#1083;&#1080;';
                        }
                        if($options['lang'] == 'Polish') {
                            $suffix = '/pl/szablony/';
                            $cartsuf = '/pl/';
                            $pricet = 'Cena: ';
                            $templates[$i]['view'] = 'Info';
                        }
                        if($options['lang'] == 'Turkish') {
                            $suffix = '/tr/sablonlar/';
                            $cartsuf = '/tr/';
                            $pricet = 'Fiyat: ';
                            $templates[$i]['view'] = 'Bilgi';
                        }
                        if($options['lang'] == 'Portuguese') {
                            $suffix = '/pt-br/search/';
                            $cartsuf = '/pt-br/';
                            $pricet = 'Pre&ccedil;o: ';
                            $templates[$i]['view'] = 'Vis&atilde;o';
                        }
                        if( $options['lang'] == 'Italian') {
                            $suffix = '/it/modelli/';
                            $cartsuf = '/it/';
                            $pricet = 'Prezzo: ';
                            $templates[$i]['view'] = 'Info';
                        }
                        preg_match('/[^0-9]+([0-9]+)\.html/', $template[1], $matches);
                        $t = $matches[1];
                        //$t = $templates[$i]['tid'];
                        $templates[$i]['href'] = "//www.templatemonster.com" . $suffix . "?keywords=" . $t . "&aff=" . $aff . "&utm_source=wpinc&utm_medium=wpwidget&utm_campaign=v" . TH_WIDGET_VERSION;
                    } else {
                        preg_match('/[^0-9]+([0-9]+)\.html/', $template[1], $matches);
$t = $matches[1];
//$t = $templates[$i]['tid'];
$templates[$i]['href'] = "//www.templatemonster.com/templates.php?keywords=".$t."&aff=$aff"."&utm_source=wpinc&utm_medium=wpwidget&utm_campaign=v".TH_WIDGET_VERSION;
$cartsuf = '/';
$pricet = 'Price: ';
$templates[$i]['view'] = 'View';
                    }
                    if ($pr_code) {
                        $templates[$i]['tid'] = $template[1];
                        $templates[$i]['href'] = '//www.templatehelp.com/preset/pr_preview.php?i=' . $templates[$i]['tid'].'&pr_code=' . $pr_code . '&utm_source=wpinc&utm_medium=wpwidget&utm_campaign=v'.TH_WIDGET_VERSION;
                    } else {
                        preg_match('/[^0-9]+([0-9]+)\.html/', $template[1], $matches);
                        $templates[$i]['tid'] = $matches[1];
                        $smb = '?';
                        if ($shop_url) {
                            $smb = '&';
                            //$templates[$i]['href'] = $shop_url.'/show.php?id='.$templates[$i]['tid'];
                            $templates[$i]['href'] = $shop_url . 'search?keyword=' . $templates[$i]['tid'] . '&utm_source=wpinc&utm_medium=wpwidget&utm_campaign=v' . TH_WIDGET_VERSION;
                            $templates[$i]['cart'] = $shop_url . 'search?keyword=' . $templates[$i]['tid'] . '&utm_source=wpinc&utm_medium=wpwidget&utm_campaign=v' . TH_WIDGET_VERSION;
                        } else {
                            //$templates[$i]['href'] = $template[1];
                        }
                        if ($aff AND !$shop_url) {
                            $t = $templates[$i]['tid'];
                            //$templates[$i]['cart'] = $templates[$i]['cart']."&aff=$aff&utm_source=wpinc&utm_medium=wpwidget&utm_campaign=v" . TH_WIDGET_VERSION;
                            $templates[$i]['cart'] = "https://templatemonster.com" . $cartsuf . "cart.php?add=" . $t . "&aff=" . $aff . "&utm_source=wpinc&utm_medium=wpwidget&utm_campaign=v" . TH_WIDGET_VERSION;
                        }
                    }
                    $curr2 = get_curr_list();
                    foreach ($curr2 as $cid => $cname) {
                        if($cid == $options['curr']) {
                            $currsymb = $cname;
                            if($currsymb == 'USD') {$currsymb='$';}
                            elseif($currsymb == 'EUR') {$currsymb='&#8364;';}
                            elseif($currsymb == 'CAD') {$currsymb='C&#36;';}
                            elseif($currsymb == 'GBP') {$currsymb='&#163;';}
                            elseif($currsymb == 'JPY') {$currsymb='&#165;';}
                            elseif($currsymb == 'AUD') {$currsymb='A&#36;';}
                            elseif($currsymb == 'PLN') {$currsymb='z&#322;';}
                            elseif($currsymb == 'NOK' || $currsymb == 'SEK' || $currsymb == 'DKK'){$currsymb='kr';}
                            elseif($currsymb == 'CHF') {$currsymb='Fr';}
                            elseif($currsymb == 'BRL') {$currsymb='R&#36;';}
                            elseif($currsymb == 'MXN') {$currsymb='&#8369;';}
                            elseif($currsymb == 'TRY') {$currsymb='&#8378;';}
                            elseif($currsymb == 'INR') {$currsymb='&#8377;';}
							elseif($currsymb == 'RUB') {$currsymb='P';}
							elseif($currsymb == 'CNY') {$currsymb='&#20803;';}
                        }
                    }
                    $templates[$i]['price'] = "$pricet $currsymb" . round($template[2] * $options['curr']);
                    $templates[$i]['downloads'] = $template[3];
                    $ga->setEvent("Views", "http://" . $_SERVER['HTTP_HOST'], $templates[$i]['tid']);
                    $ga->createEvent();
                }
            }
        }
    }
    echo json_encode(array('templates'=>$templates, 'error'=>empty($templates)));
    exit;
}
function th_admin_warnings() {
    $options = (array) get_option('widget_template_help');
    if ($options['aff'] == '' || $options['aff'] == DEFAULT_AFF) {
        function th_warning() {
            echo '
            <div id="th-warning" class="updated fade"><p><strong>Template-Help Widget is almost ready.</strong> You must <a href="' . get_option('home') . '/wp-admin/widgets.php">configure Affiliate and WebAPI Password</a> for it to work.</p></div>
            ';
        }
        add_action('admin_notices', 'th_warning');
    }
}
// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_template_help_init');
th_admin_warnings();
?>