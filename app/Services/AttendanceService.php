<?php

namespace App\Services;

use GuzzleHttp\Pool;
use App\Models\Cookie;
use GuzzleHttp\Client;
use App\Models\GameUser;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class AttendanceService
{
    protected $client;
    protected $cookieJar;

    public $giftInfos = [
        's' => [
            'gameid' => '127',
            'type' => '2',
            'pay' => '200',
        ],
        'm' => [
            'gameid' => '127',
            'type' => '4',
            'pay' => '400',
        ],
        'l' => [
            'gameid' => '127',
            'type' => '6',
            'pay' => '600',
        ],
    ];

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

        if ($response->getBody()->getContents() === '1') {
            $this->cookieJar->setCookie(new SetCookie($response->getHeader('Set-Cookie')));

            $gameUser->cookies = serialize($this->cookieJar);
            $gameUser->save();
        }
    }

    public function loginWtihPool(Collection $users)
    {
        $client = new Client;

        $requests = function ($users) use ($client) {
            $uri = 'https://passport.icantw.com/login.php';

            foreach ($users as $user) {
                yield function() use ($client, $uri, $user) {
                    return $client->postAsync($uri, [
                        'form_params' => [
                            'LoginForm[username]' => $user->username,
                            'LoginForm[password]' => $user->password,
                            'LoginForm[rememberMe]' => '0',
                        ],
                    ]);
                };
            }
        };

        $pool = new Pool($client, $requests($users), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use ($users) {
                $headerSetCookies = $response->getHeader('Set-Cookie');

                $cookies = [];
                foreach ($headerSetCookies as $key => $header) {
                    $cookie = SetCookie::fromString($header);
                    $cookie->setDomain('.icantw.com');

                    $cookies[] = $cookie;
                }

                $cookieJar = new CookieJar(false, $cookies);

                $users[$index]->cookies = serialize($cookieJar);
                $users[$index]->save();
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    public function searchCoinInfo(Collection $users)
    {
        $client = new Client;

        $requests = function ($users) use ($client) {
            $uri = 'https://www.icantw.com/event/2023/DragonBoatFestival/SearchcoinlInfo';

            foreach ($users as $user) {
                yield function() use ($client, $uri, $user) {
                    $cookies = unserialize($user->cookies);

                    return $client->postAsync($uri, [
                        'cookies' => $cookies
                    ]);
                };
            }
        };

        $pool = new Pool($client, $requests($users), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use ($users) {
                $coinInfo = collect(json_decode($response->getBody()->getContents())->coinArr);

                $users[$index]->coin_info = $coinInfo->sum('coin');
                $users[$index]->save();
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    public function getGift(Collection $users)
    {
        $client = new Client;

        $requests = function ($users) use ($client) {
            $uri = 'https://www.icantw.com/event/2023/DragonBoatFestival/GetGiftInfo';

            foreach ($users as $user) {
                yield function() use ($client, $uri, $user) {
                    $cookies = unserialize($user->cookies);

                    return $client->postAsync($uri, [
                        'form_params' => $this->giftInfos['m'],
                        'cookies' => $cookies
                    ]);
                };
            }
        };

        $pool = new Pool($client, $requests($users), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) {
                //
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    public function searchCodeInfo(Collection $users)
    {
        $client = new Client;

        $requests = function ($users) use ($client) {
            $uri = 'https://www.icantw.com/event/2023/DragonBoatFestival/SearchCodeInfo';

            foreach ($users as $user) {
                yield function() use ($client, $uri, $user) {
                    $cookies = unserialize($user->cookies);

                    return $client->postAsync($uri, [
                        'cookies' => $cookies
                    ]);
                };
            }
        };

        $pool = new Pool($client, $requests($users), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use ($users) {                ;
                $users[$index]->code_info = json_decode($response->getBody()->getContents())->codearr;
                $users[$index]->save();
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    public function checkIn(Collection $users)
    {
        $client = new Client;

        $requests = function ($users) use ($client) {
            $uri = 'https://www.icantw.com/event/2023/DragonBoatFestival/CheckIn';

            foreach ($users as $user) {
                yield function() use ($client, $uri, $user) {
                    $cookies = unserialize($user->cookies);

                    return $client->postAsync($uri, [
                        'cookies' => $cookies
                    ]);
                };
            }
        };

        $pool = new Pool($client, $requests($users), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) {
                //
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }
}
