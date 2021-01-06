<?php
require('wp-blog-header.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

$mysqli = new mysqli("190.60.174.20", "tradem", "fabiano", "guiatradem_com_-_2010");
// local
//$mysqli = new mysqli("localhost", "root", "globaljp", "tradem_et");
if ($mysqli->connect_errno) {
	echo "dentro";
    return false;
}

//$NotasSQL = $mysqli->query("SELECT * from nuke_stories WHERE sid>975 and sid<980");

/*$NotasSQL = $mysqli->query("SELECT * from nuke_stories 
WHERE sid>=694 and sid<720");*/

$NotasSQL = $mysqli->query("SELECT * from nuke_stories 
WHERE sid>=701 and sid<710");


 function getImgSrcFromHTML($html){
  $doc = new DOMDocument();
  $doc->loadHTML($html);
  $imagepPaths = array();
  $imageTags = $doc->getElementsByTagName('img');
  foreach ($imageTags as $tag) {
    $imagePaths[] = $tag->getAttribute('src');
  }
  if(!empty($imagePaths)) {
  	return array_map('trim', $imagePaths);
    //return $imagePaths;
  } else {
    return false;
  }
 }


/*
while ($nota = $NotasSQL->fetch_object()) {
	echo $nota->bodytext;
}*/

//$nota = $NotasSQL->fetch_object();
/*echo "<pre>";
	var_dump($nota);
echo "</pre>";
exit();*/

/*
SELECT * from nuke_stories 
inner join nuke_clientes_stats ON (nuke_stories.sid=nuke_clientes_stats.id_articulo AND sitio='espacio')
WHERE sid>975 and sid<980

*/

//$datosCliente = $clienteByNota->fetch_assoc();
//$valorIns = array();
while ($nota = $NotasSQL->fetch_object()) {
//echo $nota->bodytext;
	/*$src = null;
	$doc = new DOMDocument("1.0", "utf-8");
	$doc->loadHTML(stripslashes($nota->bodytext));
	$doc->formatOutput = true;
	$doc->preserveWhitespace = false;
	$xpath = new DOMXPath($doc);
	$src = $xpath->evaluate("string(//img/@src)");
	echo $src."<br>";

	// Buscamos del bodytext la primer imagen
	$imagenUno=$doc->getElementsByTagName('img')->item(0);
	$imagenNodo = $imagenUno->parentNode;
	// Eliminamos
	$imagenNodo->removeChild($imagenUno); 

	$texto = $doc->saveHTML();*/
	//$texto = html_entity_decode($texto,ENT_QUOTES,"UTF-8");
	//$texto = mb_convert_encoding($texto, 'HTML-ENTITIES', 'UTF-8');



	$categoria = array();
	switch ($nota->topic) {
		case 6:
			$categoria[] = 13;
			break;
		case 7:
			$categoria[] = 14;
			break;
		case 8:
			$categoria[] = 10;
			break;
		case 9:
			$categoria[] = 3;
			break;	
		case 10:
			$categoria[] = 12;
			break;	
		case 11:
			$categoria[] = 7;
			break;
		case 14:
			$categoria[] = 9;
			break;
		default:
			$categoria[] = 1;
			break;
	}

		/*echo "<pre>";
	var_dump( utf8_decode($texto) );
echo "</pre>";*/
//echo utf8_encode($texto)."<br>";
	$valorIns = array(
		'post_title'   => utf8_encode($nota->title),
		'post_content'   => stripslashes(utf8_encode($nota->hometext))."<!--more-->".stripslashes( utf8_encode($nota->bodytext)),
		'post_name'	=> $nota->slug,
		'post_excerpt' => utf8_encode($nota->descripcion),
		'post_status' => 'publish',
		'post_category' => $categoria,
		'post_author' => 1,
		'post_date'	=> $nota->time,
		'comment_status' => 'closed',

		);
/*
echo "<pre>";
var_dump($valorIns);
echo "</pre>";*/


/*$Address = "www.xxx.com/" . $file;
$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_URL, $file);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$url = curl_exec($ch);
curl_close($ch);
$location = "/wordpress/wp-content/uploads/2013/a.jpg"; 
file_put_contents($location, file_get_contents($url));
if(! function_exists('wp_upload_dir'))
                {
                    define('WP_USE_THEMES',false);
                    require 'wordpress/wp-load.php';
                }
    $upload_dir = wp_upload_dir(); 
    echo $upload_dir['path'];
    echo $upload_dir['url'];
    echo $upload_dir['subdir']; */



$postID = wp_insert_post( $valorIns, $wp_error );
add_post_meta( $postID, 'views', $nota->counter, true );
add_post_meta( $postID, 'slug', $nota->slug, true );
add_post_meta( $postID, 'idanterior', $nota->sid, true );
add_post_meta( $postID, 'cliente', $nota->cliente, true );

//wp_set_post_tags( $postID, utf8_encode($nota->keywords), true );

$src = getImgSrcFromHTML($nota->hometext);
/*echo "<pre>";
echo stripcslashes($src[0]);
echo "</pre>";*/

if(filter_var(stripslashes(str_replace('"', "", $src[0]) ), FILTER_VALIDATE_URL) === FALSE)
{
        //echo stripcslashes($src[0])." "."Not valid";
        $imgF = "http://www.trademdesign.com".stripslashes(str_replace('"', "", $src[0]) );
}else{
        //echo stripcslashes($src[0])." "."VALID";
        $imgF = stripslashes(str_replace('"', "", $src[0]) );
}


echo $imgF."<br>";
$image = media_sideload_image($imgF, $postID, utf8_encode($nota->title));
$attachments = get_posts(array('numberposts' => '1', 'post_parent' => $postID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC'));


/*$url = "http://wordpress.org/about/images/logos/wordpress-logo-stacked-rgb.png";
$post_id = 1;
$desc = "The WordPress Logo";
echo $postID;
$image = media_sideload_image($url, $postID, $desc);*/


if(sizeof($attachments) > 0){
    set_post_thumbnail($postID, $attachments[0]->ID);
}

	/*$postID = wp_insert_post( $valorIns, $wp_error );
	add_post_meta( $postID, 'views', $nota->counter, true );
	wp_set_post_tags( $postID, $nota->keywords, true );*/


	//echo $nota->sid;

	unset($postID,$valorIns);
}


$NotasSQL->close();
/*echo "<pre>";
	var_dump($clienteByNota);
echo "</pre>";*/

?>