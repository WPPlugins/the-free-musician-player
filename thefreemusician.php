<?php
/**
 * @package thefreemusician
 * @version 0.6.1
 */
/*
Plugin Name: thefreemusician
Plugin URI: http://thefreemusician.com
Description: Helps you put songs/albums and Artists on your site!
Author: Rob Kay
Version: 0.6.1
Author URI: http://freedomonlineservices.net
*/

include "ob_settings_v1_3.php";

// Admin options

/* What to do when the plugin is activated? */
register_activation_hook(__FILE__,'TFM_Player_plugin_install');

/* What to do when the plugin is deactivated? */
register_deactivation_hook( __FILE__, 'TFM_Player_plugin_remove' );

function  TFM_Player_plugin_install() {
/* Create a new database fields */
add_option('TFM_flush', 'true');
add_option("TFM_Player_enable_ajax");
add_option("TFM_Player_fade");
add_option("TFM_Player_id");
add_option("TFM_Player_menu");
add_option("TFM_Player_playhtml");
add_option("TFM_Player_stophtml");
add_option("TFM_Player_dl");
}

function TFM_Ajax()
{
	$tfmajax=get_option('TFM_Player_enable_ajax');
	if($tfmajax[text_string]=='')
		return 'false';
	else
		return $tfmajax[text_string];
}
function TFM_Fade()
{
	$tfmfade=get_option('TFM_Player_fade');
	if($tfmfade[text_string]=='')
		return '<p>Loading, please wait...</p>';
	else
		return $tfmfade[text_string];
}
function TFM_ID()
{
	$tfmid=get_option('TFM_Player_id');
	if($tfmid[text_string]=='')
		return '#content';
	else
		return $tfmid[text_string];
}
function TFM_Menu()
{
	$tfmid=get_option('TFM_Player_menu');
	if($tfmid[text_string]=='')
		return '#navbar';
	else
		return $tfmid[text_string];
}
function TFM_Play()
{
	$tfmplay=get_option('TFM_Player_playhtml');
	if($tfmplay[text_string]=='')
		return 'Listen';
	else
		return $tfmplay[text_string];
}
function TFM_Stop()
{
	$tfmstop=get_option('TFM_Player_stophtml');
	if($tfmstop[text_string]=='')
		return 'Stop';
	else
		return $tfmstop[text_string];
}
function TFM_Dl()
{
	$tfmdl=get_option('TFM_Player_dl');
	if($tfmdl[text_string]=='')
		return 'Download';
	else
		return $tfmdl[text_string];
}

function TFM_Player_plugin_remove() {
/* Delete the database fields */
delete_option('TFM_Player_enable_ajax');
delete_option('TFM_Player_fade');
delete_option("TFM_Player_id");
delete_option("TFM_Player_menu");
delete_option("TFM_Player_playhtml");
delete_option("TFM_Player_stophtml");
delete_option("TFM_Player_dl");
}

// Link the sm2 scripts

function TFM_Addlibs() {
$plurl = plugins_url(); 
?>

<script type="text/javascript" src="<?php echo $plurl; ?>/thefreemusician/js/soundmanager2.js"></script>
<script src="<?php echo $plurl; ?>/thefreemusician/js/jquery.ba-hashchange.js"></script>
<?php $cs=get_option('TFM_Player_custom_stylesheet'); 
if($cs[text_string]=="")
echo '<link href="'.$plurl.'/thefreemusician/TFM_style.css" rel="stylesheet" type="text/css" />';
else
echo '<link href="'.$cs[text_string].'" rel="stylesheet" type="text/css" />';
 ?>

<?php }
add_action('wp_head', 'TFM_Addlibs');
	

function tfm_jq() {
if (!is_admin()) {
    wp_enqueue_script('jquery');
 }
}
add_action('wp_enqueue_scripts', 'tfm_jq');


// Create the 'song' post type

