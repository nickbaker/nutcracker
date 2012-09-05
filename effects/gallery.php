<?php
/*
Nutcracker: RGB Effects Builder
Copyright (C) 2012  Sean Meighan
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
to rebuild gallery database:  http://meighan.net/nutcracker_sean/effects/gallery.php???INSERT_NEW_GIFS=1
*/
require_once('../conf/auth.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Nutcracker: RGB Effects Builder</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="last-modified" content=" 24 Feb 2012 09:57:45 GMT"/>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8"/>
<meta name="robots" content="index,follow"/>
<meta name="googlebot" content="noarchive"/>
<link rel="shortcut icon" href="barberpole.ico" type="image/x-icon"/> 
<meta name="description" content="RGB Sequence builder for Vixen, Light-O-Rama and Light Show Pro"/>
<meta name="keywords" content="DIY Light animation, Christmas lights, RGB Sequence builder, Vixen, Light-O-Rama or Light Show Pro"/>
<link href="../css/loginmodule.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>Welcome <?php echo $_SESSION['SESS_FIRST_NAME'];?></h1>
<?php $menu="effect-form"; require "../conf/menu.php"; ?>
<?php
require("../effects/read_file.php");
if( isset($_REQUEST['group']) && $_REQUEST['group'] !='')
{
	$group=$_REQUEST['group'];
}
if( isset($_REQUEST['effect_class']) && $_REQUEST['effect_class'] !='')
{
	$effect_class=$_REQUEST['effect_class'];
}
extract ($_POST);
/* Array
(
[submit] => Submit Form to create your target model
[number_gifs] => 100
[sort] => member_id
[effect_class_selected] => Array
(
[0] => all
[1] => bars
[2] => butterfly
)
	[number_segments] => 
)*/
echo "<pre>";
print_r($_POST);
echo "</pre>\n";
// http://meighan.net/nutcracker/effects/gallery.php?start=101?end=151?number_gifs=50?sort=member_id?effect_class_selected=dummy|garlands|meteors
// QUERY_STRING] => start=101?end=151?number_gifs=50?sort=member_id?effect_class_selected=dummy|garlands|meteors
//
//
//$tokens=explode("?model=",$REQUEST_URI);
if(isset($_POST)===false or $_POST==null ) // First time here? Called by member-index.php
{ // yes
	$tokens=explode("?",$_SERVER['QUERY_STRING']);
	$tok2=explode("=",$tokens[0]); $start = $tok2[1];
	$tok2=explode("=",$tokens[1]); $end = $tok2[1];
	$tok2=explode("=",$tokens[2]); $number_gifs = $tok2[1];
	$tok2=explode("=",$tokens[3]); $sort = $tok2[1];
	$tok2=explode("=",$tokens[4]); $effect_class_selected_array = $tok2[1];
	$tok3=explode("|",$effect_class_selected_array);
	$effect_class_selected=array();
	foreach($tok3 as $class)
	{
		$effect_class_selected[]=$class;
	}
	echo "<pre>";
	echo "start,end=$start,$end</pre>\n";
	print_r($effect_class_selected);
	echo "</pre>";
}
else
{
	extract ($_POST);
	$start=1;
	$end=$number_gifs;
}
//
//
$total_gifs=count_gallery($effect_class_selected);
echo "<h1>$total_gifs gif's in Library</h1>";
$number_gifs+=0;
if($number_gifs<=0)
	$number_gifs=1;
$loops = intval($total_gifs/$number_gifs)+1;
$effect_class_selected_buff="dummy";
foreach ($effect_class_selected as $class)
{
	$effect_class_selected_buff = $effect_class_selected_buff . "|" . $class;
}
echo "<ol>";
for ($l=1;$l<=$loops;$l++)
{
	$start = 1+($l-1)*$number_gifs;
	$end=$start +$number_gifs;
	echo "<li><a href=gallery.php?start=$start?end=$end?number_gifs=$number_gifs?sort=$sort?effect_class_selected=$effect_class_selected_buff>$start - $end</a>";
}
echo "</ol>";
$tokens=explode("?",$_SERVER['QUERY_STRING']);
$c=count($tokens);
$group=1;
$INSERT_NEW_GIFS=0;
if($c>0)
{
	//	gallery.php?INSERT_NEW_GIFS=1
	$tokens2=explode("INSERT_NEW_GIFS=",$tokens[0]);
	$c1=count($tokens2);
	if($c1>1) $INSERT_NEW_GIFS=$tokens2[1];
}
if(!isset($group) or $group<1) $group=1;
if(!isset($group_size) or $group_size<1) $group_size=40;
$pics_in_group=$group_size;
gallery($group,$pics_in_group,$INSERT_NEW_GIFS,$number_gifs,$sort,$effect_class_selected,$start,$end);

function getFilesFromDir($dir)
{
	$files = array(); 
	$n=0;
	if ($handle = opendir($dir))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." )
			{
				if(is_dir($dir.'/'.$file))
				{
					$dir2 = $dir.'/'.$file; 
					$files[] = getFilesFromDir($dir2);
				}
				else 
				{ 
					$path_parts = pathinfo($file);  // workspaces/nuelemma/MEGA_001+SEAN_d_22.dat
					$dirname   = $path_parts['dirname']; // workspaces/nuelemma
					$basename  = $path_parts['basename']; // MEGA_001+SEAN_d_22.dat
					$extension =$path_parts['extension']; // .dat
					$filename  = $path_parts['filename']; // MEGA_001+SEAN_d_22
					$cnt=count($files);
					$tokens=explode("/",$dirname);
					//	0 = workspaces
					//	1 = nuelemma or id
					//
					$pos=strpos($file,"_amp.gif");
					$th =strpos($file,"_th.gif");
					if($extension=="gif" and $pos === false and $th>1)
					{
						$files[] = $dir.'/'.$file; 
						$n++;
						//echo "<pre>$cnt $n $file</pre>\n";
					}
					} 
				} 
			} 
		closedir($handle);
	}
	return array_flat($files);
}

