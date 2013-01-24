<?php

class Updater
{
    protected static $_last_fetch = null;

    public function parseChinaCompany($doc)
    {
        $info = new StdClass;
        $base_table_dom = $doc->getElementsByTagName('table')->item(6)->getElementsByTagName('table')->item(1);
        foreach ($base_table_dom->childNodes as $tr_dom) {
            if ($tr_dom->tagName !== 'tr') {
                continue;
            }
            if (!$tr_dom->getElementsByTagName('td')->item(1)) {
                continue;
            }
            $column = trim($tr_dom->getElementsByTagName('td')->item(1)->childNodes->item(0)->wholeText);

            if (in_array($column, array('統一編號', '公司狀況', '登記機關', '辦事處所在地', '在中華民國境內營運資金', '分公司所在地', '股權狀況', '代表人在台灣地區業務活動範圍', '訴訟及非訴訟代理人姓名', '在台灣地區營業所用'))) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2)->childNodes->item(0);
                $info->{$column} = trim(explode("\n", trim($value_dom->wholeText))[0]);
            } elseif (in_array($column, array('公司名稱'))) { // 有中英文名稱
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                $lines = explode("\n", trim($value_dom->nodeValue));
                $info->{$column} = array(
                    array(trim($lines[0]), trim($lines[1])),
                    array(trim($lines[3]), trim($lines[4])),
                );
            } elseif (in_array($column, array('核准許可報備日期', '最後核准變更日期', '核准許可日期', '停業日期(起)', '停業日期(迄)'))) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2)->childNodes->item(0);
                $value = trim(explode("\n", trim($value_dom->wholeText))[0]);
                if (!preg_match('#(.*)年(.*)月(.*)日#', $value, $matches)) {
                    $info->{$column} = null;
                } else {
                    $value = new stdClass;
                    $value->year = 1911 + intval($matches[1]);
                    $value->month = intval($matches[2]);
                    $value->day = intval($matches[3]);
                    $info->{$column} = $value;
                }
            } elseif ('所營事業資料' == $column) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                $list_table_dom = $value_dom->getElementsByTagName('table')->item(0);
                $list = array();
                foreach ($list_table_dom->getElementsByTagName('td') as $td_dom) {
                    $lines = explode("\n", $td_dom->childNodes->item(0)->wholeText);
                    if (!preg_match('#^([A-Z0-9]*)#', trim($lines[1]), $matches)) {
                        throw new Exception('事業代號不正確');
                    }
                    $list[] = array($matches[1], trim($lines[2]));
                }
                $info->{$column} = $list;
            } elseif ($column == '' or trim($column, chr(0xC2).chr(0xA0)) == '') {
            } else {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                echo '[TODO2]';
                var_dump($column);
                var_dump($value_dom->nodeValue);
                exit;
            }
        }

        return $info;
    }
    public function parseForeignCompany($doc)
    {
        $info = new StdClass;
        $base_table_dom = $doc->getElementsByTagName('table')->item(6)->getElementsByTagName('table')->item(1);
        foreach ($base_table_dom->childNodes as $tr_dom) {
            if ($tr_dom->tagName !== 'tr') {
                continue;
            }
            if (!$tr_dom->getElementsByTagName('td')->item(1)) {
                continue;
            }
            $column = trim($tr_dom->getElementsByTagName('td')->item(1)->childNodes->item(0)->wholeText);

            if (in_array($column, array('統一編號', '公司狀況', '登記機關', '辦事處所在地', '在中華民國境內營運資金', '分公司所在地', '股權狀況', '代表人在中華民國境內所為之法律行為'))) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2)->childNodes->item(0);
                $info->{$column} = trim(explode("\n", trim($value_dom->wholeText))[0]);
            } elseif (in_array($column, array('訴訟及非訴訟代理人姓名', '公司名稱'))) { // 有中英文名稱
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                $lines = explode("\n", trim($value_dom->nodeValue));
                $info->{$column} = array(
                    trim($lines[0]),
                    trim($lines[count($lines) - 1]),
                );
            } elseif (in_array($column, array('核准設立日期', '最後核准變更日期', '核准報備日期', '核准認許日期', '停業日期(起)', '停業日期(迄)'))) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2)->childNodes->item(0);
                $value = trim(explode("\n", trim($value_dom->wholeText))[0]);
                if (!preg_match('#(.*)年(.*)月(.*)日#', $value, $matches)) {
                    $info->{$column} = null;
                } else {
                    $value = new stdClass;
                    $value->year = 1911 + intval($matches[1]);
                    $value->month = intval($matches[2]);
                    $value->day = intval($matches[3]);
                    $info->{$column} = $value;
                }
            } elseif ('所營事業資料' == $column) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                $list_table_dom = $value_dom->getElementsByTagName('table')->item(0);
                $list = array();
                foreach ($list_table_dom->getElementsByTagName('td') as $td_dom) {
                    $lines = explode("\n", $td_dom->childNodes->item(0)->wholeText);
                    if (!preg_match('#^([A-Z0-9]*)#', trim($lines[1]), $matches)) {
                        throw new Exception('事業代號不正確');
                    }
                    $list[] = array($matches[1], trim($lines[2]));
                }
                $info->{$column} = $list;
            } elseif ($column == '' or trim($column, chr(0xC2).chr(0xA0)) == '') {
            } else {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                echo '[TODO2]';
                var_dump($column);
                var_dump($value_dom->nodeValue);
                exit;
            }
        }

        return $info;
    }

    public function parseFile($content)
    {
        $doc = new DOMDocument();
        @$doc->loadHTML(str_replace('text/html; charset=Big5', 'text/html; charset=UTF-8', iconv('Big5', 'UTF-8//IGNORE', $content)));

        // 基本資料
        $info = new StdClass;
        $table_dom = $doc->getElementsByTagName('table');
        if ('外國公司報備基本資料' == trim(explode("\n", trim($table_dom->item(3)->nodeValue))[0])) {
            return self::parseForeignCompany($doc);
        } elseif ('外國公司認許基本資料' == trim(explode("\n", trim($table_dom->item(3)->nodeValue))[0])) {
            return self::parseForeignCompany($doc);
        } elseif ('大陸公司許可報備基本資料' == trim(explode("\n", trim($table_dom->item(3)->nodeValue))[0])) {
            return self::parseChinaCompany($doc);
        } elseif ('大陸公司許可基本資料' == trim(explode("\n", trim($table_dom->item(3)->nodeValue))[0])) {
            return self::parseChinaCompany($doc);
        }
        $table_dom = $doc->getElementById('Tab01');
        if (!$table_dom) {
            throw new Exception('不知道的 HTML');
        }
        $base_table_dom = $table_dom->getElementsByTagName('table')->item(1);
        foreach ($base_table_dom->getElementsByTagName('tr') as $tr_dom) {
            if (!$tr_dom->getElementsByTagName('td')->item(1)) {
                continue;
            }
            $column = trim($tr_dom->getElementsByTagName('td')->item(1)->childNodes->item(0)->wholeText);

            if (in_array($column, array('統一編號', '公司狀況', '公司名稱', '資本總額(元)', '實收資本額(元)', '代表人姓名', '公司所在地', '登記機關', '股權狀況'))) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2)->childNodes->item(0);
                $info->{$column} = trim(explode("\n", trim($value_dom->wholeText))[0]);
            } elseif (in_array($column, array('核准設立日期', '最後核准變更日期', '停業日期(起)', '停業日期(迄)', '延展開業日期(迄)'))) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2)->childNodes->item(0);
                $value = trim(explode("\n", trim($value_dom->wholeText))[0]);
                if (preg_match('#(.*)年(.*)月(.*)日#', $value, $matches)) {
                    $value = new stdClass;
                    $value->year = 1911 + intval($matches[1]);
                    $value->month = intval($matches[2]);
                    $value->day = intval($matches[3]);
                    $info->{$column} = $value;
                } else {
                    $info->{$column} = null;
                }
            } elseif ('所營事業資料' == $column) {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                $list_table_dom = $value_dom->getElementsByTagName('table')->item(0);
                $list = array();
                foreach ($list_table_dom->getElementsByTagName('td') as $td_dom) {
                    $lines = explode("\n", $td_dom->childNodes->item(0)->wholeText);
                    if (!preg_match('#^([A-Z0-9]*)#', trim($lines[1]), $matches)) {
                        throw new Exception('事業代號不正確');
                    }
                    $list[] = array($matches[1], trim($lines[2]));
                }
                $info->{$column} = $list;
            } elseif ($column == '' or preg_match('/查詢「/', $column)) {
            } else {
                $value_dom = $tr_dom->getElementsByTagName('td')->item(2);
                echo '[TODO1]' . $column . ' ' . $value_dom->nodeValue . "\n";
                exit;
            }
        }

        if ('核准認許' !== $info->{'公司狀況'}) {
        // 董監事名單
        $table_dom = $doc->getElementById('Tab02');
        $base_table_dom = $table_dom->getElementsByTagName('table')->item(1);
        $list = array();
        for ($i = 1; $i < $base_table_dom->getElementsByTagName('tr')->length; $i ++) {
            $td_doms = $base_table_dom->getElementsByTagName('tr')->item($i)->getElementsByTagName('td');
            $row = new StdClass;
            $row->{'序號'} = trim($td_doms->item(1)->nodeValue);
            $row->{'職稱'} = trim($td_doms->item(2)->nodeValue);
            $row->{'姓名'} = trim($td_doms->item(3)->nodeValue);
            if (trim($td_doms->item(4)->nodeValue) != '') {
                $a_dom = $td_doms->item(4)->getElementsByTagName('a')->item(0);
                if (!$a_dom) {
                    $row->{'所代表法人'} = array(0, trim($td_doms->item(4)->nodeValue));
                } else {
                    $link = $a_dom->getAttribute('href');
                    if (!preg_match('#banNo=(.*)#', $link, $matches)) {
                        throw new Exception('請處理法人');
                    }
                    $row->{'所代表法人'} = array($matches[1], trim($a_dom->nodeValue));
                }
            } else {
                $row->{'所代表法人'} = '';
            }
            $row->{'出資額'} = trim($td_doms->item(5)->nodeValue);
            $list[] = $row;
        }
        $info->{'董監事名單'} = $list;

        // 經理人名單
        $table_dom = $doc->getElementById('Tab03');
        $base_table_dom = $table_dom->getElementsByTagName('table')->item(1);
        $list = array();
        for ($i = 1; $i < $base_table_dom->getElementsByTagName('tr')->length; $i ++) {
            $td_doms = $base_table_dom->getElementsByTagName('tr')->item($i)->getElementsByTagName('td');
            $row = new StdClass;
            $row->{'序號'} = trim($td_doms->item(1)->nodeValue);
            $row->{'姓名'} = trim($td_doms->item(2)->nodeValue);
            if (!preg_match('#(.*)年(.*)月(.*)日#', trim($td_doms->item(3)->nodeValue), $matches)) {
                $row->{'到職日期'} = null;
            } else {
                $value = new stdClass;
                $value->year = 1911 + intval($matches[1]);
                $value->month = intval($matches[2]);
                $value->day = intval($matches[3]);
                $row->{'到職日期'} = $value;
            }
            $list[] = $row;
        }
        $info->{'經理人名單'} = $list;
        }

        return $info;
    }

    public function update($id, $options = array())
    {
        $unit = Unit::find($id);
        if (!$unit) {
            // 找不到檔案就不用判斷了
        } else {
            $modified_at = $unit->updated_at;
            if (array_key_exists('month', $options)) {
                $query_time = strtotime('+1 month', mktime(0, 0, 0, $options['month'], 1, $options['year']));
                if ($query_time < $modified_at) {
                    return;
                }
            }
        }
        $url = 'http://gcis.nat.gov.tw/pub/cmpy/cmpyInfoAction.do?method=detail&banNo=' . $id;
        // 一秒只更新一個檔案
        while (!is_null(self::$_last_fetch) and (microtime(true) - self::$_last_fetch) < 0.5) {
            usleep(1000);
        }
        self::$_last_fetch = microtime(true);

        $content = self::http($url);
        if (!$content) {
            trigger_error("找不到網頁內容: $url", E_USER_WARNING);
            return;
        }

        $info = self::parseFile($content);

        if (!$parsed_id = $info->{'統一編號'}) {
            trigger_error("找不到統一編號: $id", E_USER_WARNING);
            return;

            throw new Exception('統一編號 not found?');
        }
        unset($info->{'統一編號'});

        if (!$unit = Unit::find($id)) {
            $unit = Unit::insert(array(
                'id' => $id,
                'type' => 1,
            ));
        }
        $unit->updateData($info);
    }

    public function http($url)
    {
        error_log('Fetching ' . $url);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_REFERER, 'http://gcis.nat.gov.tw/pub/cmpy/cmpyInfoListAction.do');
        return curl_exec($curl);
    }
}