function post_type_song() {
register_post_type('song', array(
'label' => 'Songs',
'public' => true,
'show_ui' => true,
'capability_type' => 'post',
'hierarchical' => false,
'rewrite' => array('slug' => 'music/songs', 'with_front' => false),
'query_var' => true,
'supports' => array('title', 'comments', 'editor')
) );
}

// Columns for list view

function song_edit_columns($columns)
{  
    $columns = array(
             "cb" => "<input type=\"checkbox\" />",
            "title" => "Title",
            "album" => "Album");  
   
    return $columns;  
}

// Add a meta box to the edit screen

function song_meta_box_add()  
{  
    add_meta_box( 'song-meta-box-id', 'Song Details', 'song_meta_box_render', 'song', 'normal', 'high' );  
}

// Render our meta-box

function song_meta_box_render( $post )  
{  
    $songvalues = get_post_custom( $post->ID );  
    $album = isset( $songvalues['album'] ) ? esc_attr( $songvalues['album'][0] ) : ''; 
    $track = isset( $songvalues['track'] ) ? esc_attr( $songvalues['track'][0] ) : '';
    $mpeg = isset( $songvalues['mpeg'] ) ? esc_attr( $songvalues['mpeg'][0] ) : ''; // streaming file
    $available = isset( $songvalues['available'] ) ? esc_attr( $songvalues['available'][0] ) : '';
    wp_nonce_field( 'song_meta_box_nonce', 'meta_box_nonce' );   ?> 
        <p>  
        <label for="track">Track</label>
        <input type="text" name="track" id="track" value="<?php echo $track; ?>" />
            <br>
       
            <label for="album">Album</label>  
            <select name="album" id="album">
            <?php // get an array of all the albums and make them into options available to select
            global $post;
            $tmp_post = $post;
            $myposts = get_posts('post_type=album&numberposts=-1');
            $values=array('');
            foreach($myposts as $post) :
            array_push($values, get_the_title($post->id));
            endforeach;
            $post = $tmp_post;
            unset($values[0]);
            foreach($values as $value) : ?>
                <option value="<?php echo $value; ?>" <?php selected( $album, $value ); ?>><?php echo $value; ?></option>
            <?php endforeach; ?>  
            </select>
            <br>
            <label for="mpeg">MP3</label>
            <input type="text" name="mpeg" id="mpeg" value="<?php echo $mpeg ?>" />
            <br>
            <label for="available">Available As</label>
            <select name="available" id="available">
            <option value="nodl" <?php selected( $available, 'nodl' ); ?>>Streaming Only</option>
            <option value="dl" <?php selected( $available, 'dl' ); ?>>Download</option>
            </select>
       </p>  
        <?php  
}

// Save our data to mysql
function song_meta_box_save( $post_id )  
{  
    // Bail if we're doing an auto save  
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
 
    // if our nonce isn't there, or we can't verify it, bail 
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'song_meta_box_nonce' ) ) return; 
 
    // if our current user can't edit this post, bail  
    if( !current_user_can( 'edit_post' ) ) return;  
  
    // Make sure your data is set before trying to save it  

    if( isset( $_POST['track'] ) )  
        update_post_meta( $post_id, 'track', esc_attr( $_POST['track'] ) );  
    if( isset( $_POST['mpeg'] ) )  
        update_post_meta( $post_id, 'mpeg', esc_attr( $_POST['mpeg'] ) );
    if( isset( $_POST['album'] ) )  
        update_post_meta( $post_id, 'album', esc_attr( $_POST['album'] ) );
    if( isset( $_POST['available'] ) )  
        update_post_meta( $post_id, 'available', esc_attr( $_POST['available'] ) );
        
}

// display album in list view
function song_custom_columns($column)
{
         switch ($column)  
         {  
             case "album":  
                 $custom = get_post_custom();  
                 echo $custom["album"][0];  
                 break;
         }
 }

