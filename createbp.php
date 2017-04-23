<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


require_once( 'simplexml.php' );
global $wpdb;





if (!file_exists(wp_upload_dir()['basedir'].'/best-price')) {
    wp_mkdir_p(wp_upload_dir()['basedir'].'/best-price');
}


if (!file_exists(wp_upload_dir()['basedir'].'/best-price/bp.xml')) {
    touch(wp_upload_dir()['basedir'].'/best-price/bp.xml');
}


if (file_exists(wp_upload_dir()['basedir'].'/best-price/bp.xml')) {
    $xmlFile = wp_upload_dir()['basedir'].'/best-price/bp.xml';
} else {
    echo "Could not create file.";
}

$xml = new feed_SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><webstore/>');
$now = date('Y-n-j G:i');
$xml->addChild('date', "$now");
$products = $xml->addChild('products');

$query = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "posts	WHERE `post_type` LIKE 'product' AND `post_status` LIKE 'publish'",0);

$result = $wpdb->get_results($query);

	$instockavailability = get_option('instockavailability');
	$avaibilities =array("Σε απόθεμα", "Διαθέσιμο σε 1-3 ημέρες", "Διαθέσιμο σε 4-7 μέρες", "Διαθέσιμο σε 7+ μέρες");
	$availabilityST = $avaibilities[$instockavailability];
	$ifoutofstock = get_option('ifoutofstock');	
	$featureslist= get_option('features');
	
