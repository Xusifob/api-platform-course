<?php

namespace App\Tests\Http\ResponseFactory;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class NewsResponseFactory
{

    public function __construct(private readonly string $apiKey)
    {
    }


    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        $pageSize = $options['query']['pageSize'] ?? 100;
        $page = $options['query']['page'] ?? 1;

        if ($page * $pageSize > 100) {
            return $this->returnResponse(self::getNewsErrorResponseBody(), Response::HTTP_UPGRADE_REQUIRED);
        }

        return $this->returnResponse(self::getNewsResponseBody());
    }


    private function returnResponse(?array $body, int $statusCode = Response::HTTP_OK): ResponseInterface
    {
        return new MockResponse(
            json_encode($body, JSON_PRETTY_PRINT), ['http_code' => $statusCode]
        );
    }


    public static function getNewsErrorResponseBody(): array
    {
        return [
            'status' => 'error',
            'code' => 'maximumResultsReached',
            'message' => 'You have requested too many results. Developer accounts are limited to a max of 100 results. You are trying to request results 270 to 300. Please upgrade to a paid plan if you need more results.',
        ];
    }


    public static function getNewsResponseBody(): array
    {
        return [
            'status' => 'ok',
            'totalResults' => 6008,
            'articles' => [
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Teenager reported after alleged hit-and-run in Glasgow',
                    'description' => 'A 36-year-old man was taken to hospital after being struck by a car in Glasgow city centre.',
                    'url' => 'https://www.bbc.co.uk/news/uk-scotland-glasgow-west-62786146',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/70D7/production/_126578882_highstreet0309.1_frame_2043.jpg',
                    'publishedAt' => '2022-09-04T10:10:09Z',
                    'content' => 'A teenager has been reported for a motoring offence after an alleged hit-and-run in Glasgow city centre.
Police were called to High Street at about 15:30 on Saturday after reports that a man had bee… [+452 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Ukraine war: Russia wants to destroy Europeans\' normal life, Zelensky warns',
                    'description' => 'Russia is trying to attack Europe with "poverty and political chaos", President Zelensky says.',
                    'url' => 'https://www.bbc.co.uk/news/world-europe-62786447',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/4F0E/production/_126583202_mediaitem126583201.jpg',
                    'publishedAt' => '2022-09-04T09:57:53Z',
                    'content' => 'Russia wants to destroy the normal life of every European citizen, Ukrainian President Volodymyr Zelensky has said.
"It is trying to attack with poverty and political chaos where it cannot yet attac… [+3179 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Bow stabbing: One dead and another critical after \'disturbance\'',
                    'description' => 'Police are called to "a disturbance involving a large number of people" in east London.',
                    'url' => 'https://www.bbc.co.uk/news/uk-england-london-62786363',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/16592/production/_126583519_bowstabbingbbc1.jpg',
                    'publishedAt' => '2022-09-04T09:52:16Z',
                    'content' => 'One person was stabbed to death and another seriously wounded during a violent disturbance in east London.
The Met Police was called to a fight "involving a large number of people" at Lichfield Road… [+637 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'The Big Half 2022: Mo Farah and Eilish McColgan win London half-marathon races',
                    'description' => 'Mo Farah wins the men\'s Big Half in London and Eilish McColgan wins the women\'s race in a course record.',
                    'url' => 'https://www.bbc.co.uk/sport/athletics/62785523',
                    'urlToImage' => 'https://ichef.bbci.co.uk/live-experience/cps/624/cpsprodpb/4608/production/_126582971_hi078464541.jpg',
                    'publishedAt' => '2022-09-04T09:41:33Z',
                    'content' => 'McColgan and Farah proved to be the class of their respective fields in the Big Half
Mo Farah won the men\'s Big Half in London, while Eilish McColgan set a course record to win the women\'s race.
Fo… [+1517 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Liz Truss pledges energy plan within week if elected PM',
                    'description' => 'The Tory leadership favourite says she would act immediately on bills - but gives no further details.',
                    'url' => 'https://www.bbc.co.uk/news/uk-politics-62786065',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/15A2/production/_126583550_trussbbc.png',
                    'publishedAt' => '2022-09-04T09:34:30Z',
                    'content' => 'Media caption, Liz Truss promises to act immediately on bills and energy supply
Liz Truss has promised to announce a plan to deal with soaring energy costs within a week if she becomes prime ministe… [+1728 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Rishi Sunak says he can\'t solve the issue around energy bills for everyone',
                    'description' => 'Tory leadership candidate Rishi Sunak says he would target support pensioners and those on the lowest incomes.',
                    'url' => 'https://www.bbc.co.uk/news/av/uk-politics-62785848',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/152C8/production/_126582768_p0cybcr6.jpg',
                    'publishedAt' => '2022-09-04T09:25:46Z',
                    'content' => 'Conservative leadership candidate Rishi Sunak has said he can\'t solve everyone\'s energy bills problems, but explained his plans include targeted support pensioners and those on the lowest incomes.
S… [+194 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Liam Williams: New starts and fresh challenges await Cardiff and Wales full-back',
                    'description' => 'It has been a busy summer for Liam Williams and it shows no sign of letting up for the Cardiff and Wales full-back.',
                    'url' => 'https://www.bbc.co.uk/sport/rugby-union/62746252',
                    'urlToImage' => 'https://ichef.bbci.co.uk/live-experience/cps/624/cpsprodpb/EB0E/production/_126547106_cardiff_rugby_training_002.jpg',
                    'publishedAt' => '2022-09-04T09:08:29Z',
                    'content' => 'Liam Williams has joined Cardiff along with Taulupe Faletau, Lopeti Timani and Thomas Young
It has been a busy summer for new Cardiff full-back Liam Williams and it shows no sign of letting up.
He … [+5073 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => '2022/09/04 09:00 GMT',
                    'description' => 'The latest five minute news bulletin from BBC World Service.',
                    'url' => 'https://www.bbc.co.uk/programmes/w172ykq74974sf4',
                    'urlToImage' => 'https://ichef.bbci.co.uk/images/ic/1200x675/p060dh18.jpg',
                    'publishedAt' => '2022-09-04T09:06:00Z',
                    'content' => 'The latest five minute news bulletin from BBC World Service.',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Deadly attack targets Somalia food convoy',
                    'description' => 'Al-Shabab militants claim to have carried out the attack in the country\'s central region.',
                    'url' => 'https://www.bbc.co.uk/news/world-africa-62785439',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/8B86/production/_111181753_025219997afp.jpg',
                    'publishedAt' => '2022-09-04T08:56:27Z',
                    'content' => 'At least 20 people, including women and children, have been killed and food aid destroyed after militants attacked several vehicles in Somalia\'s central Hiiraan region.
"They put a bomb while peopl… [+1698 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Wolves legend Steve Daley urges prostate cancer checks',
                    'description' => 'Steve Daley said he thought he was "invincible" until he was diagnosed with the disease.',
                    'url' => 'https://www.bbc.co.uk/news/uk-england-birmingham-62772544',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/9360/production/_126582773_steve-daley-kelly-kusinski-clare-waymont.jpg',
                    'publishedAt' => '2022-09-04T08:48:02Z',
                    'content' => 'A Wolverhampton Wanderers FC legend has encouraged men to get checked for prostate cancer after his own battle with the disease.
Former Midfielder Steve Daley had his prostate removed via robotic su… [+1775 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Liz Truss promises to act immediately on bills and energy supply',
                    'description' => 'Conservative leadership hopeful Liz Truss says she will immediately act to help people with soaring energy bills if she becomes PM.',
                    'url' => 'https://www.bbc.co.uk/news/av/uk-politics-62785847',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/104A8/production/_126582766_p0cyb71b.jpg',
                    'publishedAt' => '2022-09-04T08:30:32Z',
                    'content' => 'Conservative leadership hopeful Liz Truss has said if she is elected she will immediately act to help people with soaring energy bills.
Speaking on Sunday with Laura Kuenssberg Ms Truss said that sh… [+42 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Victoria Azarenka wants safeguarding improved for female players',
                    'description' => 'Victoria Azarenka says tennis needs to improve safeguarding to stop "vulnerable young ladies getting taken advantage of".',
                    'url' => 'https://www.bbc.co.uk/sport/tennis/62785290',
                    'urlToImage' => 'https://ichef.bbci.co.uk/live-experience/cps/624/cpsprodpb/10570/production/_126582966_gettyimages-1242922265.jpg',
                    'publishedAt' => '2022-09-04T08:26:43Z',
                    'content' => 'Belarusian Victoria Azarenka is 26th in the women\'s singles rankings
Victoria Azarenka says tennis needs to improve safeguarding to stop "vulnerable young ladies getting taken advantage of".
WTA Pl… [+1725 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Body found in search for Andy Samuel missing in dinghy',
                    'description' => 'Rescuers have been searching for the man since Thursday after he was last seen near the Isle of Rum.',
                    'url' => 'https://www.bbc.co.uk/news/uk-scotland-62785793',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/0E83/production/_115651730_breaking-promo-976.png',
                    'publishedAt' => '2022-09-04T08:20:45Z',
                    'content' => 'A body has been found during the search for a man who went missing after leaving his yacht in a dinghy in the Inner Hebrides.
Andy Samuel, 59, was last seen off Kinloch on Rum, a small island south … [+663 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Fire breaks out at Perth recycling centre',
                    'description' => 'Plumes of smoke could be seen billowing across the city after the fire took hold in the early hours.',
                    'url' => 'https://www.bbc.co.uk/news/uk-scotland-tayside-central-62785792',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/17BD6/production/_126583279_shorerecyclingfire040922stuartcowperphotography.jpg',
                    'publishedAt' => '2022-09-04T08:17:08Z',
                    'content' => 'A fire has taken hold at a recycling centre on an industrial estate in Perth .
The Scottish Fire and Rescue Service were called to Shore Recycling at the Friarton Industrial Estate at 04:30.
They b… [+178 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Hamas executes two \'Israel collaborators\' in Gaza',
                    'description' => 'The men had given Israel information which had led to the killing of Palestinians, a statement says.',
                    'url' => 'https://www.bbc.co.uk/news/world-middle-east-62785404',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/BFF2/production/_126583194_mediaitem126583193.jpg',
                    'publishedAt' => '2022-09-04T08:09:22Z',
                    'content' => 'Two Palestinian men accused of collaborating with Israel have been executed in the Gaza Strip, the Hamas-run interior ministry says.
A statement did not name the men - it only gave their initials an… [+1094 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => '2022/09/04 08:00 GMT',
                    'description' => 'The latest five minute news bulletin from BBC World Service.',
                    'url' => 'https://www.bbc.co.uk/programmes/w172ykq74974np0',
                    'urlToImage' => 'https://ichef.bbci.co.uk/images/ic/1200x675/p060dh18.jpg',
                    'publishedAt' => '2022-09-04T08:06:00Z',
                    'content' => 'The latest five minute news bulletin from BBC World Service.',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Why Serena Williams has never been defined by chase for Margaret Court\'s Grand Slam record',
                    'description' => 'As Serena Williams retires one Grand Slam short of Margaret Court\'s record, BBC Sport examines their two careers and asks what makes a GOAT.',
                    'url' => 'https://www.bbc.co.uk/sport/tennis/62708129',
                    'urlToImage' => 'https://ichef.bbci.co.uk/live-experience/cps/624/cpsprodpb/897E/production/_126489153_goat.jpg',
                    'publishedAt' => '2022-09-04T07:58:12Z',
                    'content' => 'Serena Williams is an athlete who has transcended her sport
In Serena Williams\' lengthy essay announcing her impending retirement, she addressed the inescapable record - Margaret Court\'s 24 Grand Sl… [+8254 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Scotland\'s papers: Sturgeon\'s vow on PM and boy\'s club whistleblower',
                    'description' => 'FM\'s vow to outlast Truss and an investigation into police bullying make the front pages.',
                    'url' => 'https://www.bbc.co.uk/news/uk-scotland-62785788',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/140DA/production/_126583128_composite.jpg',
                    'publishedAt' => '2022-09-04T07:41:08Z',
                    'content' => 'Trump calls Biden \'enemy of the state\' over FBI raid',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Tallaght, Dublin: Three siblings killed in \'violent incident\'',
                    'description' => 'A man has been arrested and a teenage boy and the children\'s mother are in hospital in Crumlin.',
                    'url' => 'https://www.bbc.co.uk/news/world-europe-62785466',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/3035/production/_109514321_gardagetty.jpg',
                    'publishedAt' => '2022-09-04T07:12:16Z',
                    'content' => 'Two girls and a young woman have died following "a violent incident" at a house in Tallaght, Dublin, An Garda Síochána (Irish police force) has said.
Police were called to the scene in the Rossfield… [+533 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Highland Games: Girl, five, runs 400m after crossing finish line',
                    'description' => 'Edie, from Nottingham, was meant to be taking part in a children\'s race, but kept on running.',
                    'url' => 'https://www.bbc.co.uk/news/av/uk-england-nottinghamshire-62767035',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/0CA5/production/_126573230_p0cy0pk6.jpg',
                    'publishedAt' => '2022-09-04T07:06:31Z',
                    'content' => 'A five-year-old girl who was taking part in a children\'s race continued running past the finish line - to complete a lap of the track.
Edie, from Nottingham, was taking part in a 10m (32ft) sprint f… [+631 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => '2022/09/04 07:00 GMT',
                    'description' => 'The latest five minute news bulletin from BBC World Service.',
                    'url' => 'https://www.bbc.co.uk/programmes/w172ykq74974jxw',
                    'urlToImage' => 'https://ichef.bbci.co.uk/images/ic/1200x675/p060dh18.jpg',
                    'publishedAt' => '2022-09-04T07:06:00Z',
                    'content' => 'The latest five minute news bulletin from BBC World Service.',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Scottish Gossip: Rangers, Celtic, Old Firm, Ancelotti, Ajax, Jota, Hearts, Hibernian',
                    'description' => 'Former Rangers striker Kris Boyd says Ibrox boss Giovanni van Bronckhorst now has a decision to make on his first-choice goalkeeper.',
                    'url' => 'https://www.bbc.co.uk/sport/football/62785499',
                    'urlToImage' => 'https://ichef.bbci.co.uk/live-experience/cps/624/cpsprodpb/49F0/production/_126582981_-c8c2e5fb-c6bc-4166-bdae-c835e016ca2c.png',
                    'publishedAt' => '2022-09-04T07:02:12Z',
                    'content' => 'Former Rangers striker Kris Boyd says Ibrox boss Giovanni van Bronckhorst now has a decision to make on his first-choice goalkeeper after raising doubts over Jon McLaughlin in Saturday\'s 4-0 derby de… [+1716 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Barack Obama: Emmy win for narrating Netflix documentary',
                    'description' => 'The former president\'s five-part Netflix documentary series nabs an Emmy for best narrator.',
                    'url' => 'https://www.bbc.co.uk/news/entertainment-arts-62785347',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/D244/production/_126582835_gettyimages-1229382449.jpg',
                    'publishedAt' => '2022-09-04T06:54:45Z',
                    'content' => 'Former president Barack Obama won the best narrator Emmy Award on Saturday for his Netflix documentary series, Our Great National Parks.
The Obamas\' production company, Higher Ground, is behind the … [+1095 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'Tyson Fury says he will announce his next opponent next week - but it won\'t be Usyk',
                    'description' => 'WBC heavyweight champion Tyson Fury, who insisted he had retired, says he will announce his next opponent next week.',
                    'url' => 'https://www.bbc.co.uk/sport/boxing/62785297',
                    'urlToImage' => 'https://ichef.bbci.co.uk/live-experience/cps/624/cpsprodpb/AF1C/production/_126582844_gettyimages-1394160446.jpg',
                    'publishedAt' => '2022-09-04T06:44:58Z',
                    'content' => 'Fury said he had retired after beating Dillian Whyte at Wembley in April but still holds the WBC belt
WBC heavyweight champion Tyson Fury says he will announce his next opponent next week - but conf… [+1263 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Lingdale shop owner\'s wall of £2,000 \'pay later\' receipts',
                    'description' => 'Abid Hussain says most customers eventually pay up, but the cost of living crisis is getting worse.',
                    'url' => 'https://www.bbc.co.uk/news/uk-england-tees-62780299',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/7523/production/_126578992_mediaitem126578532.jpg',
                    'publishedAt' => '2022-09-04T06:10:22Z',
                    'content' => 'A shop owner is allowing his struggling customers to take essentials from his convenience store and pay for them when they can afford to.
Abid Hussain, who runs Family Mart in Lingdale, has hundred… [+1356 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => '2022/09/04 06:00 GMT',
                    'description' => 'The latest five minute news bulletin from BBC World Service.',
                    'url' => 'https://www.bbc.co.uk/programmes/w172ykq74974f5r',
                    'urlToImage' => 'https://ichef.bbci.co.uk/images/ic/1200x675/p060dh18.jpg',
                    'publishedAt' => '2022-09-04T06:06:00Z',
                    'content' => 'The latest five minute news bulletin from BBC World Service.',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => 'The Hundred: Positives persist but organisers still have puzzles to solve',
                    'description' => 'The Hundred can take more positives out of its 2022 edition, but there remain significant puzzles to solve if it is to be a sustained success.',
                    'url' => 'https://www.bbc.co.uk/sport/cricket/62783877',
                    'urlToImage' => 'https://ichef.bbci.co.uk/live-experience/cps/624/cpsprodpb/5472/production/_126581612_-dd1a7cf5-2cdf-4e61-a19a-e4e5df69b17b.png',
                    'publishedAt' => '2022-09-04T05:50:12Z',
                    'content' => 'Second-season syndrome has been the scourge of a many a Premier League manager.
After impressing in your first year on the big stage, how do you come back, consolidate and even improve?
This year T… [+5703 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => '2022/09/04 05:00 GMT',
                    'description' => 'The latest five minute news bulletin from BBC World Service.',
                    'url' => 'https://www.bbc.co.uk/programmes/w172ykq749749fm',
                    'urlToImage' => 'https://ichef.bbci.co.uk/images/ic/1200x675/p060dh18.jpg',
                    'publishedAt' => '2022-09-04T05:06:00Z',
                    'content' => 'The latest five minute news bulletin from BBC World Service.',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => 'https://www.facebook.com/bbcnews',
                    'title' => 'Donald Trump: What we learned from his rally in Pennsylvania',
                    'description' => 'The ex-president spoke for two hours at the first rally since the search - here is what we learned.',
                    'url' => 'https://www.bbc.co.uk/news/world-us-canada-62761333',
                    'urlToImage' => 'https://ichef.bbci.co.uk/news/1024/branded_news/CD30/production/_126582525_1a1d6549ac195b8190efe378c00cd2c69483be540_0_2761_18371000x665.jpg',
                    'publishedAt' => '2022-09-04T04:15:20Z',
                    'content' => 'By Kayla Epsteinin Wilkes-Barre, Pennsylvania
Donald Trump has called President Joe Biden an "enemy of the state" at his first rally since the FBI searched his Florida resort for sensitive files.
S… [+6658 chars]',
                ],
                [
                    'source' => [
                        'id' => 'bbc-news',
                        'name' => 'BBC News',
                    ],
                    'author' => null,
                    'title' => '2022/09/04 04:00 GMT',
                    'description' => 'The latest five minute news bulletin from BBC World Service.',
                    'url' => 'https://www.bbc.co.uk/programmes/w172ykq749745ph',
                    'urlToImage' => 'https://ichef.bbci.co.uk/images/ic/1200x675/p060dh18.jpg',
                    'publishedAt' => '2022-09-04T04:06:00Z',
                    'content' => 'The latest five minute news bulletin from BBC World Service.',
                ],
            ],
        ];
    }


}