// Make it all happen
add_action('init', 'post_type_song');
add_filter("manage_edit-song_columns", "song_edit_columns");  
add_action("manage_posts_custom_column",  "song_custom_columns");  
add_action( 'save_post', 'song_meta_box_save' );  
add_action( 'add_meta_boxes', 'song_meta_box_add' );     

// Album post type, edit columns

function post_type_album() { 
// Creates album post type
register_post_type('album', array(
'label' => 'Albums',
'public' => true,
'show_ui' => true,
'capability_type' => 'post',
'hierarchical' => false,
'rewrite' => array('slug' => 'music/albums', 'with_front' => false),
'query_var' => true,
'supports' => array('title', 'thumbnail', 'comments', 'editor')
) );
}

function album_meta_box_add()  
{  
    add_meta_box( 'album-meta-box-id', 'Album Details', 'album_meta_box_render', 'album', 'normal', 'high' );  
}

function album_meta_box_render( $post )  
{  
    $albumvalues = get_post_custom( $post->ID );  
    $artist = isset( $albumvalues['artist'] ) ? esc_attr( $albumvalues['artist'][0] ):'';
    $order = isset( $albumvalues['order'] ) ? esc_attr( $albumvalues['order'][0] ):'';
    $available = isset( $albumvalues['available'] ) ? esc_attr( $albumvalues['available'][0] ):'';
    $file = isset( $albumvalues['file'] ) ? esc_attr( $albumvalues['file'][0]):'';
    wp_nonce_field( 'album_meta_box_nonce', 'meta_box_nonce' );  
?> 
        <p>
        <label for="order">Order</label>
        <input type="text" name="order" id="order" value="<?php echo $order; ?>" /><br> 
        <label for="artist">Artist</label>  
        <select name="artist" id="artist"> 
            <?php 
            global $post;
            $tmp_post = $post;
            $myposts = get_posts('post_type=artist&numberposts=-1');
            $values=array('');
            foreach($myposts as $post) :
            array_push($values, get_the_title($post->id));
            endforeach;
            $post = $tmp_post;
            unset($values[0]);
            foreach($values as $value) : ?>
                <option value="<?php echo $value; ?>" <?php selected( $artist, $value ); ?>><?php echo $value; ?></option>
            <?php endforeach; ?>  
        </select>
        <br>
        <label for="available">Available As</label>
        <select name="available" id="available">
            <option value="nodl" <?php selected( $available, 'nodl' ); ?>>Streaming Only</option>
            <option value="dl" <?php selected( $available, 'dl' ); ?>>Download</option>
        </select>
        <br>
                <label for="file">File URL</label>
        <input type="text" name="file" id="file" value="<?php echo $file; ?>" />
    </p>  
        <?php  
}
    
function album_meta_box_save( $post_id )  
{  
    // Bail if we're doing an auto save  
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
 
    // if our nonce isn't there, or we can't verify it, bail 
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'album_meta_box_nonce' ) ) return; 
 
    // if our current user can't edit this post, bail  
    if( !current_user_can( 'edit_post' ) ) return;  
  
    // Make sure your data is set before trying to save it  

    if( isset( $_POST['artist'] ) )
        update_post_meta( $post_id, 'artist', esc_attr( $_POST['artist'] ) );  
    if( isset( $_POST['order'] ) )
        update_post_meta( $post_id, 'order', esc_attr( $_POST['order'] ) );  
    if( isset( $_POST['available'] ) )
        update_post_meta( $post_id, 'available', esc_attr( $_POST['available'] ) );
    if( isset( $_POST['file'] ) )
        update_post_meta( $post_id, 'file', esc_attr( $_POST['file']) );
}

function album_edit_columns($columns){  
         $columns = array(
             "cb" => "<input type=\"checkbox\" />",
            "title" => "Title",
         );  
   
         return $columns;  
}