function array_flat($array)
{
	$tmp=array();
	foreach($array as $a)
	{
		if(is_array($a))
		{
			$tmp = array_merge($tmp, array_flat($a));
		}
		else 
		{ 
			$tmp[] = $a;
		}
		} 
	return $tmp;
}

function gallery($group,$pics_in_group,$INSERT_NEW_GIFS,$number_gifs,$sort,$effect_class_selected,$start,$end)
{
	$dir = 'workspaces'; 
	$number_gifs+=0;
	if($number_gifs<=0) $number_gifs=99999;
	/* [number_gifs] => 100
	[sort] => member_id
	[effect_class_selected] => Array
	(
	[0] => all
	[1] => bars
	[2] => butterfly
	)*/
	if($INSERT_NEW_GIFS)
	{
		$array_of_gifs = getFilesFromDir($dir); 
		insert_into_gallery($array_of_gifs);
	}
	else
	{
		$array_of_gifs=get_from_gallery($number_gifs,$sort,$effect_class_selected);
	}
	/*echo "<pre>";
	print_r($array_of_gifs);
	echo "<pre>";*/
	$cnt=count($array_of_gifs);
	$line=0;
	/*
	[939] => workspaces/nuelemma/MEGA_001+SEAN_d_22.dat
	[940] => workspaces/nuelemma/MEGA_001+SEAN_d_29.dat
	[941] => workspaces/nuelemma/MEGA_001+SEAN_d_25.dat
	[942] => workspaces/nuelemma/MEGA_001+SEAN_d_24.dat
	[943] => workspaces/28/SGMEG24F+_d_15.dat
	[944] => workspaces/28/SGMEG24F+_d_9.dat
	[945] => workspaces/28/SGMEG24F+_d_19.dat
	[946] => workspaces/28/SGMEG24F+_d_23.dat
	[947] => workspaces/28/SGMEG24F+_d_6.dat
	*/
	$arr=get_max_date_gallery();
	$max_date=$arr[0];
	$cnt=$arr[1];
	// Usage find all gif files under the workspaces subdirectory 
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<h1>Gallery of Effects by all users of Nutcracker. $cnt User Effects gathered on $max_date</h1>";
	$effect_class="xx";
	$pics=0;
	$pics_per_row=6;
	$max_rows=($pics_in_group/$pics_per_row);
	$last_pic=$pics_per_row-1;
	$group_to_show=$group;
	$pics_col=0; $pics_row=$start_pic=$end_pic=0;
	$pic_group=0;
	$array_effect_classes=get_effect_class_gallery();
	//sort($array_effect_classes);
	$username='';
	?>
	<form action="<?php echo "gallery-exec.php"; ?>" method="post">
	<input type="hidden" name="username" value="<?php echo "$username"; ?>"/>
	<?php
	/*echo "FILTER:&nbsp;<INPUT TYPE=\"RADIO\" NAME=\"effect_class_array\" VALUE=\"All\" CHECKED >Any effect class";
	foreach($array_effect_classes as $effect_cl)
	{
		echo "<INPUT TYPE=\"RADIO\" NAME=\"effect_class_array\" VALUE=\"$effect_cl\"  >$effect_cl";
	}
	*/
	echo "<table border=\"1\">\n";
	foreach ($array_of_gifs as $i => $array2)
	{
		$file=$array2[0];
		$effect_class=$array2[1];
		$path_parts = pathinfo($file);  // workspaces/nuelemma/MEGA_001+SEAN_d_22.dat
		$dirname   = $path_parts ['dirname']; // workspaces/nuelemma
		$basename  = $path_parts ['basename']; // MEGA_001+SEAN_d_22.dat
		$extension =$path_parts  ['extension']; // .dat
		$filename  = $path_parts ['filename']; // MEGA_001+SEAN_d_22
		$tokens=explode("/",$dirname);
		//	0 = workspaces
		//	1 = nuelemma or id
		//
		$member_id=$tokens[1];
		$pos=strpos($file,"_amp.gif");
		$th=strpos($file,"_th.gif");	
		$checked="";	
		//echo "<pre>$file member=$member_id pos=$pos, th=$th</pre>\n";
		if($extension=="gif" and $pos === false and $th>1 and $member_id>=1 and $member_id < 99999999)
		{
			$tok2=explode("+",$filename);
			$target_model=$tok2[0];
			$effect_name=$tok2[1];   // AA+SPIRAL_th.gif"
			$tok3=explode("_th",$tok2[1]);
			$effect_name=$tok3[0];
			$username = get_username($member_id);
			$pics++;
			if($pics>=$start and $pics<$end)
			{
				if($pics%$pics_per_row==1) // check if we should advance row
				{
					echo "</tr><tr>";
					$pics_row++;
				}
				//	if($pic_group==$group_to_show or $group_to_show==0) // should we show gif?
				{
					echo "<td><b>$effect_class</b>&nbsp;&nbsp;File#$pics. &nbsp;&nbsp;Select:<input type=\"checkbox\" name=\"fullpath_array[$i]\" value=\"$file\"  $checked /> ";
					echo "<br/>Your name for this effect:<input type=\"text\" name=\"user_effect_name[$i]\" size=\"25\" value=\"\">";
					echo "<br/>Your Description:<input type=\"text\" name=\"desc[$i]\" size=\"25\" >";
					echo "<br/>$file<br /><img src=\"$file\"/></a></td>\n";
				}
			}
			$end_pic=$pics;
		}
	}
	if($pics%$pics_per_row!=1)
	{
		$pic_group++;
		$start = $pics_in_group * ($pic_group-1) + 1;
		$end=$start + $pics_in_group -1;
		echo "<tr><td><a href=gallery.php?group=$pic_group>($start - $end)</a></td></tr>";
		$start_pic=$pics;
	}
	//echo "<table border=1>";
	?>
	</table>
	<input type="submit" name="submit" value="Submit Form to copy your checked effects"  class="button" />
	</form>
	<?php
}

