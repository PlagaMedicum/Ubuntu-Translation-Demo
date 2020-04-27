<?php
// Функцыі для імпартавання файла для інтэграцыі з launchpad.net
// Аўтар Віктар Гаўрылавец (Viktar Haurylavets) bycel@tut.by
function fill_array_data($file = '')
{
    $array_data=array();
// Праглядваем файл з launchpad.net
                $mask_current2="/^$.+(^$|\z)/umsU";// Даныя паміж двумя пустымі радкамі ці пустым радком і канцом файла
                // Тут праглядваем усе блокі даных
    if (preg_match_all($mask_current2, $file, $match_first_current2)) {
        // Перабіраем блокі даных
        foreach ($match_first_current2[0] as $key_block => $block) {
            $array_data[$key_block]["initial_text"]=$block;// Захоўваем першапачатковы тэкст для замены потым
            $mask_block="/(?<=msgctxt\s)(\".*\"\n)/umU"; //глядзім параметр msgctxt
            if (preg_match_all($mask_block, $block, $match_block)) {
                $mask_block="/^\"(.*)\"$/umU";
                if (preg_match_all($mask_block, $match_block[0][0], $match_block2)) {
                    //$array_data[$key_block]["msgid"]=$match_block2[1];
                    $array_data[$key_block]["msgctxt"]="\"";
                    foreach ($match_block2[1] as $key_block2 => $value_block2) {
                        $array_data[$key_block]["msgctxt"].=$value_block2;
                    }
                    $array_data[$key_block]["msgctxt"].="\"";
                }
            } else {
                $array_data[$key_block]["msgctxt"]="";
            }
            // Глядзім даныя у параметры msgid
            $mask_block="/(?<=msgid\s)(\".*\"\n){1,}(?=msgstr)/umU";
            if (preg_match_all($mask_block, $block, $match_block)) {
                //var_dump($match_block);
                $mask_block="/^\"(.*)\"$/umU";
                if (preg_match_all($mask_block, $match_block[0][0], $match_block2)) {
                    //$array_data[$key_block]["msgid"]=$match_block2[1];
                    $array_data[$key_block]["msgid"]="\"";
                    foreach ($match_block2[1] as $key_block2 => $value_block2) {
                        $array_data[$key_block]["msgid"].=$value_block2;
                    }
                    $array_data[$key_block]["msgid"].="\"";
                }
            }
            // Глядзім даныя у параметры msgstr, па сутнасці пераклад
            $mask_block="/(?<=msgstr\s)(\".*\"\n){1,}(?=\z)/umU";
            if (preg_match_all($mask_block, $block, $match_block)) {
                $mask_block="/^\"(.*)\"$/umU";
                if (preg_match_all($mask_block, $match_block[0][0], $match_block2)) {
                    //$array_data[$key_block]["msgstr"]=$match_block2[0];
                    $array_data[$key_block]["msgstr"]="\"";
                    foreach ($match_block2[1] as $key_block2 => $value_block2) {
                        $array_data[$key_block]["msgstr"].=$value_block2;
                    }
                    $array_data[$key_block]["msgstr"].="\"";
                }
            }
            // Глядзім ці ёсць множны лік у перакладзе
            $mask_block="/(?<=msgid_plural\s)\".*\"$/umU";
            if (preg_match_all($mask_block, $block, $match_block)) {
                $array_data[$key_block]['plural']['msgid_plural']=$match_block[0][0];
                // Занава шукаем msgid, таму што па масцы вышэй не знойдзецца
                $mask_block="/(?<=msgid\s)\".*\"$/umU";
                if (preg_match_all($mask_block, $block, $match_block)) {
                    $array_data[$key_block]['msgid']=$match_block[0][0];
                }
                // Розныя формы множнага ліку
                $mask_block="/(?<=msgstr\[0\]\s)\".*\"$/umU";
                if (preg_match_all($mask_block, $block, $match_block)) {
                    $array_data[$key_block]['plural']['msgstr'][0]=$match_block[0][0];
                }
                $mask_block="/(?<=msgstr\[1\]\s)\".*\"$/umU";
                if (preg_match_all($mask_block, $block, $match_block)) {
                    $array_data[$key_block]['plural']['msgstr'][1]=$match_block[0][0];
                }
                $mask_block="/(?<=msgstr\[2\]\s)\".*\"$/umU";
                if (preg_match_all($mask_block, $block, $match_block)) {
                    $array_data[$key_block]['plural']['msgstr'][2]=$match_block[0][0];
                }
                // $mask_block="/(?<=msgstr\[3\]\s)\".*\"$/umU";
                // if (preg_match_all($mask_block, $block, $match_block)) {
                //     $array_data[$key_block]['plural']['msgstr'][3]=$match_block[0][0];
                // }
            }
        }
    }
    return $array_data;
}

function change_array_data($array_LP, $array_old_LP, &$parameters)
{

    foreach ($array_LP as $key => $block) {
        if (array_key_exists('msgid', $block)) {
            // var_dump($block['msgid']);
            // if (array_key_exists('plural', $block)) {
            //     var_dump($block['plural']);
            // } else {
            //               var_dump($block['msgstr']);
            // }
            //var_dump($block['msgid']);
            $id = array_search($block['msgid'], array_column($array_old_LP, 'msgid'));
            // if ($id===false) {
            // } else {
            //     var_dump($block['msgid']);
            //     var_dump($array_old_LP[$id]['msgid']);
            // }
            foreach ($array_old_LP as $key_old => $block_old) {
                if (array_key_exists('msgid', $block_old)) {
                    if ($block['msgid']==$block_old['msgid'] and $block['msgctxt']==$block_old['msgctxt']) {
                        if (array_key_exists('plural', $block)) {
                            $block['plural']['msgstr'];
                            for ($i=0; $i <3; $i++) {
                                if (array_key_exists($i, $block['plural']['msgstr'])) {
                                } else {
                                    $block['plural']['msgstr'][$i]="\"\"";
                                }
                                if (array_key_exists($i, $block_old['plural']['msgstr'])) {
                                    if ($block['plural']['msgstr'][$i]!=$block_old['plural']['msgstr'][$i]) {
                                        if ($block['plural']['msgstr'][$i]=="\"\"") {
                                            $block['plural']['msgstr'][$i]=$block_old['plural']['msgstr'][$i];
                                            $parameters['new']=true;
                                            $block['changed']=true;
                                        } else {
                                            if ($parameters['change_not_empty']) {
                                                $block['plural']['msgstr'][$i]=$block_old['plural']['msgstr'][$i];
                                                $parameters['new']=true;
                                                $block['changed']=true;
                                            }
                                        }
                                    }
                                };
                            }
                        } else {
                            if ($block['msgstr']!==$block_old['msgstr']) {
                                if ($block['msgstr']=="\"\"") {
                                    $block['msgstr']=$block_old['msgstr'];
                                    $parameters['new']=true;
                                    $block['changed']=true;
                                } else {
                                    if ($block['msgstr']!="\"\"" and $parameters['change_not_empty']) {
                                        $parameters['new']=true;
                                        
                                        $block['changed']=true;
                                    }
                                }
                            }
                        }
                    };
                }
            }
        }
        $array_LP[$key]=$block;
    }

    return $array_LP;
}