// Make it happen
add_action( 'save_post', 'album_meta_box_save' );  
add_action('init', 'post_type_album');
add_action( 'add_meta_boxes', 'album_meta_box_add' );  
add_filter("manage_edit-album_columns", "album_edit_columns");  
  
// Artist post type, edit columns

function post_type_artist() { 
// Creates artist post type
register_post_type('artist', array(
'label' => 'Artists',
'public' => true,
'show_ui' => true,
'capability_type' => 'post',
'hierarchical' => false,
'rewrite' => array('slug' => 'music/artists'),
'query_var' => true,
'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt')
) );
}
 
add_action('init', 'post_type_artist');

add_filter("manage_edit-artist_columns", "artist_edit_columns");  

function artist_edit_columns($columns){  
         $columns = array(
             "cb" => "<input type=\"checkbox\" />",
            "title" => "Name",
         );
   
         return $columns;  
}

// display your artists/albums/songs

function TFM_getArtists() { 
  $args = array(
    'post_type'=> 'artist',
    'orderby'=>'rand',
    'posts_per_page'=>'-1'
  );
  $my_query = new WP_Query($args);
  if ( $my_query->have_posts() )
  {
?>
    <ul style="list-style-type: none; margin-bottom: 10px;">
    <?php while ( $my_query->have_posts() ) : $my_query->the_post();
      $img_details = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );?>
    <li style="clear:left; padding-top: 5px;">
      <img src="<?php echo $img_details[0]; ?>" align="left" style="padding-right: 10px; padding-bottom: 0px;" width="<?php echo $img_details[1]; ?>" height="<?php echo $img_details[2]; ?>" /><a href="<?php echo get_permalink( $post->ID ); ?> "><?php the_title();?></a><?php the_excerpt(); ?> 
    </li> 
    <?php endwhile; ?> 
    </ul> 
    <?php
  }
}
      
function TFM_shortcode_getArtists($atts)
{
 ob_start();
TFM_getArtists();
$ietc = ob_get_contents();
ob_end_clean();
return $ietc;
}

add_shortcode('TFM_getArtists', 'TFM_shortcode_getArtists');

function TFM_getAlbums($artist, $artistheader)
{
global $wpdb;

$Q="SELECT post_id, meta_value FROM ".$wpdb->base_prefix."postmeta WHERE meta_key = 'order'";
$Q.=" AND (post_id IN (SELECT post_id FROM ".$wpdb->base_prefix."postmeta WHERE meta_key='artist'";
$Q.=" AND meta_value='".$artist."')) ORDER BY meta_value";
    
$myalbums = $wpdb->get_results($Q);
if($artistheader==true) {
?>
<h1><?php echo $artist; ?></h1>
<?php
$args = array(
    'post_type'=> 'artist',
    'name' => $artist
);

  $my_query = new WP_Query($args);
if ( $my_query->have_posts() )
{ 
while ( $my_query->have_posts() ) : $my_query->the_post();
$img_details = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' ); ?>
<img src="<?php echo $img_details[0]; ?>" align="left" style="padding-right: 10px; padding-bottom: 0px;" width="<?php echo $img_details[1]; ?>" height="<?php echo $img_details[2]; ?>" /><?php the_content(); endwhile;
}
echo '<br/><br/>';
}
foreach($myalbums as $album) : $albumpost=get_post($album->post_id);
  getSongs($albumpost->post_title, false);
endforeach;
}

function TFM_shortcode_getAlbums($atts)
{
extract(shortcode_atts(array('artist' => '', 'header' => 'false'), $atts));
 ob_start();
TFM_getAlbums($artist, $header);
$ietc = ob_get_contents();
ob_end_clean();
return $ietc;
}

add_shortcode('TFM_getAlbums', 'TFM_shortcode_getAlbums');

function shortcode_TFM_getSong($atts)
{
extract(shortcode_atts(array('id' => $id), $atts));
$ph=TFM_Play();

return '<a href="" class="tfmtrigger" name="'.$id.'">'.$ph.'</a>';
}
add_shortcode('TFM_getSong', 'shortcode_TFM_getSong');

