<?php
/* ----------------------------------------------------------------------

   MyOOS [Dumper]
   http://www.oos-shop.de/

   Copyright (c) 2013 - 2022 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   MySqlDumper
   http://www.mysqldumper.de

   Copyright (C)2004-2011 Daniel Schlichtholz (admin@mysqldumper.de)
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

if (!defined('MOD_VERSION')) {
    exit('No direct access.');
}
// fuegt eine Sortierungsnummer hinzu, um die Ausgabereihenfolge der Daten steuern zu koennen
// (das Feld ENGINE interessiert mich nicht so sehr und muss nicht vorne stehen)
$keysort = [
            'Name' => 0,
            'Rows' => 1,
            'Data_length' => 2,
            'Auto_increment' => 3,
            'Avg_row_length' => 4,
            'Max_data_length' => 5,
            'Comment' => 6,
            'Row_format' => 7,
            'Index_length' => 8,
            'Data_free' => 9,
            'Collation' => 10,
            'Create_time' => 11,
            'Update_time' => 12,
            'Check_time' => 13,
            'Create_options' => 14,
            'Version' => 15,
            'Engine' => 16,
            'Checksum' => 17,
];

$byte_output = [
                'Data_length',
                'Avg_row_length',
                'Max_data_length',
                'Index_length',
                'Data_free',
];

function add_sortkey($name)
{
    global $keysort;
    //echo "<br>Uebergeben: ".$name;
    if (array_key_exists($name, $keysort)) {
        $ret = $keysort[$name];
    } else {
        $ret = 0;
    }
    return $ret;
}

//Data-View
echo $aus.'<h4>'.((1 == $showtables) ? $lang['L_SQL_TABLEVIEW'] : $lang['L_SQL_DATAVIEW']).'</h4><p>';
if (0 == $showtables) {
    $p = 'sql.php?sql_statement='.urlencode($sql['sql_statement']).'&amp;db='.$db.'&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'&amp;limitstart='.$limitstart.'&amp;order='.urlencode($order).'&amp;orderdir='.$orderdir.'&amp;tdc='.$tdcompact;
    echo '<a href="'.$p.'&amp;mode=new">'.$lang['L_SQL_RECORDNEW'].'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<a href="sql.php?db='.$databases['db_actual'].'&amp;dbid='.$dbid.'&amp;tablename='.$tablename.'&amp;context=2">'.$lang['L_SQL_EDIT_TABLESTRUCTURE'].'</a>';
} else {
    $p = 'sql.php?db='.$db.'&amp;dbid='.$dbid.'&amp;context=2';
    echo '<a href="'.$p.'">'.$lang['L_SQL_TABLENEW'].'</a>';
}

//Statuszeile
$tn = ExtractTablenameFromSQL($sql['sql_statement']);
if ($databases['Name'][$dbid] != $databases['db_actual']) {
    // Table is located in a different databasse
    // switch the actual database
    $databases['db_actual'] = $databases['Name'][$dbid];
    // refresh menu to switch to actual database
    echo '<script>'
        .'parent.MyOOS_Dumper_menu.location.href=\'menu.php?dbindex='.$dbid.'\';</script>';
}

echo '</p><p class="tablename">'.('' != $tn ? $lang['L_TABLE'].' <strong>`'.$databases['db_actual'].'`.`'.$tn.'`</strong><br>' : '');
if (isset($msg)) {
    echo $msg;
}

$numrowsabs = -1;
$numrows = 0;
// Vorgehensweise - es soll die Summe der Datensaetze ermittelt werden, wenn es kein LIMIT gibt,
// um die Blaettern-Links korrekt anzuzeigen
$skip_mysql_execution = false;

if (0 == $sql_to_display_data) {
    //mehrere SQL-Statements
    $numrowsabs = $numrows = 0;
    MOD_DoSQL($sql['sql_statement']);
    echo SQLOutput($out);
    $skip_mysql_execution = true;
} else {
    $sql_temp = strtolower($sql['sql_statement']);

    if ('select ' == substr($sql_temp, 0, 7)) {
        if (false !== strpos($sql_temp, ' limit ')) {
            // es wurde ein eigenes Lmit im Query angegeben - eigene Berechnung abbrechen
            $numrowsabs = -1;
        } else {
            $sql_temp = 'SELECT count(*) as anzahl FROM ('.$sql_temp.') as query;';
            $res = @mod_query($sql_temp, false);
            if ($res) {
                if ($row = mysqli_fetch_object($res)) {
                    $numrowsabs = $row->anzahl;
                }
            } else {
                // Query ergab Fehler - Anzahl unbekannt; -1 übernimmt dann die Groesse des Resultsets
                $numrowsabs = -1;
            }
        }
    }
}

$sqltmp = $sql['sql_statement'].$sql['order_statement'].(strpos(strtolower($sql['sql_statement'].$sql['order_statement']), ' limit ') ? '' : $limit);

if (!$skip_mysql_execution) {
    $res = mod_query($sqltmp);
}
$numrows = mysqli_num_rows($res);

if (-1 == $numrowsabs) {
    $numrowsabs = $numrows;
}
if ($limitende > $numrowsabs) {
    $limitende = $numrowsabs;
}

if ($numrowsabs > 0 && $Anzahl_SQLs <= 1) {
    if (0 == $showtables) {
        $command_line = $lang['L_INFO_RECORDS'].' '.($limitstart + 1).' - ';
        if ($limitstart + $limitende > $numrowsabs) {
            $command_line .= $numrowsabs;
        } else {
            $command_line .= $limitstart + $limitende;
        }
        $command_line .= ' '.$lang['L_SQL_VONINS']." $numrowsabs &nbsp;&nbsp;&nbsp;";
        $command_line .= ($limitstart > 0) ? '<a href="'.$params.'&amp;limitstart=0">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;' : '&lt;&lt;&nbsp;&nbsp;&nbsp;&nbsp;';
        $command_line .= ($limitstart > 0) ? '<a href="'.$params.'&amp;limitstart='.(($limitstart - $config['sql_limit'] < 0) ? 0 : $limitstart - $config['sql_limit']).'">&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;' : '&lt;&nbsp;&nbsp;&nbsp;&nbsp;';
        $command_line .= ($limitstart + $limitende < $numrowsabs) ? '<a href="'.$params.'&amp;limitstart='.($limitstart + $config['sql_limit']).'">&gt;</a>&nbsp;&nbsp;&nbsp;&nbsp;' : '&gt;&nbsp;&nbsp;&nbsp;&nbsp;';
        $command_line .= ($limitstart + $limitende < ($numrowsabs - $config['sql_limit'])) ? '<a href="'.$params.'&amp;limitstart='.($numrowsabs - $config['sql_limit']).'">&gt;&gt;</a>' : '&gt;&gt;';
        echo $command_line;
    } else {
        echo $numrowsabs.' '.($numrowsabs > 1 ? $lang['L_TABLES'] : $lang['L_TABLE']);
    }
    echo '</p>';
    //Datentabelle
    echo '<table class="bdr" id="dataTable">';

    $t = $d = '';
    $fdesc = [];
    $key = -1;
    if ($numrows > 0) {
        //Infos und Header holen
        //1.Datensatz fuer Feldinfos
        $row = mysqli_fetch_row($res);
        //Kompaktmodus-Switcher
        $t = '<td colspan="'.(count($row) + 1).'" align="left"><a href="sql.php?db='.$db.'&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'&amp;order='.urlencode($order).'&amp;orderdir='.$orderdir.'&amp;limitstart='.$limitstart.'&amp;sql_statement='.urlencode($sql['sql_statement']).'&amp;tdc='.((0 == $tdcompact) ? '1' : '0').'">'.((1 == $tdcompact) ? $lang['L_SQL_VIEW_STANDARD'] : $lang['L_SQL_VIEW_COMPACT']).'</a>';
        $t .= '&nbsp;&nbsp;&nbsp;'.$lang['L_SQL_QUERYENTRY'].' '.count($row).' '.$lang['L_SQL_COLUMNS'];
        $t .= '</td></tr><tr class="thead">';
        $t .= '<th>&nbsp;</th><th>#</th>';
        $temp = [];

        for ($x = 0; $x < count($row); ++$x) {
            //	$temp[$x]['data'] =mysqli_fetch_field($res, $x);
            $temp[$x]['data'] = mysqli_fetch_field($res);
            $temp[$x]['sort'] = add_sortkey($temp[$x]['data']->name);
        }

        if (1 == $showtables) {
            $temp = mu_sort($temp, 'sort');
        }

        for ($x = 0; $x < count($temp); ++$x) {
            $str = $temp[$x]['data'];
            $t .= '<th align="left" nowrap="nowrap">';
            $pic = '';
            $fdesc[$temp[$x]['data']->name]['name'] = isset($str->name) ? $str->name : '';
            $fdesc[$temp[$x]['data']->name]['table'] = isset($str->table) ? $str->table : '';
            $fdesc[$temp[$x]['data']->name]['max_length'] = isset($str->max_length) ? $str->max_length : '';
            $fdesc[$temp[$x]['data']->name]['not_null'] = isset($str->not_null) ? $str->not_null : '';
            $fdesc[$temp[$x]['data']->name]['primary_key'] = isset($str->primary_key) ? $str->primary_key : '';
            $fdesc[$temp[$x]['data']->name]['unique_key'] = isset($str->unique_key) ? $str->unique_key : '';
            $fdesc[$temp[$x]['data']->name]['multiple_key'] = isset($str->multiple_key) ? $str->multiple_key : '';
            $fdesc[$temp[$x]['data']->name]['numeric'] = isset($str->numeric) ? $str->numeric : '';
            $fdesc[$temp[$x]['data']->name]['blob'] = isset($str->blob) ? $str->blob : '';
            $fdesc[$temp[$x]['data']->name]['type'] = isset($str->type) ? $str->type : '';
            $fdesc[$temp[$x]['data']->name]['unsigned'] = isset($str->unsigned) ? $str->unsigned : '';
            $fdesc[$temp[$x]['data']->name]['zerofill'] = isset($str->zerofill) ? $str->zerofill : '';
            $fdesc[$temp[$x]['data']->name]['Check_time'] = isset($str->Check_time) ? $str->Check_time : '';
            $fdesc[$temp[$x]['data']->name]['Checksum'] = isset($str->Checksum) ? $str->Checksum : '';
            $fdesc[$temp[$x]['data']->name]['Engine'] = isset($str->Engine) ? $str->Engine : '';
            if (isset($str->Comment) && 'VIEW' == substr($str->Comment, 0, 4)) {
                $fdesc[$temp[$x]['data']->name]['Engine'] = 'View';
            }
            $fdesc[$temp[$x]['data']->name]['Version'] = isset($str->Version) ? $str->Version : '';

            $tt = $lang['L_NAME'].': '.$fdesc[$temp[$x]['data']->name]['name'].' Type: '.$fdesc[$temp[$x]['data']->name]['type'].' Max Length: '.$fdesc[$temp[$x]['data']->name]['max_length'].' Unsigned: '.$fdesc[$temp[$x]['data']->name]['unsigned'].' zerofill: '.$fdesc[$temp[$x]['data']->name]['zerofill'];

            $pic = '<img src="'.$icon['blank'].'" alt="" width="1" height="1" border="0">';
            if ((isset($str->primary_key) && (1 == $str->primary_key)) || (isset($str->unique_key) && (1 == $str->unique_key))) {
                if (-1 == $key) {
                    $key = $temp[$x]['data']->name;
                } else {
                    $key .= '|'.$temp[$x]['data']->name;
                }

                if (1 == $str->primary_key) {
                    $pic = $icon['key_primary'];
                } elseif (1 == $str->unique_key) {
                    $pic = $icon['index'];
                }
            }

            // show sorting icon
            $arname = ('ASC' == $orderdir) ? $icon['arrow_down'] : $icon['arrow_up'];
            if ($str->name == $order) {
                $t .= $arname;
            }

            if (-1 == $bb) {
                $bb_link = ('blob' == $str->type) ? '&nbsp;&nbsp;&nbsp;<a style="font-size:10px;color:blue;" title="use BB-Code for this field" href="sql.php?db='.$db.'&amp;bb='.$x.'&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'&amp;order='.$order.'&amp;orderdir='.$orderdir.'&amp;limitstart='.$limitstart.'&amp;sql_statement='.urlencode($sql['sql_statement']).'&amp;tdc='.$tdcompact.'">[BB]</a>' : '';
            } else {
                $bb_link = ('blob' == $str->type) ? '&nbsp;&nbsp;&nbsp;<a title="use BB-Code for this field" href="sql.php?db='.$db.'&amp;bb=-1&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'&amp;order='.urlencode($order).'&amp;orderdir='.$orderdir.'&amp;limitstart='.$limitstart.'&amp;sql_statement='.urlencode($sql['sql_statement']).'&amp;tdc='.$tdcompact.'">[no BB]</a>' : '';
            }
            if (false == $no_order && 0 == $showtables) {
                $t .= $pic.'&nbsp;<a title="'.$tt.'" href="sql.php?db='.$db.'&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'&amp;order='.urlencode($str->name).'&amp;orderdir='.$norder.'&amp;sql_statement='.urlencode($sql['sql_statement']).'&amp;tdc='.$tdcompact.'">'.$str->name.'</a>'.$bb_link;
            } else {
                $t .= $pic.'&nbsp;<span title="'.$tt.'" >'.$str->name.'</span>'.$bb_link;
            }
            $t .= '</th>';
        }
        unset($temp);

        $temp = [];

        //und jetzt Daten holen
        mysqli_data_seek($res, 0);

        $s = $keysort;
        $s = array_flip($keysort);
        ksort($s);
        for ($i = 0; $i < $numrows; ++$i) {
            $data[0] = mysqli_fetch_array($res, MYSQLI_ASSOC);
            if (1 == $showtables && 1 == $tabellenansicht) {
                // Spalten sortieren, wenn wir uns in einer Tabellenuebersicht befinden
                $xx = mu_sort($data, "$s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10], $s[11], $s[12], $s[13], $s[14], $s[15], $s[16]");
                $temp[$i] = $xx[0];

                /***********************
                Ergänzung www.betanet-web.ch - 30.04.2019
                Anz. Einträge in der Tabelle wird in Ausgabe Array überschrieben, damit alle Daten exportiert werden.
                ************************/

                $tabellenname = $data[0]['Name'];
                $numrows12 = 0;
                $select12 = "select * from $tabellenname";
                $res12 = mod_query($select12, false);
                if (!empty($res12)) {
                    $numrows12 = mysqli_num_rows($res12);
                }

                // Überschreiben mit neuem Wert
                $temp[$i]['Rows'] = $numrows12;
            } else {
                $temp[$i] = $data[0];
            }
        }

        $rownr = $limitstart + 1;
        for ($i = 0; $i < $numrows; ++$i) {
            $row = $temp[$i]; // mysqli_fetch_row($res);
            $cl = ($i % 2) ? 'dbrow' : 'dbrow1';
            $erste_spalte = 1;

            // bei Tabellenuebersicht soll nach vorgefertigter Reihenfolge sortiert werden, ansonsten einfach Daten anzeigen
            if (1 == $showtables) {
                $sortkey = $keysort;
            } else {
                $sortkey = $row;
            }
            $spalte = 0;

            // get primary key link for editing
            if ($key > -1) {
                $primary_key = '';
                $keys = explode('|', $key);
                foreach ($sortkey as $rowkey => $rowval) {
                    if (in_array($rowkey, $keys)) {
                        if (strlen($primary_key) > 0) {
                            $primary_key .= ' AND ';
                        }
                        $primary_key .= '`'.urlencode($rowkey).'`=\''.urlencode($rowval).'\'';
                    }
                }
                //echo "<br><br>Primaerschluessel erkannt: ".$primary_key;
            }

            foreach ($sortkey as $rowkey => $rowval) {
                if (('Name' == $rowkey) && 1 == $tabellenansicht && isset($row['Name'])) {
                    $tablename = $row['Name'];
                }

                if (1 == $erste_spalte) {
                    //edit-pics
                    $d .= $nl.'<td valign="top" nowrap="nowrap" class="small">&nbsp;'.$nl;
                    $p = 'sql.php?sql_statement='.urlencode($sql['sql_statement']).'&amp;db='.$databases['db_actual'].'&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'&amp;limitstart='.$limitstart.'&amp;order='.urlencode($order).'&amp;orderdir='.$orderdir.'&amp;tdc='.$tdcompact;
                    if (-1 == $key) {
                        $rk = build_where_from_record($temp[$i]);
                        $p .= '&amp;recordkey='.urlencode($rk);
                    } else {
                        //Key vorhanden
                        $p .= '&amp;recordkey='.urlencode($primary_key); //urlencode("`".$fdesc[$key]['name']."`='".$rowval."'");
                    }
                    if (1 == $showtables) {
                        $p .= '&amp;recordkey='.urlencode($tablename);
                    }
                    if (!isset($no_edit) || !$no_edit) {
                        if (0 == $showtables) {
                            $d .= '<a href="'.$p.'&amp;mode=edit">'.$icon['edit'].'</a>&nbsp;';
                        }
                    }

                    if (0 == $showtables && 0 == $tabellenansicht) {
                        $d .= '<a href="'.$p.'&amp;mode=kill" onclick="if(!confirm(\''.$lang['L_ASKDELETERECORD'].'\')) return false;">'.$icon['delete'].'</a>';
                    } else {
                        if (1 == $tabellenansicht && 1 == $showtables) {
                            $d .= '<a href="sql.php?db='.$db.'&amp;dbid='.$dbid.'&amp;tablename='.$tablename.'&amp;context=2">'.$icon['edit'].'</a>&nbsp;'.$nl.$nl;
                            if (!(isset($row['Comment']) && ('VIEW' == substr(strtoupper($row['Comment']), 0, 4)))) {
                                $d .= '<a href="'.$p.'&amp;mode=empty" onclick="if(!confirm(\''.sprintf($lang['L_ASKTABLEEMPTY'], $tablename).'\')) return false;">'.$icon['table_truncate'].'</a>&nbsp;'.$nl.$nl;
                                $d .= '<a href="'.$p.'&amp;mode=emptyk" onclick="if(!confirm(\''.sprintf($lang['L_ASKTABLEEMPTYKEYS'], $tablename).'\')) return false;">'.$icon['table_truncate_reset'].'</a>&nbsp;'.$nl.$nl;
                                $d .= '<a href="'.$p.'&amp;mode=kill" onclick="if(!confirm(\''.sprintf($lang['L_ASKDELETETABLE'], $tablename).'\')) return false;">'.$icon['delete'].'</a>&nbsp;'.$nl.$nl;
                            } else {
                                $d .= '<a href="'.$p.'&amp;mode=kill_view" onclick="if(!confirm(\''.sprintf($lang['L_ASKDELETETABLE'], $tablename).'\')) return false;">'.$icon['delete'].'</a>&nbsp;'.$nl.$nl;
                            }
                        }
                    }
                    $d .= '</td><td valign="top" class="small" style="text-align:right">'.$rownr.'.&nbsp;</td>';
                    ++$rownr;
                    $erste_spalte = 0;
                }
                $d .= '<td valign="top" class="small" nowrap="nowrap">';
                $divstart = '<div'.((1 == $tdcompact) ? ' class="tdcompact" ' : ' class="tdnormal"').'>';
                $divend = '</div>';
                if ($bb == $spalte) {
                    $data = convert_to_utf8(simple_bbcode_conversion($rowval));
                } else {
                    if (0 == $showtables) {
                        if (isset($fdesc[$rowkey]['type'])) {
                            $data = ('string' == $fdesc[$rowkey]['type'] || 'blob' == $fdesc[$rowkey]['type']) ? convert_to_utf8($rowval) : $rowval;
                        }
                    } else {
                        if (isset($temp[$i][$rowkey])) {
                            $data = ('string' == $fdesc[$rowkey]['type'] || 'blob' == $fdesc[$rowkey]['type']) ? convert_to_utf8($temp[$i][$rowkey]) : $temp[$i][$rowkey];
                        } else {
                            $data = '';
                        }
                        if (in_array($rowkey, $byte_output)) {
                            $data = byte_output($data);
                        }
                    }
                }
                //v($fdesc[$rowkey]);
                if (0 == $showtables) {
                    if (is_null($rowval)) {
                        $data = '<i>NULL</i>';
                    } else {
                        $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
                    }
                }
                ++$spalte;
                $browse_link = '<a href="sql.php?db='.$db.'&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'" title="'.$data.'">';
                $d .= (1 == $tabellenansicht && 'Name' == $rowkey) ? $divstart.$browse_link.$icon['browse'].'</a>&nbsp;'.$browse_link.$data."</a>$divend" : $divstart.$data.$divend;
                $d .= '</td>';
            }
            // Tabellenueberschrift en ausgeben
            if (0 == $i) {
                echo '<tr>'.$t.'</tr>';
            }
            // Daten anzeigen
            echo "\n\n".'<tr class="'.$cl.'">'.$d.'</tr>'."\n\n";
            $d = '';
        }
    }
    echo '</table>';

    if (0 == $showtables) {
        echo '<br>'.$command_line;
    }
} else {
    echo '<p class="success">'.$lang['L_SQL_NODATA'].'</p>';
}
