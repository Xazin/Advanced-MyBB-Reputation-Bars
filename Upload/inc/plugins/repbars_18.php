<?php

 /*     This file is part of Rep Bars

    Rep Bars is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Rep Bars is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Rep Bars.  If not, see <http://www.gnu.org/licenses/>. */

if(!defined("IN_MYBB")) {
    die("Hacking Attempt.");
}

if (defined('IN_ADMINCP')) {
	$plugins->add_hook('admin_user_menu', 'advrepbars_admin_menu');
	$plugins->add_hook('admin_user_action_handler', 'advrepbars_admin_menu_action_handler');	
}

if (isset($mybb->settings['repbar_18_postbit']) && $mybb->settings['repbar_18_postbit'] == 1) {
    $plugins->add_hook("postbit", "repbars_18_parse");
    $plugins->add_hook("postbit_pm", "repbars_18_parse");
    $plugins->add_hook("postbit_announcement", "repbars_18_parse");
    $plugins->add_hook("showthread_start", "repbars_18_loadlang");
    $plugins->add_hook("private_start", "repbars_18_loadlang");
}

if (isset($mybb->settings['repbar_18_profile']) && $mybb->settings['repbar_18_profile'] == 1) {
    $plugins->add_hook("member_profile_start", "repbars_18_loadlang");
    $plugins->add_hook("member_profile_end", "repbars_18_profile");
}

function repbars_18_info() {
    global $lang; 
    $lang->load("repbars_18");

	return array(
		'name'	        =>  htmlspecialchars($lang->repbars_18_title),
		'description'	=>  htmlspecialchars($lang->repbars_18_desc),
		'website'		=>  'https://www.mathiasm.com',
		'author'		=>  'Xazin / GodLess101',
		'authorsite'	=>  'https://www.mathiasm.com',
		'codename' 		=>  'repbars_18',
		'version'		=>  '2.0',
		"compatibility"	=>  "18*"
	);
}

function repbars_18_install() {
    global $db;

    if (!$db->table_exists("advrepbars_bars"))
    {
        $db->write_query("CREATE TABLE ".TABLE_PREFIX."advrepbars_bars (
            bid int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name varchar(255) NOT NULL,
            level int NOT NULL,
            bgcolor varchar(255),
            fontstyle varchar(255),
            dateline int(11) NOT NULL
        );");
    
        // Insert dummy data
        $repbar = array(
            "name"  => "Rookie",
            "level" => 10,
            "bgcolor"   => "linear-gradient(#e66465, #9198e5);",
            "fontstyle" => "font-weight:bold;color:#fff;text-align:center;",
            "dateline"  => TIME_NOW
        );

        $db->insert_query("advrepbars_bars", $repbar);
        
        $repbar = array(
            "name"  => "Expert",
            "level" => 20,
            "bgcolor"   => "linear-gradient(#b11489, #191169);",
            "fontstyle" => "font-weight:bold;color:#fff;text-align:center;",
            "dateline"  => TIME_NOW
        );

        $db->insert_query("advrepbars_bars", $repbar);

        $repbar = array(
            "name"  => "Master",
            "level" => 30,
            "bgcolor"   => "linear-gradient(#dd4195, #ab4615);",
            "fontstyle" => "font-weight:bold;color:#fff;text-align:center;",
            "dateline"  => TIME_NOW
        );

        $db->insert_query("advrepbars_bars", $repbar);

        update_cache();
    }
}

function repbars_18_is_installed() {
    global $db;

    if ($db->table_exists("advrepbars_bars")) {
        return true;
    } else {
        return false;
    }
}

function repbars_18_uninstall() {
    global $db;

    if ($db->table_exists("advrepbars_bars"))
    {
        $db->write_query("DROP TABLE ".TABLE_PREFIX."advrepbars_bars");
    }
}

