<?php

class CrmUserClass {
    private $id_account = '46d74f702922';
    private $id_user = 'd20b79d92a62';
    private $token = '9b793e88a2cd4df2a2974b8b58d2a792';

    function __construct(){
        $this->urlParams = '/a/'.$this->id_account.'/u/'.$this->id_user.'/t/'.$this->token;
    }

    public function addRandomUsersToCrm(
        $count = 1,
        $birthDay = []
    ){
        $arUsers = $this->getUserArray($count,$birthDay);
        $result = $this->sendUsersToCRM($arUsers);

        return $result;
    }

    public function getUserArray($count, $birthDay){
        $arUsers = [];
        for ($i = 1; $i <= $count; $i++) {
            $user = $this->generateUser($birthDay);

            if(isset($arUsers[$i-1]['code']) && isset($arUsers[$i-2]['code'])){
                $user['code'] = $arUsers[$i-1]['code'] + $arUsers[$i-2]['code'];
            }elseif(isset($arUsers[$i-1]['code'])){
                $user['code'] = $arUsers[$i-1]['code'] + 1;
            }else{
                $user['code'] = 1;
            }


            $arUsers[$i] = $user;
        }

        return $arUsers;
    }

    public function generateUser($birthDay){
        $user = [];

        $user['name'] = $this->generateName();

        $birthDayStart = $birthDay['start'];
        $birthDayEnd = $birthDay['end'];
        $userBirthDay = $this->randomDate($birthDayStart,$birthDayEnd);
        $age = $this->getFullYears($userBirthDay);

        if($age>30){
            $sale = '7';
        }elseif ($age<31 && $age>19){
            $sale = '5';
        }else{
            $sale = '3';
        }

        $phone = rand(11111111111,99999999999);
        $user['birth_day'] = $userBirthDay;
        $user['age'] = $age;
        $user['sale'] = $sale;
        $user['phone'] = $phone;

        return $user;
    }

    public function getFullYears($birthdayDate) {
        $datetime = new DateTime($birthdayDate);
        $interval = $datetime->diff(new DateTime(date("d.m.Y")));
        return $interval->format("%Y");
    }

    public function generateName(){
        $name = [];

        $sexArr = [
            'male',
            'female'
        ];

        $sex = $sexArr[array_rand($sexArr)];

        /* я бы конечно брал лучше из бд, но думаю для демонстрации и так сойдет :) */
        $firstNameArr = [
            'male' => [
                'Аннастасия',
                'Светлана',
                'Мария',
            ],
            'female' => [
                'Александр',
                'Павел',
                'Сергей',
            ]
        ];
        $patronNameArr = [
            'male' => [
                'Сергеевна',
                'Юрьевна',
                'Александрова',
            ],
            'female' => [
                'Сергеевич',
                'Юрьевич',
                'Александрович',
            ]
        ];
        $secondNameArr = [
            'male' => [
                'Иванова',
                'Ахаматова',
                'Мирошниченко',
            ],
            'female' => [
                'Иванов',
                'Ахаматов',
                'Мирошниченко',
            ]
        ];

        $name['first_name'] = $firstNameArr[$sex][array_rand($firstNameArr[$sex])];
        $name['patron_name'] = $patronNameArr[$sex][array_rand($patronNameArr[$sex])];
        $name['second_name'] = $secondNameArr[$sex][array_rand($secondNameArr[$sex])];
        $name['sex'] = $sex;
        return $name;
    }

    public function randomDate($startDate='', $endDate='')
    {
        if(empty($endDate)) $endDate = time();

        $min = strtotime($startDate);
        $max = strtotime($endDate);

        $val = rand($min, $max);

        return date('d.m.Y', $val);
    }

    public function sendUsersToCRM($arUsers = []){

        $url = 'https://klientiks.ru/clientix/Restapi/add'.$this->urlParams.'/m/Clients/';

        foreach ($arUsers as $user){
            $servicePack = [];

            /* если хотим добавить абонемент*/
            if($user['sale']==3){
                $servicePack[] = 362117;
            }elseif ($user['sale']==5){
                $servicePack[] = 362116;
            }elseif ($user['sale']==7){
                $servicePack[] = 362114;
            }


            $data = [
                'first_name' => $user['name']['first_name'],
                'patron_name' => $user['name']['patron_name'],
                'second_name' => $user['name']['second_name'],
                'sex' => $user['name']['sex'],
                'phone' => $user['phone'],
                'birth_date' => $user['birth_day'],
                'number' => $user['code'],
            ];
            $resPost = $this->postData($data, $url);
            if(!empty($resPost['errors'])){
                //debug, etc
            }
            $result['user_'.$user['code']] = $resPost;
        }


        return $result;
    }

    private function postData($data, $url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $exec = curl_exec($ch);
        $result = json_decode($exec, true);
        curl_close($ch);
        sleep(1);
        return $result;
    }

}