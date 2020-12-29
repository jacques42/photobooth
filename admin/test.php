<?php
session_start();

require_once('../lib/config.php');
require_once('../lib/configsetup.inc.php');

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
	<meta name="msapplication-TileColor" content="<?=$config['colors']['primary']?>">
	<meta name="theme-color" content="<?=$config['colors']['primary']?>">

	<!-- Favicon + Android/iPhone Icons -->
	<link rel="apple-touch-icon" sizes="180x180" href="../resources/img/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../resources/img/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../resources/img/favicon-16x16.png">
	<link rel="manifest" href="../resources/img/site.webmanifest">
	<link rel="mask-icon" href="../resources/img/safari-pinned-tab.svg" color="#5bbad5">
<!--
	<link rel="stylesheet" type="text/css" href="../node_modules/normalize.css/normalize.css" />
	<link rel="stylesheet" type="text/css" href="../node_modules/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="../resources/css/admin.css" />
	<?php if ($config['rounded_corners']): ?>
	<link rel="stylesheet" type="text/css" href="../resources/css/rounded.css" />
	<?php endif; ?>
-->
<style>

/* The side navigation menu */
.sidebar {
  margin: 0;
  padding: 0;
  width: 200px;
  background-color: #f1f1f1;
  position: fixed;
  height: 100%;
  overflow: auto;
}

/* Sidebar links */
 a {
  display: block;
  color: black;
  padding: 16px;
  text-decoration: none;
}

/* Active/current link */
.active {
  background-color: #4CAF50;
  color: white;
}

/* Links on mouse-over */
a:hover:not(.active) {
  background-color: #555;
  color: white;
}

ul.navlist {
  list-style-position: inside;
  list-style-type: none;
  margin: 0;
  padding: 0;
}


/* Page content. The value of the margin-left property should match the value of the sidebar's width property */
div.content {
  margin-left: 200px;
  padding: 1px 16px;
  overflow: auto;
  height: 100%;
}

/* On screens that are less than 700px wide, make the sidebar into a topbar */
@media screen and (max-width: 700px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
  }
  .sidebar a {float: left;}
  div.content {margin-left: 0;}
}

div.setting_section {
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.7);
  display: block;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  padding: 15px 5px;
}

div.setting {
  float: left;
  width: 33.3%;
  margin-bottom: 16px;
  padding: 0 8px;
  border: 1px solid rgba(0,0,0,0.2);
  background-color: rgba(0,0,0,0.1);
}

/* Clear floats */
.setting_section::after {
  content: "";
  clear: both;
  display: table;
}

.setting_heading {
  padding: 10px 0px;
  }

@media screen and (max-width: 1400px) {
  div.setting {
    float: left;
    width: 50%;
  }
}

@media screen and (max-width: 1000px) {
  div.setting {
    width: 100%;
    display: block;
  }
}

/* On screens that are less than 400px, display the bar vertically, instead of horizontally */
@media screen and (max-width: 400px) {
  .sidebar a {
    text-align: center;
    float: none;
  }
}
</style>
</head>
<body>
<div class="sidebar">

<?php
	function html_src_indent($num)
	{
		 echo "\n".str_repeat("\t",$num);
	}

	$indent = 3;

	html_src_indent($indent++);
	echo '<ul class="navlist" id="navlist">';
	
	foreach($configsetup as $section => $fields)
	{
		html_src_indent($indent);
		echo '<li><a class="navlistelement" href="#'.$section.'" id="nav-'.$section.'"><span data-i18n="'.$section.'">'.$section.'</span></a></li>';

	}

	html_src_indent(--$indent);
	echo '</ul>';

	?>
