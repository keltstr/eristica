<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 16.04.14 at 10:22
 */

/** Controller for subscribing user*/
function parse_async_subscribe()
{
    $result = array('status' => '0');

    if(isset($_POST)) {
        $parse = new Parse();

        $params = array(
            'userMail' => $_POST['EMAIL'],
            'userName' => $_POST['FNAME'],
        );

        if($parse->request('https://api.parse.com/1/classes/Subscribe', 'POST', $params)){
            $result['status'] = '1';
            $result['message'] = $parse->lastResponse;
        }

    }

    return $result;
}

/** Controller for saving user question */
function parse_async_question()
{
    $result = array('status' => '0');

    if(isset($_POST)) {
        $parse = new Parse();

        $params = array(
            'userMail' => $_POST['EMAIL'],
            'question' => $_POST['QUESTION'],
        );

        if($parse->request('https://api.parse.com/1/classes/Subscribe', 'POST', $params)){
            $result['status'] = '1';
            $result['message'] = $parse->lastResponse;
        }
    }

    return $result;
}