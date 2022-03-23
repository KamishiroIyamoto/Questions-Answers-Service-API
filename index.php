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
    $type = $attr[0] ?? null;
    $params = $attr[1] ?? null;
    switch($method){
        case 'GET':
            switch($type){
                case 'Questions':
                    if($params == 'All'){
                        echo json_encode(getQuestionsAll());
                    } elseif($params == 'Count'){
                        echo json_encode(getQuestionsCount());
                    } elseif($params == 'MostAnswered'){
                        echo json_encode(getQuestionsMostAnswered());
                    } elseif(gettype($params) == 'string'){
                        $temp = getQuestions($params);
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
                if($params == 'Count'){
                    echo json_encode(getAnswersCount($attr[2]));
                } elseif(gettype($params) == 'string') {
                    $temp = getAnswers($params);
                    if(isset($temp)){
                        echo json_encode($temp);
                    }
                    else{
                        http_response_code(404);
                        die(json_encode(['message' => 'Not Found']));
                    }
                }
                break;
            case 'Users':
                if($params == 'Count'){
                    echo json_encode(getUsersCount());
                } elseif(gettype($params) == 'string') {
                    $temp = getUsers($params);
                    if(isset($temp)){
                        echo json_encode($temp);
                    }
                    else{
                        http_response_code(404);
                        die(json_encode(['message' => 'Not Found']));
                    }
                }
                else{
                    http_response_code(400);
                    die(json_encode(['message' => 'Bad Request']));
                }
                break;
            case 'Help':
                echo json_encode(getHelp());
                break;            
            default:
                http_response_code(400);
                die(json_encode(['message' => 'Bad Request']));
            }
            break;
        case 'POST':
            switch($type){
                case 'Users':
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
            $params = json_decode(file_get_contents('php://input'), true);
            switch($type){
                case 'Users':
                    echo json_encode(updateUser($params, $params['AdminToken'] ?? null));
                    break;
                case 'Questions':
                    echo json_encode(updateQuestion($params, $params['AdminToken'] ?? null));
                    break;
                case 'Answers':
                    echo json_encode(updateAnswer($params, $params['AdminToken'] ?? null));
                    break;
                default:
                    http_response_code(400);
                    die(json_encode(['message' => 'Bad Request']));
                }
            break;
        case 'DELETE':
            break;
        default:
            die($type.'\n'.$params);
            http_response_code(400);
            die(json_encode(['message' => 'Bad Request']));
    }
}
else {
    http_response_code(400);
    die(json_encode(['message' => 'Bad Request']));
}