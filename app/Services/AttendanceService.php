<?php

namespace App\Services;

use App\Models\Cookie;
use GuzzleHttp\Client;
use App\Models\GameUser;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class AttendanceService
{
    protected $client;
    protected $cookieJar;

    public function __construct()
    {
        $this->cookieJar = new CookieJar;
        $this->client = new Client(['cookies' => $this->cookieJar]);
    }

    public function login(GameUser $gameUser)
    {
        $response = $this->client->post('https://passport.icantw.com/login.php', [
            'form_params' => [
                'LoginForm[username]' => $gameUser->username,
                'LoginForm[password]' => $gameUser->password,
                'LoginForm[rememberMe]' => '0',
            ]
        ]);

        // 假設服務器在成功登入後返回一個cookie
        if ($response->getBody()->getContents() === '1') {
            // 保存cookie以供後續請求使用
            $this->cookieJar->setCookie(new SetCookie($response->getHeader('Set-Cookie')));

            // 将 cookie 保存到数据库中
            $gameUser->cookies = serialize($this->cookieJar);
            $gameUser->save();
        }
    }

    public function loginWtihPool(array $gameUsers)
    {
        $response = $this->client->post('https://passport.icantw.com/login.php', [
            'form_params' => [
                'LoginForm[username]' => $gameUser->username,
                'LoginForm[password]' => $gameUser->password,
                'LoginForm[rememberMe]' => '0',
            ]
        ]);

        // 假設服務器在成功登入後返回一個cookie
        if ($response->getBody()->getContents() === '1') {
            $gameUser->cookies = serialize($this->cookieJar);
            $gameUser->save();
        }
    }

    public function checkIn()
    {
        // 从数据库中获取cookie
        $cookie = Cookie::latest()->first();

        if ($cookie) {
            $this->cookieJar = unserialize($cookie->cookies);

            // 使用已保存的cookie發送打卡請求
            $response = $this->client->post('https://www.icantw.com/event/2023/DragonBoatFestival/CheckIn', [
                'cookies' => $this->cookieJar
            ]);

            return $response->getBody()->getContents();
        } else {
            throw new \Exception('No cookie found');
        }
    }
}
