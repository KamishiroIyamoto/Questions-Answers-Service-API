<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Credentials: true');
header('Content-type: json/application');

require_once $_SERVER["DOCUMENT_ROOT"]."/lib/Functions.php";

$q = htmlspecialchars(strip_tags($_GET['q']));
$method = $_SERVER['REQUEST_METHOD'];

if(isset($q)){    
    $attr = explode('/', $q);
    switch($method){
        case 'GET':
            switch($attr[0]){
                case 'Questions':
                    if($attr[1] == 'All'){
                        echo json_encode(getQuestionsAll());
                    } else if($attr[1] == 'MostAnswered'){
                        echo json_encode(getQuestionsMostAnswered());
                    } else if(gettype($attr[1]) == 'string'){
                        $temp = getQuestionsAnswers($attr[1]);
                        if(isset($temp)){
                            echo json_encode($temp);
                        }
                        else{
                            http_response_code(404);
                            die(json_encode(['message' => 'Not Found']));
                        }
                    }
                     else {
                        http_response_code(400);
                        die(json_encode(['message' => 'Bad Request']));
                    }                       
                break;
            case 'Answers':
                break;
            case 'Count':
                break;
            case 'Help':
                break;
            default:
                http_response_code(400);
                die(json_encode(['message' => 'Bad Request']));
            }
            break;
        case 'POST':
            switch($attr[0]){
                case 'User':
                    echo json_encode(setUser());
                    break;
                case 'Questions':
                    echo json_encode(setQuestion());
                    break;
                case 'Answers':
                    echo json_encode(setAnswer());
                    break;
                default:
                    http_response_code(400);
                    die(json_encode(['message' => 'Bad Request']));
            }
            break;
        case 'PATCH':
            break;
        case 'DELETE':
            break;
        default:
            http_response_code(400);
            die(json_encode(['message' => 'Bad Request']));
    }
}
else{
    http_response_code(400);
    die(json_encode(['message' => 'Bad Request']));
}