function repbars_18_activate() {
    global $db, $lang;

	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
    $lang->load("repbars_18");
    
    find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'groupimage\']}').'#', '{$post[\'groupimage\']} {$post[\'repbars_18\']}');	
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'groupimage\']}').'#', '{$post[\'groupimage\']} {$post[\'repbars_18\']}');	
	find_replace_templatesets("member_profile", '#'.preg_quote('{$groupimage}').'#', '{$groupimage} {$memprofile[\'repbars_18\']}');

    // Insert settings
    $setting_group = array (
        'name' => 'repbars_18', 
        'title' => $db->escape_string($lang->repbars_18_title),
        'description' => $db->escape_string($lang->repbars_18_desc),
        'disporder' => $rows+3,
        'isdefault' => 0
    ); 

    $group['gid'] = $db->insert_query("settinggroups", $setting_group); // inserts new group for settings into the database. 	
    $settings = array();

    $settings[] = array(
        'name' => 'repbar_18_postbit',
        'title' => $db->escape_string($lang->repbars_18_postbit),
        'description' => $db->escape_string($lang->repbars_18_postbit_desc),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 1,
        'isdefault' => 0,
        'gid' => $group['gid']
    );

    $settings[] = array(
        'name' => 'repbar_18_profile',
        'title' => $db->escape_string($lang->repbars_18_profile),
        'description' => $db->escape_string($lang->repbars_18_profile_desc),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 1,
        'isdefault' => 0,
        'gid' => $group['gid']
    );

    foreach($settings as $array => $setting) {
        $db->insert_query("settings", $setting);
    }

    rebuild_settings();

    // Insert templates
    $templates = array();
    $templates['repbars_18_bar'] = '
    {$br_above_label}
    <div style="margin-top:3px;padding:0px;padding-right:3px;margin-right:5px;{$max_width};width:200px" title="{$lang->repbars_18_reputation}">
        <div class="rep-meter" style="border-radius:4px;padding:2px;padding-right:5px;border:1px solid #cccccc;width:100%; ">
            <div class="rep-meter-inner" style="background:{$background};width:{$rep}%;min-width:25px;text-align:left;padding-left:2px;">
                <span style="margin-left:5px;{$fontstyle}">{$post[\'reputation\']}</span>
            </div>
        </div>    
    </div>'; 

    $templates['repbars_18_legend'] = '
<html>
	<head>
		<title>Advanced Reputation Bars - Legend</title>
		{$headerinclude}
	</head>
<body>
    {$header}
    <h2>{$lang->repbars_18_reputation_bars}</h2>
	<div class="border-wrapper">
		{$advrepbars_templ}
	</div>
	{$footer}
</body>
</html>'; 

    foreach($templates as $title => $template_new){
        $template = array('title' => $db->escape_string($title), 'template' => $db->escape_string($template_new), 'sid' => '-1', 'dateline' => TIME_NOW, 'version' => '1800');
        $db->insert_query('templates', $template);
    }
}

function repbars_18_deactivate() {
    global $db; 
    require MYBB_ROOT.'/inc/adminfunctions_templates.php';
    
    // Undo template modifications
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'repbars_18\']}').'#', '',0);
    find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'repbars_18\']}').'#', '',0);
	find_replace_templatesets("member_profile", '#'.preg_quote('{$memprofile[\'repbars_18\']}').'#', '',0);

    // Remove settings
    $query = $db->simple_select('settinggroups', 'gid', 'name = "repbars_18"'); // remove settings
    $groupid = $db->fetch_field($query, 'gid');
    $db->delete_query('settings','gid = "'.$groupid.'"');
    $db->delete_query('settinggroups','gid = "'.$groupid.'"');

    rebuild_settings();

    // Remove templates
    $templates = array('repbars_18_bar', 'repbars_18_legend'); // remove templates
    foreach($templates as $template) {
        $db->delete_query('templates', "title = '{$template}'");
    }
}

function repbars_18_parse(&$post) {
    global $mybb, $templates, $repbars_18, $templates, $lang, $background, $rep, $max_width, $br_above_label;

    // Grab all Reputation Bars
    $advrepbars = $mybb->cache->read('advrepbars');

    if (empty($advrepbars))
    {
        return;
    }

    $max_width = "";
    $br_above_label = "<br />"; 

    $max = count($advrepbars)-1; // -1 due to 0 being the index counter

    if ($max <= 0) {
        // No reputation bars
        return;
    } else {
        // There is at least 1 reputation bar
        // Find out which level the user is at
        $counter = 0;
        foreach ($advrepbars as $repbar) {
            if ($counter == 0 && $post['reputation'] <= $repbar['level'])
            {
                // Lowest Reputation Bar
                $currentbar = $repbar;
                $nextbar = $advrepbars[$counter+1];
            break;
            } elseif ($counter == $max && $post['reputation'] >= $repbar['level']) {
                // Highest Reputation Bar
                $currentbar = $repbar;
            break;
            } elseif ($post['reputation'] >= $repbar['level'] && $post['reputation'] < $advrepbars[$counter+1]['level']) {
                // Somewhere in the middle
                $currentbar = $repbar;
                $nextbar = $advrepbars[$counter+1];
            break;
            }

            $counter++;
        }

        $background = $currentbar['bgcolor'];
        $fontstyle = $currentbar['fontstyle'];

        // Calculate reputation bar fill
        if (!empty($nextbar))
        {
            // This one has a next reputation level
            if ($counter == 0)
            {
                $rep = $post['reputation'];
                $rep = $rep / ($nextbar['level'] - $currentbar['level']); 
                $rep = (int)($rep * 100);
            } else {
                $rep = $post['reputation'] - $currentbar['level'];
                $rep = $rep / ($nextbar['level'] - $currentbar['level']); 
                $rep = (int)($rep * 100);
            }
        } else {
            // The reputation bar is the highest level
            $rep = 100;
        }

        eval("\$post['repbars_18'] = \"".$templates->get("repbars_18_bar")."\";");    
    }
}

