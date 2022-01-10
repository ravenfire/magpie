<?php

namespace Ravenfire\Magpie\Ravenfire;

use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Table;
use Ravenfire\Magpie\Ravenfire\Game;
use Ravenfire\Magpie\Sources\AbstractSource;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;

class BoardGameGeek extends AbstractSource
{
    public function searchInfo(array $inputs, bool $exponential): array {
        $search = [];
        if($exponential){
            foreach ($inputs as $input) {
                for ($i = 0; $i < count($inputs); $i++) {
                    $search[] = $input . $inputs[$i];
                }
            }
            return $search;
        }
        return $inputs;
    }

    public function populateInfo($gamesInfo, object $table, string $columnName){
        if($gamesInfo === "0" or $gamesInfo === null){
            $this->alert("{$columnName} not provided for {$table}");
            return $table->$columnName = null;
        }
        else if(is_array($gamesInfo)){
            $response = implode("; ", $gamesInfo);
            return $table->$columnName = $response;
        }
        else{
            $table->$columnName = $gamesInfo;
        }
    }

    public function gameKeyChecker($response, $column): bool
    {
        if (!Arr::get($response, $column)
            or $response[$column] === "0"
            or $response[$column] === null) {
            BoardGameGeek::notice("Essential info not provided. Skipping to next game");
            return false;
        }
        return true;
    }

    public function saveGameInfo(string $gameId, $client)
    {
        $response = $client->request("GET", 'boardgame/' . $gameId);

        echo "\n";
        echo $response->getStatusCode();
        echo "\n";

        $body = $response->getBody()->getContents();
        $response = $this->xmlToJson($body);

        $responseBoardgame = $response["boardgame"];
        $essentialColumns = ["name", "boardgamepublisher", "yearpublished"];

        foreach($essentialColumns as $essentialColumn){
            if($this->gameKeyChecker($responseBoardgame, $essentialColumn) and is_array($responseBoardgame[$essentialColumn])){
                $responseBoardgame[$essentialColumn] = $responseBoardgame[$essentialColumn][0];
            }
            else if(!$this->gameKeyChecker($responseBoardgame, $essentialColumn)) {
                return false;
            }
        }

        $gameKey = Game::gameKey($responseBoardgame['name'], $responseBoardgame['boardgamepublisher'], $responseBoardgame['yearpublished']);
        $game = Game::where('game_key', $gameKey)->get();
        if(count($game) === 0){
            $game = new Game();
            $game->game_key = $gameKey;
        }else{
            BoardGameGeek::notice("Game already exists. Skipping to next game");
            return false;
        }

        //Game
        $gameColumns = [
            "name"=>"name",
            "boardgamepublisher"=>"boardgame_publisher",
            "yearpublished"=>"year_published",
            "description"=>"description",
            "boardgameartist"=>"boardgame_artist"];

        foreach($gameColumns as $key => $value){ //todo lookup how to write key and values
            if(Arr::get($response["boardgame"], $key)){ //todo use gameColumn key
                $this->populateInfo($response["boardgame"][$key], $game, $value);
            }
        }

        //BGG
        $boardgamegeek = BoardGameGeekModel::where('bgg_foreign_id', $gameId)->get();
        if(count($boardgamegeek) === 0){
            $boardgamegeek = new BoardGameGeekModel();
        }else{
            BoardGameGeek::notice("Game already exists. Skipping to next game");
            return false;
        }

        $boardgamegeek->bgg_foreign_id = explode("?",$gameId)[0];

        $bggColumns = [
            "playingtime"=>"average_playingtime",
            "age"=>"for_player_ages",
            "boardgamemechanic"=>"boardgame_mechanic",
            "thumbnail"=>"thumbnail",
            "image"=>"image",
            "boardgamefamily"=>"boardgame_family",
            "boardgamecategory"=>"boardgame_category",
            "boardgamedesigner"=>"boardgame_designer",
            "boardgameversion"=>"boardgame_version",
            "comment"=>"comments"];

        foreach($bggColumns as $key=>$value){ //todo lookup how to write key and values
            if(Arr::get($response['boardgame'], $key)){ //todo use gameColumn key
                $this->populateInfo($response["boardgame"][$key], $boardgamegeek, $value);
            }
        }

        if (Arr::get($response, 'boardgame.minplayers') and Arr::get($response, 'boardgame.maxplayers')) {
            $boardgamegeek->number_of_players =
                BoardGameGeek::playerCount($response["boardgame"]["minplayers"], $response["boardgame"]["maxplayers"], $boardgamegeek);
        } else {
            BoardGameGeek::alert("Number of players not provided for BGG foreign id: {$boardgamegeek -> bgg_foreign_id}");
            $boardgamegeek->number_of_players = null;
        }

        if (Arr::get($response, 'boardgame.age')) {
            $boardgamegeek->for_player_ages .= "+";
        }

        $rank = Arr::get($response, 'boardgame.statistics.ratings.ranks.rank');
        if (Arr::get($rank, '@attributes.value')){
            if (Arr::get($rank, '@attributes.value') !== null and Arr::get($rank, '@attributes.value') !== "0") {
                $boardgamegeek->boardgame_rank = Arr::get($rank, '@attributes.value');
            }else{
                BoardGameGeek::alert("Boardgame rank not provided for BGG foreign id: {$boardgamegeek -> bgg_foreign_id}");
                $boardgamegeek->boardgame_rank = "Not ranked";
            }
        }else if(Arr::get($rank[0], '@attributes.value')) {
            if (Arr::get($rank[0], '@attributes.value') !== null and Arr::get($rank[0], '@attributes.value') !== "0") {
                $boardgamegeek->boardgame_rank = Arr::get($rank[0], '@attributes.value');
            } else {
                BoardGameGeek::alert("Boardgame rank not provided for BGG foreign id: {$boardgamegeek -> bgg_foreign_id}");
                $boardgamegeek->boardgame_rank = "Not ranked";
            }
        }

        BoardGameGeek::save($boardgamegeek, $game);
    }