function getSongs($album, $lyricslink)
{ 

global $wpdb;

$Q="SELECT post_id, meta_value FROM ".$wpdb->base_prefix."postmeta WHERE meta_key = 'track'";
$Q.=" AND (post_id IN (SELECT post_id FROM ".$wpdb->base_prefix."postmeta WHERE meta_key='album'";
$Q.=" AND meta_value='".$album."')) ORDER BY meta_value";

$mysongs = $wpdb->get_results($Q);    

?>

<?php $args = array(
    'post_type'=> 'album',
    'name' => $album
);

  $my_query = new WP_Query($args);
if ( $my_query->have_posts() )
{ ?>
<h3><?php echo $album; if(get_post_meta($my_query->post->ID, 'available', true)=='dl') { ?>
      <a href="<?php echo get_post_meta($my_query->post->ID, 'file', true); ?>"><?php 
$dl=get_option('TFM_Player_dl', 'Download'); echo $dl[text_string]; ?></a>
<?php } ?></h3>
<?php
while ( $my_query->have_posts() ) : ?>
<?php 
$my_query->the_post();
?>
<?php
$img_details = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );?>
<img src="<?php echo $img_details[0]; ?>" align="left" style="padding-right: 10px; padding-bottom: 0px;" width="<?php echo $img_details[1]; ?>" height="<?php echo $img_details[2]; ?>" /><?php endwhile;
}?>
<table class="track-listing">
<?php
foreach($mysongs as $song) : $songpost=get_post($song->post_id); ?>
  <tr>
<td class="track-num"><?php echo get_post_meta($song->post_id, 'track', true); ?></td>
<td class="track-name"><?php echo $songpost->post_title; ?></td>
<td class="track-listen"><a href="" class="tfmtrigger" name="<?php echo $song->post_id;?>"><?php 
$ph=TFM_Play(); echo $ph; ?></a></td>
<td class="track-lyrics"> <a href="<?php echo get_permalink( $song->post_id ); ?>">info</a> </td> 
<?php if(get_post_meta($song->post_id, 'available', true)=='dl') { ?>
      <td class="track-download">     <a href="<?php echo get_post_meta($song->post_id, 'mpeg', true); ?>"><?php 
$dl=TFM_Dl(); echo $dl; ?></a></td>
<?php } ?>    
</tr>
<?php
endforeach; ?>
</table><br clear="left" />&nbsp;<br />
<?php }

function shortcode_tfmlink()
{
return 'Music Player Powered by <a href="http://thefreemusician.com/" target="_blank">TFM</a>.';
}

add_shortcode('tfmlink', 'shortcode_tfmlink');

function TFM_flusher() {
// Register code for your new post type here...
// register_post_type( 'custom_post_type_name', $customPostTypeDefs );
 
// Check the option we set on activation.
if (get_option('TFM_flush') == 'true') {
flush_rewrite_rules();
delete_option('TFM_flush');
}
}
 
add_action( 'init', 'TFM_flusher', 100 );

