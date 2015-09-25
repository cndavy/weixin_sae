<?php
// var_dump(getQuestionInfo("ollB4jtmI_i8CqYlj-QMiuxx", "A"));

function getQuestionInfo($openid, $answer)
{
    if(isset($_SERVER['HTTP_APPNAME'])){        //SAE
        $mysql_host = SAE_MYSQL_HOST_M;
        $mysql_host_s = SAE_MYSQL_HOST_S;
        $mysql_port = SAE_MYSQL_PORT;
        $mysql_user = SAE_MYSQL_USER;
        $mysql_password = SAE_MYSQL_PASS;
        $mysql_database = SAE_MYSQL_DB;
    }else{
        $mysql_host = "127.0.0.1";
        $mysql_host_s = "127.0.0.1";
        $mysql_port = "3306";
        $mysql_user = "root";
        $mysql_password = "root";
        $mysql_database = "weixin";
    }

    $con = mysql_connect($mysql_host.':'.$mysql_port, $mysql_user, $mysql_password);
    if (!$con){die('Could not connect: ' . mysql_error());}
    mysql_query("SET NAMES 'UTF8'");
    mysql_select_db($mysql_database, $con);

    $mysql_table_questions = "questions";
    $mysql_get_rows = "SHOW TABLE STATUS like '".$mysql_table_questions."';";
    $result5 = mysql_query($mysql_get_rows);
    $row = mysql_fetch_array($result5);
    $max_question = $row['Rows'];
    
    $mysql_table_record = "record";
    $finish_question = 0;
    if ($answer == "答题"){
        $mysql_insert = "INSERT INTO `".$mysql_table_record."` (`openid`, `question`, `question_y`, `question_n`) VALUES('".$openid."', '0', '0', '0') ON DUPLICATE KEY UPDATE `question` = '0', `question_y` = '0', `question_n` = '0';";
        @mysql_query($mysql_insert);
        $finish_question = 0;
    }else{
        $mysql_select = "SELECT * FROM `".$mysql_table_record."` WHERE `openid` = '".$openid."';";
        $result4 = mysql_query($mysql_select);
        $row = mysql_fetch_array($result4);
        $finish_question = $row['question'];
        
        if($finish_question == $max_question){
            return "你已经答完了全部题目";
        }
        
        $mysql_update = "UPDATE `".$mysql_table_record."` SET `question` = `question` + 1 WHERE `openid` = '".$openid."';";
        $result3 = mysql_query($mysql_update);
        $finish_question += 1; 
    }

    if ($finish_question == 0){
        $mysql_state = "SELECT * FROM `".$mysql_table_questions."` LIMIT ".$finish_question.",1";
    }else if($finish_question == $max_question){
        $mysql_state = "SELECT * FROM `".$mysql_table_questions."` LIMIT ".($finish_question - 1).",1";
    }else{
        $mysql_state = "SELECT * FROM `".$mysql_table_questions."` LIMIT ".($finish_question - 1).",2";
    }
    $result = @mysql_query($mysql_state);

    $content = "";
    if ($finish_question == 0){
        if (mysql_num_rows($result) < 1){
            $content .= "系统繁忙，请过会重试\n";
        }else{
            $content .= "一站到底在线答题开始\n";
            while($row = mysql_fetch_array($result))
            {
                $content .= "第".$row['id']."题：".$row['question']."\n";
                $content .= empty($row['optionA'])?"":"A. ".$row['optionA']."\n";
                $content .= empty($row['optionB'])?"":"B. ".$row['optionB']."\n";
                $content .= empty($row['optionC'])?"":"C. ".$row['optionC']."\n";
                $content .= empty($row['optionD'])?"":"D. ".$row['optionD']."\n";
                $content .= empty($row['optionE'])?"":"E. ".$row['optionE']."\n";
                $content .= empty($row['optionF'])?"":"F. ".$row['optionF']."\n";
            }
        }
    }else if($finish_question == $max_question){
        while($row = mysql_fetch_array($result))
        {
            $content .= ($answer == $row['answer'])?"回答正确\n":"回答错误！正确答案为 ".$row['answer']."\n";
            if ($answer == $row['answer']){
                $mysql_update_yesno = "UPDATE `".$mysql_table_record."` SET `question_y` = `question_y` + 1 WHERE `openid` = '".$openid."';";
            }else{
                $mysql_update_yesno = "UPDATE `".$mysql_table_record."` SET `question_n` = `question_n` + 1 WHERE `openid` = '".$openid."';";
            }
            $result_yesno = mysql_query($mysql_update_yesno);
            
            $mysql_statistics = "SELECT * FROM `".$mysql_table_record."` WHERE `openid` = '".$openid."';";
            $result6 = mysql_query($mysql_statistics);
            $row = mysql_fetch_array($result6);
            $result_all = $row['question'];
            $result_yes = $row['question_y'];
            $content .= "恭喜，您已完成全部题目。"."\n题目个数：".$result_all."\n正确个数：".$result_yes."\n正确率：".round(($result_yes / $result_all) * 100, 2)."%";
        }
    }else{
        $first = true;
        while($row = mysql_fetch_array($result))
        {
            if ($first){
                $content .= ($answer == $row['answer'])?"回答正确\n":"回答错误！正确答案为 ".$row['answer']."\n";
                $first = false;
                if ($answer == $row['answer']){
                    $mysql_update_yesno = "UPDATE `".$mysql_table_record."` SET `question_y` = `question_y` + 1 WHERE `openid` = '".$openid."';";
                }else{
                    $mysql_update_yesno = "UPDATE `".$mysql_table_record."` SET `question_n` = `question_n` + 1 WHERE `openid` = '".$openid."';";
                }
                $result_yesno = mysql_query($mysql_update_yesno);
            }else{
                $content .= "第".$row['id']."题：".$row['question']."\n";
                $content .= empty($row['optionA'])?"":"A. ".$row['optionA']."\n";
                $content .= empty($row['optionB'])?"":"B. ".$row['optionB']."\n";
                $content .= empty($row['optionC'])?"":"C. ".$row['optionC']."\n";
                $content .= empty($row['optionD'])?"":"D. ".$row['optionD']."\n";
                $content .= empty($row['optionE'])?"":"E. ".$row['optionE']."\n";
                $content .= empty($row['optionF'])?"":"F. ".$row['optionF']."\n";
            }
        }
    }
    mysql_close($con);
    return trim($content);
}

?>