</div>
<!-- Page content -->
<div class="content" id="contentpage">
<?php

	foreach($configsetup as $section => $fields)
	{
		html_src_indent($indent++);
		echo '<div class="setting_section" id="'.$section.'">';
		html_src_indent($indent);
		echo '<h1 class="setting_heading"> '.$section.' </h1>';

		$col = 0;
		foreach($fields as $key => $setting)
		{
			if ($key == 'platform' || $key == 'view') {
				continue;
			};

			html_src_indent($indent++);
			echo '<div class="setting" name="'.$key.'">';

			switch($setting['type']) {
				case 'input':
					echo '<label data-i18n="'.$section.'_'.$key.'">'.$section.'_'.$key.'</label><input type="text" name="'.$setting['name'].'" value="'.$setting['value'].'" placeholder="'.$setting['placeholder'].'"/>';
					break;
				case 'range':
					echo '<label data-i18n="'.$section.'_'.$key.'">'.$section.'_'.$key.'</label></br>';
					html_src_indent(++$indent);
					echo '<div class="'.$setting['name'].'"><span>'.$setting['value'].'</span> <span data-i18n="'.$setting['unit'].'"</span></div>';
					html_src_indent($indent);
					echo '<input type="range" name="'.$setting['name'].'" class="slider" value="'.$setting['value'].'" min="'.$setting['range_min'].'" max="'.$setting['range_max'].'" step="'.$setting['range_step'].'" placeholder="'.$setting['placeholder'].'"/>';
					html_src_indent($indent--);
					echo '<script> window.addEventListener("load", function() { var slider = document.querySelector("input[name='.$setting['name'].']"); slider.addEventListener("change", function() { document.querySelector(".'.$setting['name'].' span").innerHTML = this.value; }); }); </script>';
					break;
				case 'color':
					echo '<input type="color" name="'.$setting['name'].'" value="'.$setting['value'].'" placeholder="'.$setting['placeholder'].'"/> <label data-i18n="'.$section.'_'.$key.'"> '.$section.'_'.$key.'</label>';
					break;
				case 'hidden':
					echo '<input type="hidden" name="'.$setting['name'].'" value="'.$setting['value'].'"/>';
					break;
				case 'checkbox':
					$checked = '';
					if ($setting['value'] == 'true') {
						$checked = ' checked="checked"';
					}
					echo '<span data-i18n="'.$key.'">'.$key.'</span><br><label class="switch"><input type="checkbox" '.$checked.' name="'.$setting['name'].'" value="true"/><span class="toggle"></span></label>';
					break;
				case 'multi-select':
				case 'select':
					$selectTag = '<select name="'.$setting['name'] . ($setting['type'] === 'multi-select' ? '[]' : '') . '"' . ($setting['type'] === 'multi-select' ? ' multiple="multiple" size="10"' : '') . '>';
					echo '<label data-i18n="'.$section.'_'.$key.'">'.$section.'_'.$key.'</label>' . $selectTag;
						foreach($setting['options'] as $val => $option) {
							$selected = '';
							if ((is_array($setting['value']) && in_array($val, $setting['value'])) || ($val === $setting['value'])) {
								$selected = ' selected="selected"';
							}
							echo '<option '.$selected.' value="'.$val.'">'.$option.'</option>';
						}
					echo '</select>';
					break;
			}
			echo '</div>';
			--$indent;
		}
		html_src_indent(--$indent);
		echo '</div>';
	}
?>
</div>
        <script src="../node_modules/whatwg-fetch/dist/fetch.umd.js"></script>
        <script type="text/javascript" src="../api/config.php"></script>
        <script type="text/javascript" src="../node_modules/jquery/dist/jquery.min.js"></script>
        <script type="text/javascript" src="waypoints/lib/jquery.waypoints.min.js"></script>
        <script type="text/javascript" src="../resources/js/theme.js"></script>
        <script type="text/javascript" src="../resources/js/admin.js"></script>
        <script src="../node_modules/@andreasremdt/simple-translator/dist/umd/translator.min.js"></script>
        <script type="text/javascript" src="../resources/js/i18n-sub.js"></script>
	<script type="text/javascript">

		$('#nav-general').addClass("active");

<?php
	foreach($configsetup as $section => $fields)
	{
		html_src_indent($indent);

		echo '$(\'#'.$section.'\').waypoint({
		     handler: function() {
		     		     console.log(\'waypoint triggered '.$section.'\');
		     		     $(".navlistelement").removeClass("active");
				     $(\'#nav-'.$section.'\').addClass("active");
		     },
		     offset: "-5px",
		     continuous: false
		     });';
		     
	       	echo '$(\'#nav-'.$section.'\').click(function(e) {
								e.preventDefault();
	       	      						console.log(\'nav clicked '.$section.'\');
								var target = $(this).attr(\'href\');
								$(\'html, body\').animate({
								       scrollTop: ($(target).offset().top)
								            }, 1000, () => {
		    					 	  	       $(".navlistelement").removeClass("active");
									       $(\'#nav-'.$section.'\').addClass("active");
									       console.log("callback triggered"); });
								return false;
		      						}
							);';

	}

?>	
	</script>
</body>
</html>