function repbars_18_profile() {
    global $mybb, $templates, $memprofile, $repbars_18, $templates, $lang, $background, $rep, $max_width, $br_above_label;
    // Grab all Reputation Bars
    $advrepbars = $mybb->cache->read('advrepbars');

    if (empty($advrepbars))
    {
        return;
    }

    $max_width = "max-width:200px";
    $br_above_label = "<br />"; 

    $max = count($advrepbars)-1; // -1 due to 0 being the index counter

    if ($max <= 0) {
        // No reputation bars
        return;
    } else {
        // There is at least 1 reputation bar
        // Find out which level the user is at
        $counter = 0;
        foreach ($advrepbars as $repbar) {
            if ($counter == 0 && $memprofile['reputation'] <= $repbar['level'])
            {
                // Lowest Reputation Bar
                $currentbar = $repbar;
                $nextbar = $advrepbars[$counter+1];
            break;
            } elseif ($counter == $max && $memprofile['reputation'] >= $repbar['level']) {
                // Highest Reputation Bar
                $currentbar = $repbar;
            break;
            } elseif ($memprofile['reputation'] >= $repbar['level'] && $memprofile['reputation'] < $advrepbars[$counter+1]['level']) {
                // Somewhere in the middle
                $currentbar = $repbar;
                $nextbar = $advrepbars[$counter+1];
            break;
            }

            $counter++;
        }

        $background = $currentbar['bgcolor'];
        $fontstyle = $currentbar['fontstyle'];

        // Calculate reputation bar fill
        if (!empty($nextbar))
        {
            // This one has a next reputation level
            if ($counter == 0)
            {
                $rep = $memprofile['reputation'];
                $rep = $rep / ($nextbar['level'] - $currentbar['level']); 
                $rep = (int)($rep * 100);
            } else {
                $rep = $memprofile['reputation'] - $currentbar['level'];
                $rep = $rep / ($nextbar['level'] - $currentbar['level']); 
                $rep = (int)($rep * 100);
            }
        } else {
            // The reputation bar is the highest level
            
            $rep = 100;
        }

        $post['reputation'] = (int)$memprofile['reputation'];

        eval("\$memprofile['repbars_18'] = \"".$templates->get("repbars_18_bar")."\";");     
    }
}

/* Load Reputation Bar Language File */
function repbars_18_loadlang() {
    global $lang; 
    $lang->load("repbars_18");
}

/* Cache Functionality */
function update_cache()
{
	global $mybb, $db;

	$advrepbars = array();

	/* Grab all Reputation Bars */
	$advrepbars_query = $db->simple_select("advrepbars_bars", "*", "", array("order_by" => "level", "order_dir" => "ASC"));
	if ($db->num_rows($advrepbars_query) <= 0)
	{
		unset($advrepbars);
	} else {
		while ($advrepbar = $db->fetch_array($advrepbars_query))
		{
			array_push($advrepbars, $advrepbar);
		}
	}

	$mybb->cache->update('advrepbars', $advrepbars);
}

/* AdminCP Functions - Do not edit below here */
function advrepbars_admin_menu(&$sub_menu) {
	$sub_menu[] = ['id' => 'advrepbars', 'title' => 'Advanced Reputation Bars', 'link' => 'index.php?module=user-advrepbars'];
}

function advrepbars_admin_menu_action_handler(&$actions) {
	$actions['advrepbars'] = ['active' => 'advrepbars', 'file' => 'advrepbars.php'];
}