function get_eff_class($username,$effect_name)
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	// effect_class	username	effect_name	effect_desc	created	last_upd
	$query = "select effect_class from effects_user_hdr where username='$username' and effect_name='$effect_name'";
	$result=mysql_query($query) or die ("Error on $query");
	while ($row = mysql_fetch_assoc($result))
	{
		extract($row);
	}
	mysql_close();
	return ($effect_class);
}

function insert_into_gallery($array_of_gifs)
{
	//
	/*CREATE TABLE `seqbuilder`.`gallery` (`fullpath` VARCHAR(100) NOT NULL, `effect_class` VARCHAR(25) NULL, `username` VARCHAR(25) NULL, `effect_name` VARCHAR(25) NULL, PRIMARY KEY (`fullpath`)) ENGINE = MyISAM COMMENT = 'Gallery of thumbnail gifs'*/
	//
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	// effect_class	username	effect_name	effect_desc	created	last_upd
	$query = "delete from  gallery where 1=1";
	$result=mysql_query($query) or die ("Error on $query");
	$line=0;
	foreach($array_of_gifs as $fullpath)
	{
		$line++;
		// workspaces/2/AA+LAYER2_th.gif
		$tok=explode("/",$fullpath);
		$member_id=$tok[1];
		$username=get_username($member_id);
		$effect_class="spiral";
		$tok2=explode("+",$tok[2]);
		$tok3=explode("_th.",$tok2[1]);
		$effect_name=$tok3[0];
		$ar=get_effect_user_hdr($username,$effect_name);
		$effect_class = $ar[0]['effect_class'];
		$query = "replace into gallery (fullpath,effect_class,username,effect_name,linenumber,member_id) values 
		('$fullpath','$effect_class','$username','$effect_name',$line,$member_id)";
		echo "<pre>$line $fullpath.";
		/*print_r($ar);
		foreach($ar as $arr)
		{
			print_r($arr);
		}
		*/
		echo "</pre>\n";
		$result=mysql_query($query);
		if (mysql_errno() == 1062)
		{
			echo "<pre>Got duplicate error on $query</pre>\n";
		}
	}
}