    public function xmlToJson($body)
    {
        $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA); //todo include simplexml in composer. Already installed on Mac's
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    public function playerCount($min, $max, $source): string
    {
        if ($min === null || $max === null) {
            $this->alert("Player age not provided. For BGG foreign id: {$source -> bgg_foreign_id}");
            $source->number_of_players = null;
        }
        return $max === $min ? "$max players" : "$min to $max players";
    }

    /**
     * @inheritDoc
     */
    static public function getKey(): string
    {
        return "BoardGameGeek";
    }

    static public function getModelClass(): string
    {
        return BoardGameGeekModel::class;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->alert("Beginning BGG.");

        $client = new Client([
            'base_uri' => 'https://www.boardgamegeek.com/xmlapi/',
            'timeout' => 150.0,
        ]);

        $alphabetLibrary = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

        $searches = $this->searchInfo($alphabetLibrary, true);
//        $searches = $this->searchInfo(["br"], false);

        foreach ($searches as $search) {
            var_dump($search);
            $response = $client->request("GET", 'search', [
                "query" => ['search' => $search]
            ]);

            echo "\n";
            echo $response->getStatusCode();
            echo "\n";

            $body = $response->getBody()->getContents();
            $games = $this->xmlToJson($body);

            if (Arr::get($games, 'boardgame.@attributes')) {
                $gameId = $games["boardgame"]["@attributes"]["objectid"] . "?comments=1" . "&stats=1";
                $this->saveGameInfo($gameId, $client);
            } else {
                foreach ($games['boardgame'] as $game) {
                    $gameId = $game["@attributes"]["objectid"] . "?comments=1" . "&stats=1";
                    $this->saveGameInfo($gameId, $client);
                }
            }
        }
    }

//    public function debug($message, array $context = array())
//    public function info($message, array $context = array())
//    public function notice($message, array $context = array())
//    public function warning($message, array $context = array())
//    public function error($message, array $context = array())
//    public function alert($message, array $context = array())
//    public function critical($message, array $context = array())
//    public function emergency($message, array $context = array())


        //        Manager::schema()->create(static::getTableName(), function (Blueprint $table) {
//            $table->id();
//            $table->integer('game_id');
//            $table->foreign('game_id')->references('id')->on('games');
//            $table->integer('bgg_foreign_id');
//            $table->integer('number_of_players');
//            $table->integer('for_player_ages');
//            $table->integer('average_playtime');
//            $table->string('boardgame_mechanic');
////            $table->('images');
////            $table->('thumbnail');
////            $table->string('boardgame_family');
//            $table->string('boardgame_designer');
//            $table->string('boardgame_version');
//            $table->string('boardgame_implementation');
////            $table->string('poll');
//            $table->string('comments');
////            $table->string('statistics');
//            $table->timestamps();
//        });

//        protected function execute(InputInterface $input, OutputInterface $output): int
//    {
//        $this->getContext()->getLogger()->pushHandler(new ConsoleHandler($output)); //@todo intialize logger like other commands
//
//        $job = new Job();
//        $job->name = Job::createName(); // @todo: all this to be passed in
//        $job->save();
//
//        foreach ($this->getContext()->getAllSources() as $source) {
//            $source->run($job, $output);
//        }
//
//        $this->getContext()->getLogger()->info("Done");
//
//        return Command::SUCCESS;
//    }

    public static function getMigrations(): array
    {
        return [
            BoardGameGeekMigration::class
        ];
    }
}