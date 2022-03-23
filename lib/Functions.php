<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/lib/DataBase.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/cfg/config.php";

// GET

/**
 *   Возвращает все вопросы
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
 *   Возвращает вопрос по заголовку вопроса
 *   @param string $title Заголовок вопроса
*/
function getQuestions(string $title){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT * FROM `questions` WHERE `Title` = :Title",
    array('Title' => $title));
    $res = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_ASSOC);
    if(isset($res)){
        return $res;
    }
    else{
        return null;
    }
}
/**
 *   Возвращает ответы по заголовку вопроса
 *   @param string $title Заголовок вопроса
*/
function getAnswers(string $title){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT `IdQuestion` FROM `questions` WHERE `Title` = :Title",
    array('Title' => $title));    
    $res = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
    if(isset($res[0])){
        $dataBaseHandler->query("SELECT * FROM `answers` WHERE `IdQuestion` = :IdQuestion",
        array('IdQuestion' => $res[0]));
        $res = $dataBaseHandler->fetch(DataBase::FETCH_ALL, PDO::FETCH_ASSOC);
        if(isset($res)){
            return $res;
        }
        else{
            return null;
        }
    }
    else{
        return null;
    }
}
/**
 *   Возвращает, есть ли такой пользователь
 *   @param string $token Токен пользователя
*/
function getUsers(string $token): bool {
    if(strlen($token) < 1024){
        return false;
    }
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT COUNT(*) FROM `users` WHERE `Token` = :Token",
    array('Token' => $token));
    $res = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM)[0];
    if((int)$res == 1){
        return 1;
    }
    return 0;
    
}
/**
 * Возвращает количество пользователей
 */
function getUsersCount(){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT COUNT(*) FROM `users`", array());
    return $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM)[0];
}
/**
 * Возвращает количество вопросов
 */    
function getQuestionsCount(){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT COUNT(*) FROM `questions`", array());
    return $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM)[0];
}
/**
 * Возвращает количество ответов по заголовку вопроса
 * @param string $title Заголовок вопроса
 */    
function getAnswersCount(string $title){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT `IdQuestion` FROM `questions` WHERE `Title` = :Title",
    array('Title' => $title));    
    $res = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
    if(isset($res[0])){
        $dataBaseHandler->query("SELECT COUNT(*) FROM `answers` WHERE `IdQuestion` = :IdQuestion",
        array('IdQuestion' => $res[0]));
        $res = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
        if(isset($res[0])){
            return $res[0];
        }
        else{
            http_response_code(404);
            return ['message' => 'Not Found'];
        }
    }
    else{
        http_response_code(404);
        return ['message' => 'Not Found'];
    }
}
/**
 * Возвращает документацию
 */ 
