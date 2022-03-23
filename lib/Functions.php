<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/lib/DataBase.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/cfg/config.php";

// GET
/*
    Возвращает все вопросы
*/
function getQuestionsAll(){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT * FROM `questions`", array());
    return $dataBaseHandler->fetch(DataBase::FETCH_ALL, PDO::FETCH_ASSOC);
}
/**
 *   Возвращает самый просматриваемый вопрос
*/
function getQuestionsMostAnswered(){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT `IdQuestion` FROM `answers`", array());
    $ans = $dataBaseHandler->fetch(DataBase::FETCH_ALL, PDO::FETCH_NUM);    
    $tempRes = [];
    foreach($ans as $value)
        $tempRes[] =  $value[0];
    $res = array_flip(array_count_values($tempRes));
    arsort($res);
    $QuestionId = array_keys($res)[0];
    $dataBaseHandler->query("SELECT * FROM `questions` WHERE `IdQuestion` = :QuestionId",
    array('QuestionId' => $QuestionId));
    return $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_ASSOC);
}
/**
 *   Возвращает ответы на вопрос по заголовку
 *   @param string $title
*/
function getQuestionsAnswers(string $title){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT * FROM `questions` WHERE `Title` = :Title",
    array('Title' => $title));
    $res = $dataBaseHandler->fetch(DataBase::FETCH_ALL, PDO::FETCH_ASSOC);
    if(isset($res)){
        return $res;
    }
    else{
        return null;
    }
}

// POST
/**
 * Записывает (регистрирует) пользователя
 */
function setUser(){
    $Token = htmlspecialchars(strip_tags($_POST['Token'] ?? null));
    $Login = htmlspecialchars(strip_tags($_POST['Login'] ?? null));
    $Password = htmlspecialchars(strip_tags($_POST['Password'] ?? null));
    $Email = htmlspecialchars(strip_tags($_POST['Email'] ?? ''));
    $Img = htmlspecialchars(strip_tags($_POST['Img'] ?? ''));

    if(!is_null($Token) && strlen($Token) >= 1024 && !is_null($Login) && !is_null($Password)){
        $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
        $dataBaseHandler->query("SELECT COUNT(*) `Token` FROM `users` WHERE `Token` = :Token",
        array('Token' => $Token));
        $count = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
        if((int)$count[0] == 0){
            $dataBaseHandler->query("INSERT INTO `users` (`Token`, `Login`, `Password`, `Email`, `Role`, `Img`) VALUES (:Token, :Login, :Password, :Email, 'user', :Img)",
            array('Token' => $Token, 'Login' => $Login, 'Password' => $Password, 'Email' => $Email, 'Img' => $Img));
            http_response_code(201);
            return ['message' => 'Created'];
        }
        else{
            http_response_code(412);
            return ['message' => 'Precondition Failed'];
        }        
    }
    else{
        http_response_code(412);
        return ['message' => 'Precondition Failed'];
    }
}
/**
 * Записывает вопрос
 */
function setQuestion(){
    $UserToken = htmlspecialchars(strip_tags($_POST['Token'] ?? null));
    $Title = htmlspecialchars(strip_tags($_POST['Title'] ?? null));
    $Text = htmlspecialchars(strip_tags($_POST['Text'] ?? null));
    $Tegs = htmlspecialchars(strip_tags($_POST['Tegs'] ?? ''));
    $Img = htmlspecialchars(strip_tags($_POST['Img'] ?? ''));

    if(!is_null($UserToken) && strlen($UserToken) >= 1024 && !is_null($Title) && !is_null($Text)){
        $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
        $dataBaseHandler->query("SELECT COUNT(*) `Token` FROM `users` WHERE `Token` = :Token",
        array('Token' => $UserToken));
        $count = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
        if((int)$count[0] == 1){
            $dataBaseHandler->query("INSERT INTO `questions`(`IdQuestion`, `Title`, `Text`, `Img`, `Tegs`, `UserToken`) VALUES (:Id,:Title,:Text,:Img,:Tegs,:UserToken)",
            array('Id' => null, 'Title' => $Title, 'Text' => $Text, 'Img' => $Img, 'Tegs' => $Tegs, 'UserToken' => $UserToken));
            http_response_code(201);
            return ['message' => 'Created'];
        }
        else{
            http_response_code(412);
            return ['message' => 'Precondition Failed'];
        }
    }
    else{
        http_response_code(412);
        return ['message' => 'Precondition Failed'];
    }
}
/**
 * Записывает вопрос
 */
function setAnswer(){
    $UserToken = htmlspecialchars(strip_tags($_POST['Token'] ?? null));
    $IdQuestion = htmlspecialchars(strip_tags($_POST['IdQuestion'] ?? null));
    $Text = htmlspecialchars(strip_tags($_POST['Text'] ?? null));
    $Img = htmlspecialchars(strip_tags($_POST['Img'] ?? ''));

    if(!is_null($UserToken) && strlen($UserToken) >= 1024 && !is_null($IdQuestion) && !is_null($Text)){
        $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
        $dataBaseHandler->query("SELECT COUNT(*) `Token` FROM `users` WHERE `Token` = :Token",
        array('Token' => $UserToken));
        $count = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
        if((int)$count[0] == 1){

            $dataBaseHandler->query("SELECT COUNT(*) `IdQuestion` FROM `questions` WHERE `IdQuestion` = :IdQuestion",
            array('IdQuestion' => $IdQuestion));
            $count = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
            if((int)$count[0] == 1){
                $dataBaseHandler->query("INSERT INTO `answers`(`IdAnswer`, `Text`, `Img`, `IdQuestion`, `UserToken`) VALUES (:IdAnswer,:Text,:Img,:IdQuestion,:UserToken)",
                array('IdAnswer' => null, 'Text' => $Text, 'Img' => $Img, 'IdQuestion' => $IdQuestion, 'UserToken' => $UserToken));
                http_response_code(201);
                return ['message' => 'Created'];
            }
            else{
                http_response_code(412);
                return ['message' => 'Precondition Failed'];
            }
        }
        else{
            http_response_code(412);
            return ['message' => 'Precondition Failed'];
        }
    }
    else{
        http_response_code(412);
        return ['message' => 'Precondition Failed'];
    }
}