function TFM_setupJukebox()
{
  $args = array(
    'post_type'=> 'song',
      'posts_per_page'    => '-1'
  );
$plurl = plugins_url();

?>
<!--[if IE]>
<div style="display:none;" id="IEhell">iehell</div> 
<![endif]-->
<script>
jQuery(document).ready(function(){ 
       
var TFM_jukebox=new Array(); var i='';
soundManager.setup({

  // location: path to SWF files, as needed (SWF file name is appended later.)
debugMode: false,
    url: '<?php echo $plurl; ?>/thefreemusician/swf/',

  // optional: version of SM2 flash audio API to use (8 or 9; default is 8 if omitted, OK for most use cases.)
  // flashVersion: 9,

  // use soundmanager2-nodebug-jsmin.js, or disable debug mode (enabled by default) after development/testing
  // debugMode: false,

  // good to go: the onready() callback

  onready: function() {
<?php
  $my_query = new WP_Query($args);

  if ( $my_query->have_posts() )
  {
  while ( $my_query->have_posts() ) : $my_query->the_post(); ?>
  i='<?php echo $my_query->post->ID; ?>';
TFM_jukebox[i] = soundManager.createSound({id: 'a_'+'<?php echo $my_query->post->ID; ?>',
                                                 url: '<?php echo get_post_meta($my_query->post->ID, "mpeg", true); ?>'});

  <?php endwhile; } ?>
       jQuery('.tfmtrigger').live("click", function(e) { e.preventDefault(); 
                                        var nme=jQuery(this).attr('name');
                                        jQuery(document).find('.tfmpauser').removeClass('tfmpauser').addClass('tfmtrigger').html('listen');
                                        soundManager.stopAll();
                                        TFM_jukebox[nme].play();
                                        jQuery(this).addClass('tfmpauser');
                                        jQuery(this).removeClass('tfmtrigger');
                                        jQuery(this).html('<?php $sh=TFM_Stop(); echo $sh; ?>');
     });
      jQuery('.tfmpauser').live("click", function(e) { e.preventDefault();
                                                    var nme=jQuery(this).attr('name');
                                                    TFM_jukebox[nme].stop();
                                                     jQuery(this).html('<?php $ph=TFM_Play();  echo $ph; ?>');
                                                     jQuery(this).removeClass('tfmpauser');
                                                     jQuery(this).addClass('tfmtrigger');});
  }
});
    jQuery('.stopmusic').live('click', function() {soundManager.stopAll();}); 

<?php $tfmajax=TFM_Ajax(); if($tfmajax=='true') { ?>
var $tfmdivid = '<?php $divid=TFM_ID(); echo $divid; ?>';
var $tfmmenuid = '<?php $menuid=TFM_Menu(); echo $menuid; ?>';
   var siteUrl = "http://" + top.location.host.toString(),
		url = '';
	jQuery(document).on("click", "a[href^='"+siteUrl+"']:not([href*='/wp-admin/']):not([href*='/wp-login.php']):not([href$='/feed/'])", function() {
location.hash = this.pathname;
return false;
});
    jQuery("#searchform").submit(function(e) {
    location.hash = '?s=' + jQuery("#s").val();
    e.preventDefault();
    });
    jQuery(window).bind('hashchange', function(){
    url = window.location.hash.substring(1);
    if(jQuery('#IEhell').length != 0)
		{
    var parser = document.createElement('a');
    parser.href = window.location;
    url=parser.hash+parser.search;     // => "#hash"
			url='/'+url.substr(1);
		}
    if (!url) {
    return;
    }
    if(window.location.hash!='#/' && window.location.hash!='')
		{
		
		jQuery($tfmdivid).animate({opacity: "0.1"}).html('<?php $tfmload=TFM_Fade(); echo $tfmload; ?>'); jQuery.get(url, function(resp) {
	jQuery($tfmmenuid).html(jQuery(resp).find($tfmmenuid).html());
	
    	
    	jQuery($tfmdivid).html(jQuery(resp).find($tfmdivid).html());
    	
    	jQuery('.tfmtrigger').each(function(e) {
                                        var nme=jQuery(this).attr('name');
                                        if(TFM_jukebox[nme].playState==1) {
                                        jQuery(this).addClass('tfmpauser');
                                        jQuery(this).removeClass('tfmtrigger');
                                        jQuery(this).html('<?php $sh=TFM_Stop(); echo $sh; ?>'); }
     });
  jQuery($tfmdivid).animate({opacity: "1"})
		});
		};
		});
    
    jQuery(window).trigger('hashchange');
<?php } ?>
});
</script>
<?php
}

add_action('wp_footer', 'TFM_setupJukebox'); ?>