function get_from_gallery($number_gifs,$sort,$effect_class_selected)
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	// effect_class	username	effect_name	effect_desc	created	last_upd
	/*E=RADIO NAME="sort" VALUE="member_id"   CHECKED  >user id, effect name<br/>
	<INPUT TYPE=RADIO NAME="sort" VALUE=effect_class"          >effect_class,user_id,effect_name<br/>
	<INPUT TYPE=RADIO NAME="sort" VALUE="effect_name"          >effect_name, user_id<P>*/
	if($sort=="member_id") $sort_string="member_id,effect_class, fullpath";
	if($sort=="effect_class") $sort_string="effect_class,member_id, fullpath";
	if($sort=="effect_name") $sort_string="effect_name,member_id, fullpath";
	$c=count($effect_class_selected);
	$effect_string="'dummy'";
	foreach($effect_class_selected as $effect_class)
	{
		$effect_string=$effect_string . ",'" . $effect_class . "'";
	}
	if($effect_class_selected[0]=="all")
		$query = "select * from gallery order by $sort_string";
	else
	$query = "select * from gallery 
	where effect_class in ($effect_string) 
	order by $sort_string";
	echo "<pre>get_from_gallery: $query</pre>\n";
	$result=mysql_query($query) or die ("Error on $query");
	$array_of_gifs=array();
	while ($row = mysql_fetch_assoc($result))
	{
		extract($row);
		$array_of_gifs[$linenumber]=array($fullpath,$effect_class);
	}
	mysql_close();
	return ($array_of_gifs);
}

function get_effect_class_gallery()
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	// effect_class	username	effect_name	effect_desc	created	last_upd
	$query = "SELECT effect_class,count(*) cnt
	from gallery
	group by effect_class
	order by effect_class";
	$result=mysql_query($query) or die ("Error on $query");
	while ($row = mysql_fetch_assoc($result))
	{
		extract($row);
		$array_effect_classes[]=$effect_class;
	}
	return $array_effect_classes;
}

function get_max_date_gallery()
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD); 
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	// effect_class	username	effect_name	effect_desc	created	last_upd
	$query = "SELECT max(created) created, count(*) cnt from gallery";
	$result=mysql_query($query);
	$row = mysql_fetch_assoc($result);
	extract ($row);
	$arr=array($created,$cnt);
	return $arr;
}

function count_gallery($effect_class_selected)
{
	//Include database connection details
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	//
	$c=count($effect_class_selected);
	$effect_string="'dummy'";
	foreach($effect_class_selected as $effect_class)
	{
		$effect_string=$effect_string . ",'" . $effect_class . "'";
	}
	echo "<pre>";
	print_r($effect_class_selected);
	echo "</pre>";
	if (in_array("all", $effect_class_selected))
	{
		$query = "select count(*) cnt from gallery ";
	}
	else
	$query = "select count(*) cnt from gallery 	where effect_class in ($effect_string)"; 
	echo "<pre>count_gallery: $query</pre>\n";
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $query . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	$cnt=0;
	while ($row = mysql_fetch_assoc($result))
	{
		extract($row);
	}
	return $cnt;
}