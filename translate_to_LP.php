<?php
include 'translate_LP_functions.php';
// Скрыпт для імпартавання файла для інтэграцыі з launchpad.net
// Аўтар Віктар Гаўрылавец (Viktar Haurylavets) bycel@tut.by
// Маска для файла, напрыклад po_adduser-be.po
$mask="/po_([\w\-+\.]+)-be\.po/uU";
// Праглядваем каталог from_LP
$path = scandir("from_LP");
// Каталог дзе знаходзяцца старыя файлы з краўдына
$link="rosetta-eoan/be/LC_MESSAGES/";
// Сюды будзем запісваць новыя файлы
$link_to_LP="Eoan-new/be/LC_MESSAGES/";
// Спіс пекаладчыкаў
$add_translators="# Клюеў Аляксандр <katoshrodingera@protonmail.ch>, 2019, 2020.
# Цуканаў Аляксандр <a-tsukanov@protonmail.com>, 2019, 2020.
# Alyaksandr Koshal <alyaksandr.koshal@gmail.com>, 2019, 2020.
# Алег Раковіч <Unknown>, 2019, 2020.\n";
// Апошні перакдадчык
$last_translator="Клюеў Аляксандр <katoshrodingera@protonmail.ch>";
// Параметры замены
$parameters=array();
// Замяняць не толькі пустыя радкі, калі стаіць false то замяняюцца только пустыя радкі
$parameters['change_not_empty']=true;
// Перабіраем файлы ў каталогу
foreach ($path as $k) {
    $parameters['new']=false;// Будзе трыгер
    $arraY_LP=array();
    $arraY_old_LP=array();
    $num_changes=0;
    if (preg_match($mask, $k, $match)) {
        echo "$k\n";// Надрукуем назву файла
        $link_current=$link.$match[1].".po";// Назва файла ў краўдыне
        $file=file_get_contents("from_LP/".$k);// Чытаем змест файла імпартаванага з launchpad.net
        $file_current=file_get_contents($link_current);// Чытаем змест файла з краўдына
        $mask_first="/\A.*^$/usUm";// Маска ад пачатку тэкста да першага пустога радка
        if (preg_match($mask_first, $file, $match_first)) {
            if (preg_match($mask_first, $file_current, $match_first_current)) {
                // Калі ёсць у першым і другім файле змест пра файл з каментарыямі
                // Спачатку змяняем апошняга перакладчыка
                $mask_current="/Last-Translator: .*\\\\n/usU";

                $match_first[0]=preg_replace($mask_current, "Last-Translator: ".$last_translator."\\n", $match_first[0]);
                // Потым глядім ці ўжо дабаўлены нашы перакладчыкі
                $pos = strpos($match_first[0], $add_translators);
                if ($pos === false) {
                    // Калі не дабаўлены, тады дабаўляем да канца
                    $mask_current2="/(#.*\n)+/um";
                    if (preg_match($mask_current2, $match_first[0], $match_first_current2)) {
                        $match_first[0]=preg_replace($mask_current2, $match_first_current2[0].$add_translators, $match_first[0]);
                    }
                }

                // Праглядваем файл з launchpad.net
                $arraY_LP=fill_array_data($file);
                $arraY_old_LP=fill_array_data($file_current);
                $arraY_LP=change_array_data($arraY_LP, $arraY_old_LP, $parameters);
                $file=preg_replace($mask_first, $match_first[0], $file);
                $num_changes=0;
                // Замяняем радкі калі ёсць для замены
                foreach ($arraY_LP as $key5 => $block) {
                    if (array_key_exists('changed', $block)) {
                        if (array_key_exists('plural', $block)) {
                            $text="";
                            $mask_block="/^$\n#/umU";
                            $text=$block["initial_text"];
                            $text=preg_replace($mask_block, "#", $text);
                            $mask_block="/^[^#].+$/umU";
                            $text=preg_replace($mask_block, "", $text);
                            $mask_block="/^\s$/umU";
                            $text=preg_replace($mask_block, "", $text);
                            if ($block["msgctxt"]!="") {
                                $text.="msgctxt ".$block["msgctxt"]."\n";
                            }
                            $text.="msgid ".$block["msgid"]."\n";
                            $text.="msgid_plural ".$block['plural']['msgid_plural']."\n";
                            foreach ($block['plural']['msgstr'] as $key8 => $value8) {
                                $text.="msgstr[".$key8."] ".$value8."\n";
                            }
                            $text="\n".$text;
                            $file=str_replace($block["initial_text"], $text, $file);
                            $num_changes++;
                        } else {
                            $text="";
                            $mask_block="/^$\n#/umU";
                            $text=$block["initial_text"];
                            $text=preg_replace($mask_block, "#", $text);
                            $mask_block="/^[^#].+$/umU";
                            $text=preg_replace($mask_block, "", $text);
                            $mask_block="/^\s$/umU";
                            $text=preg_replace($mask_block, "", $text);
                            if ($block["msgctxt"]!="") {
                                $text.="msgctxt ".$block["msgctxt"]."\n";
                            }
                            $text.="msgid ".$block["msgid"]."\n";
                            $text.="msgstr ".$block["msgstr"]."\n";
                            $text="\n".$text;
                            $file=str_replace($block["initial_text"], $text, $file);
                            $num_changes++;
                        }
                    }
                }
            }
        }
    }
    // Калі ёсць змены, запісваем файл
    if ($parameters['new']) {
        file_put_contents($link_to_LP.$k, $file);
        echo "заменена $num_changes радкоў\n";
    }
}