global $woocommerce;
$attribute_taxonomies = wc_get_attribute_taxonomies();


	
foreach ($result as $index => $prod) {

	$sql = $wpdb->prepare( "SELECT *
	FROM " . $wpdb->prefix . "postmeta
	WHERE `post_id` =" . $prod->ID . " AND `meta_key` LIKE '_stock_status';",0);


    $stockstatus = $wpdb->get_results($sql);
	
	if ((strcmp($stockstatus[0]->meta_value, "outofstock") == 0)& ($ifoutofstock==1) ){
        continue;
    }
	
    $sts = $stockstatus[0]->meta_value;

	
    /*
     * To Skip product and not add it to the feed add Special field 'onfeed' as 'no' on the product
     */
    $sql =$wpdb->prepare(  "SELECT *
		FROM " . $wpdb->prefix . "postmeta
		WHERE `post_id` =" . $prod->ID . " AND `meta_key` LIKE 'onfeed';",0);

    $onfeed = $wpdb->get_results($sql);
    if (strcmp($onfeed[0]->meta_value, "no") == 0) {
        continue;
    }

    $sql = $wpdb->prepare( "SELECT * 	FROM " . $wpdb->prefix . "postmeta WHERE post_id =" . $prod->ID . " AND meta_key LIKE '_price';",0);
    $meta = $wpdb->get_results($sql);




        $imagelink = $wpdb->prepare( "SELECT `meta_value`	FROM " . $wpdb->prefix . "postmeta	WHERE `post_id` =" . $prod->ID . " AND meta_key LIKE '_thumbnail_id' ",0);
        $imagelinkres = $wpdb->get_results($imagelink);
        $images =$wpdb->prepare(  "SELECT `guid` FROM " . $wpdb->prefix . "posts	WHERE `id` =" . $imagelinkres[0]->meta_value . " AND post_type LIKE 'attachment' AND `post_mime_type` LIKE 'image/%%';",0);
        $image = $wpdb->get_results($images);


    $skusql =$wpdb->prepare(  "SELECT *
	FROM " . $wpdb->prefix . "postmeta
	WHERE `post_id` =" . $prod->ID . " AND `meta_key` LIKE '_sku';",0);

    $skus = $wpdb->get_results($skusql);


    $cat = $wpdb->prepare(  "SELECT *
	FROM " . $wpdb->prefix . "term_relationships as tr , " . $wpdb->prefix . "term_taxonomy  as tt
	WHERE `object_id` =" . $prod->ID . " and tr.term_taxonomy_id =tt.term_taxonomy_id and tt.taxonomy like 'product_cat'  ",0);


    $flag = 0;
    $categories = $wpdb->get_results($cat);

    $sql =$wpdb->prepare(   "SELECT *
    FROM " . $wpdb->prefix . "postmeta
    WHERE `post_id` =" . $prod->ID . " AND `meta_key` LIKE 'mpn';",0);

    $mpn = $wpdb->get_results($sql);


    $sql = $wpdb->prepare( "SELECT *
        FROM " . $wpdb->prefix . "postmeta
        WHERE `post_id` =" . $prod->ID . " AND `meta_key` LIKE '_product_attributes';",0);


    $attr = $wpdb->get_results($sql);


    if (strcmp($attr[0]->meta_value, "a:0:{}")) {

        $sizestring = "";
        $childs = $wpdb->prepare( "SELECT `id`	FROM " . $wpdb->prefix . "posts	WHERE `post_parent` =" . $prod->ID . " AND post_type LIKE 'product_variation' ;",0);

        $childs = $wpdb->get_results($childs);
        foreach ($childs as $child) {
            $sstock =$wpdb->prepare(  "SELECT *  FROM " . $wpdb->prefix . "postmeta WHERE `post_id` = " . $child->id . " AND `meta_key` LIKE '_stock';",0);
            $sstock = $wpdb->get_results($sstock);
            if ($sstock[0]->meta_value > 0 || $sstock[0]->meta_value==NULL ) {
                $sizes = $wpdb->prepare( "SELECT *  FROM " . $wpdb->prefix . "postmeta WHERE `post_id` = " . $child->id . " AND `meta_key` LIKE 'attribute_pa_size';",0);
                $attrsize = $wpdb->get_results($sizes);
                $sizename =$wpdb->prepare(  "SELECT *  FROM " . $wpdb->prefix . "terms WHERE `slug` LIKE '" . $attrsize[0]->meta_value . "' ;",0);
                $sizename = $wpdb->get_results($sizename);
                $sizestring = $sizename[0]->name . ", " . $sizestring;
            }
        }
    }
	$man=null;
	$tempid = $prod->ID;
    if (strcmp($attr[0]->meta_value, "a:0:{}")) {        

        $manufacturer = $wpdb->prepare(  "SELECT * FROM " . $wpdb->prefix . "term_relationships as tr, " . $wpdb->prefix . "term_taxonomy as tt , " . $wpdb->prefix . "terms as t WHERE tr.object_id = " . $tempid . " and tt.term_taxonomy_id= tr.term_taxonomy_id and tt.term_id=t.term_id and tt.taxonomy like 'pa_manufacturer'  ;",0);
        $man = $wpdb->get_results($manufacturer);
    }
	
	if(count($man) == 0)
    {	
	$manufacturer = $wpdb->prepare(  "SELECT * FROM " . $wpdb->prefix . "term_relationships as tr, " . $wpdb->prefix . "term_taxonomy as tt , " . $wpdb->prefix . "terms as t WHERE tr.object_id = " . $tempid . " and tt.term_taxonomy_id= tr.term_taxonomy_id and tt.term_id=t.term_id and tt.taxonomy like 'product_brand'  ;",0);
	$man = $wpdb->get_results($manufacturer);
	}

	$color = $wpdb->prepare(  "SELECT * FROM " . $wpdb->prefix . "term_relationships as tr, " . $wpdb->prefix . "term_taxonomy as tt , " . $wpdb->prefix . "terms as t WHERE tr.object_id = " . $tempid . " and tt.term_taxonomy_id= tr.term_taxonomy_id and tt.term_id=t.term_id and tt.taxonomy like 'pa_color'  ;",0);
	$colorRes = $wpdb->get_results($color);
	
    $last_key = end(array_keys($categories));
    foreach ($categories as $index2 => $cat) {

        if ($index2 != $last_key) {
            continue;
        }


        if ($cat->taxonomy == "product_cat") {


            $product = $products->addChild('product');
            $term_que =$wpdb->prepare(  "SELECT *
			FROM " . $wpdb->prefix . "terms
			WHERE `term_id` = " . $cat->term_id . "
			;",0);


            $terms = $wpdb->get_results($term_que);
            $cat_par_id = $cat->parent;
            $category_path = $terms[0]->name;


            while ($cat_par_id != 0) {
                $term_que = $wpdb->prepare(  "SELECT *
			FROM " . $wpdb->prefix . "terms
			WHERE `term_id` = " . $cat_par_id . "
			;",0);
                $terms = $wpdb->get_results($term_que);
                $category_path = $terms[0]->name . " -> " . $category_path;

                $cat_par = $wpdb->prepare(  "SELECT *
	FROM " . $wpdb->prefix . "term_taxonomy  as tt
	WHERE   tt.term_id =" . $cat_par_id . " and tt.taxonomy like 'product_cat'  ",0);



                $par_cat2 = $wpdb->get_results($cat_par);

                $cat_par_id = $par_cat2[0]->parent;
				
				
            }

            $title = str_replace("'", " ", $prod->post_title);
            $title = str_replace("&", "+", $prod->post_title);
            $title = strip_tags($title);


            $sku = $skus[0];

            if ($sku != "") {

                $id = addslashes($sku->meta_value);
            } else {
                $id = addslashes($prod->ID);
            }

            $cat_id = addslashes($cat->term_taxonomy_id);
            $price = addslashes($meta[0]->meta_value);
            $desc = str_replace("'", " ", $prod->post_excerpt);
            $desc = str_replace("&", "+", $desc);
            $desc = strip_tags($desc);



            $product->mpn = NULL;
            $product->mpn->addCData($sku->meta_value);
           

            $product->addChild('productId', $prod->ID);
            $product->name = NULL;
            $product->name->addCData($title);
            $product->link = NULL;
            $product->link->addCData(get_permalink($prod->ID));


            $product->image = NULL;
            $product->image->addCData($image[0]->guid);




            $product->categoryPath = NULL;
            $product->categoryPath->addCData($category_path);


            $product->addChild('categoryID', $cat_id);
            $product->addChild('price', $price);

            $product->description = NULL;
            $product->description->addCData($desc);







            if (strcmp($sts, "instock") == 0) {
                $product->addChild('instock', "Y");
                $product->addChild('availability', $availabilityST);
            } else {
                $sql = $wpdb->prepare(  "SELECT *
		FROM " . $wpdb->prefix . "postmeta
		WHERE `post_id` =" . $prod->ID . " AND `meta_key` LIKE '_backorders';",0);

                $backorder = $wpdb->get_results($sql);
                if (strcmp($backorder[0]->meta_value, "notify") == 0) {
                    $product->addChild('instock', "N");
                    $product->addChild('availability', "Διαθέσιμο κατόπιν παραγγελίας");
                }else if (strcmp($backorder[0]->meta_value, "yes") == 0) {
                    $product->addChild('instock', "Y"); 
					$product->addChild('availability', $availabilityST);
                } 	else {
                    $product->addChild('instock', "N");
					$product->addChild('availability', "Μη διαθέσιμο");
                }
            }




            if (strcmp($attr[0]->meta_value, "a:0:{}")) {

                $product->addChild('size', substr($sizestring, 0, -2));
            }


            
            $product->manufacturer = NULL;
            $product->manufacturer->addCData($man[0]->name);
			
			$product->color = NULL;
            $product->color->addCData($colorRes[0]->name);
          
			
			$features = $product->addChild('features');		
			
			if ($featureslist != null)
			foreach($attribute_taxonomies as $tax) {
				if( in_array(  $tax->attribute_id,$featureslist ) ) {
				 $attname=$tax->attribute_name;
				 $featurevalue =$wpdb->prepare(  "SELECT * FROM " . $wpdb->prefix . "term_relationships as tr, " . $wpdb->prefix . "term_taxonomy as tt , " . $wpdb->prefix . "terms as t WHERE tr.object_id = " . $tempid . " and tt.term_taxonomy_id= tr.term_taxonomy_id and tt.term_id=t.term_id and tt.taxonomy like 'pa_".$attname."'  ;" ,0);
				 $featurev = $wpdb->get_results($featurevalue);		
				
				if( $featurev){
				 $features->$attname=NULL;
				 $features->$attname->addCData($featurev[0]->name);
				 }
			
					} 	
			}
			
			
			
        }
        $flag = 1;
    }
}




echo '</br>SUCCESSFUL CREATION OF Best Price XML</br>';
$xml->saveXML($xmlFile);
echo 'The file is located at <a href="' . wp_upload_dir()['baseurl'].'/best-price/bp.xml" target="_blank">' .wp_upload_dir()['baseurl'].'/best-price/bp.xml</a>'
?>