function getHelp(){
    $res = [
        'GET' => [
            'Questions' => [
                'All' => 'Возвращает все вопросы',
                'Count' => 'Возвращает количество вопросов',
                'MostAnswered' => 'Возвращает самый просматриваемый вопрос',
                'anyString' => 'Возвращает вопрос по заголовку вопроса'
            ],
            'Answers' => [
                'Count' =>[
                    'anyString' => 'Возвращает количество ответов по заголовку вопроса'
                ],
                'anyString' => 'Возвращает ответы по заголовку вопроса'
            ],
            'Users' => [
                'Count' =>[
                    'anyString' => 'Возвращает количество ответов по заголовку вопроса'
                ],
                'anyString' => 'Возвращает, есть ли такой пользователь'
            ],
            'Help' => 'Возвращает документацию'
        ],
        'POST' => [
            'Questions' => 'Записывает вопрос',
            'Answers' => 'Записывает ответ',
            'Users' => 'Записывает (регистрирует) пользователя'
        ],
        'PATCH' => [],
        'DELETE' => []
    ];
    return $res;
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
        if(!getUsers($Token)){
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
        if(getUsers($UserToken)){
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
 * Записывает ответ
 */
function setAnswer(){
    $UserToken = htmlspecialchars(strip_tags($_POST['Token'] ?? null));
    $IdQuestion = htmlspecialchars(strip_tags($_POST['IdQuestion'] ?? null));
    $Text = htmlspecialchars(strip_tags($_POST['Text'] ?? null));
    $Img = htmlspecialchars(strip_tags($_POST['Img'] ?? ''));

    if(!is_null($UserToken) && strlen($UserToken) >= 1024 && !is_null($IdQuestion) && !is_null($Text)){
        $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
        if(getUsers($UserToken)){
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

// PATCH

/**
 * Обновляет информацию о пользователей
 * @param array $Params Данные для изменения
 * @param string $AdminToken Токен администратора
 */
function updateUser(array $Params, string $AdminToken){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT `Role` FROM `users` WHERE `Token` = :Token",
    array('Token' => htmlspecialchars(strip_tags($AdminToken))));
    if($dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM)[0] ?? '' == 'admin'){
        $Token = htmlspecialchars(strip_tags($Params['Token'] ?? null));
        $Login = htmlspecialchars(strip_tags($Params['Login'] ?? null));
        $Password = htmlspecialchars(strip_tags($Params['Password'] ?? null));
        $Email = htmlspecialchars(strip_tags($Params['Email'] ?? null));
        $Role = htmlspecialchars(strip_tags($Params['Role'] ?? null));
        $Img = htmlspecialchars(strip_tags($Params['Img'] ?? null));

        if(getUsers($Token)){
            $dataBaseHandler->query("UPDATE `users` SET `Token`= :Token,`Login`= :Login,`Password`= :Password,`Email`= :Email,`Role`= :Role,`Img`= :Img",
            array('Token' => $Token, 'Login' => $Login, 'Password' => $Password, 'Email' => $Email, 'Role' => $Role, 'Img' => $Img));
            return ['message' => 'OK'];
        }
        else{
            http_response_code(412);
            return ['message' => 'Precondition Failed'];
        }
    }
    else{
        http_response_code(403);
        return ['message' => 'Forbidden'];
    }    
}
/**
 * Обновляет вопрос
 * @param array $Params Данные для изменения
 * @param string $AdminToken Токен администратора
 */
function updateQuestion(array $Params, string $AdminToken){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT `Role` FROM `users` WHERE `Token` = :Token",
    array('Token' => htmlspecialchars(strip_tags($AdminToken))));
    if($dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM)[0] ?? '' == 'admin'){
        $IdQuestion = htmlspecialchars(strip_tags($Params['IdQuestion'] ?? null));
        $Title = htmlspecialchars(strip_tags($Params['Title'] ?? null));
        $Text = htmlspecialchars(strip_tags($Params['Text'] ?? null));
        $Tegs = htmlspecialchars(strip_tags($Params['Tegs'] ?? null));
        $Img = htmlspecialchars(strip_tags($Params['Img'] ?? null));

        $dataBaseHandler->query("SELECT COUNT(*) FROM `questions` WHERE `IdQuestion` = :IdQuestion",
        array('IdQuestion' => $IdQuestion));       
        $count = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
        if((int) $count[0] == 1){
            $dataBaseHandler->query("UPDATE `questions` SET `IdQuestion`= :IdQuestion,`Title`= :Title,`Text`= :Text,`Img`= :Img,`Tegs`= :Tegs",
            array('IdQuestion' => $IdQuestion, 'Title' => $Title, 'Text' => $Text, 'Img' => $Img, 'Tegs' => $Tegs));
            return ['message' => 'OK'];
        }
        else{
            http_response_code(412);
            return ['message' => 'Precondition Failed'];
        }
    }
    else{
        http_response_code(403);
        return ['message' => 'Forbidden'];
    }    
}
/**
 * Обновляет ответ
 * @param array $Params Данные для изменения
 * @param string $AdminToken Токен администратора
 */
function updateAnswer(array $Params, string $AdminToken){
    $dataBaseHandler = new DataBase(HOST_NAME, USER_NAME, PASSWORD, DB_NAME);
    $dataBaseHandler->query("SELECT `Role` FROM `users` WHERE `Token` = :Token",
    array('Token' => htmlspecialchars(strip_tags($AdminToken))));
    if($dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM)[0] ?? '' == 'admin'){
        $IdAnswer = htmlspecialchars(strip_tags($Params['IdAnswer'] ?? null));
        $Text = htmlspecialchars(strip_tags($Params['Text'] ?? null));
        $Img = htmlspecialchars(strip_tags($Params['Img'] ?? null));

        $dataBaseHandler->query("SELECT COUNT(*) FROM `answers` WHERE `IdAnswer` = :IdAnswer",
        array('IdAnswer' => $IdAnswer));       
        $count = $dataBaseHandler->fetch(DataBase::FETCH, PDO::FETCH_NUM);
        if((int) $count[0] == 1){
            $dataBaseHandler->query("UPDATE `answers` SET `IdAnswer`= :IdAnswer,`Text`= :Text,`Img`= :Img",
            array('IdAnswer' => $IdAnswer, 'Text' => $Text, 'Img' => $Img));
            return ['message' => 'OK'];
        }
        else{
            http_response_code(412);
            return ['message' => 'Precondition Failed'];
        }
    }
    else{
        http_response_code(403);
        return ['message' => 'Forbidden'];
    }    
}