<?php

$templatelist = "repbars_18_bar,repbars_18_legend";

define('IN_MYBB', 1); require "./global.php";

$lang->load("repbars_18");

add_breadcrumb($lang->repbars_18_reputation_bars_legend, "advrepbars.php");

/* We only need one page for the legend */
/* Generate the repbars */

$advrepbars = $mybb->cache->read('advrepbars');

$advrepbars_templ = '';
if (!empty($advrepbars))
{
    foreach ($advrepbars as $advrepbar)
    {
        $post['reputation'] = $mybb->user['reputation'] == 0 ? 40 : $mybb->user['reputation'];
        $rep = $post['reputation'];
        $color = '';
        $background = $advrepbar['bgcolor'];
        $fontstyle = $advrepbar['fontstyle'];
        $max_width = 'max-width: 300px';
        eval("\$repbar = \"".$templates->get("repbars_18_bar")."\";");
        $repbar = '<div style="margin-bottom:15px;width:200px;"><fieldset><legend>'.$advrepbar['name'].'</legend>'.$repbar.'</fieldset></div>';

        $advrepbars_templ .= $repbar;
    }
} else {
    $advrepbars_templ = "<span>".$lang->repbars_18_no_reputation_bars."</span>";
}


eval("\$repbars_18_legend = \"".$templates->get("repbars_18_legend")."\";");
output_page($repbars_18_legend);