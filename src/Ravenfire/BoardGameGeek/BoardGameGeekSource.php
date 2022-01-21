<?php

namespace Ravenfire\Magpie\Ravenfire\BoardGameGeek;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Ravenfire\Magpie\Ravenfire\Game\GameModel;
use Ravenfire\Magpie\Sources\AbstractSource;

/**
 * Established BoardGameGeekSource
 */
class BoardGameGeekSource extends AbstractSource
{
    /**
     * Main function of the class. Sets search parameters, makes calls to Board Game Geek API and persists appropriate
     *  values to designated tables.
     *
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
                $this->getAndSaveGameInfo($gameId, $client);
            } else {
                foreach ($games['boardgame'] as $game) {
                    $gameId = $game["@attributes"]["objectid"] . "?comments=1" . "&stats=1";
                    $this->getAndSaveGameInfo($gameId, $client);
                }
            }
        }
    }

    /**
     * Returns an array of inputs used in searching Board Game Geek's API.
     *
     * If the $exponential arguement to set to true each $input is paired with the other inputs in the $inputs array
     *  including a pair of itself. For example, ['a'] returns ['aa'] and ['b,w'] returns ['bb', 'bw', 'wb', 'ww'].
     *
     * @param array $inputs
     * @param bool $exponential
     * @return array
     */
    public function searchInfo(array $inputs, bool $exponential): array
    {
        $search = [];
        if ($exponential) {
            foreach ($inputs as $input) {
                for ($i = 0; $i < count($inputs); $i++) {
                    $search[] = $input . $inputs[$i];
                }
            }
            return $search;
        }
        return $inputs;
    }

    /**
     * Decodes xml to json.
     *
     * @param $body
     * @return mixed
     */
    public function xmlToJson($body)
    {
        $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA); //todo include simplexml in composer. Already installed on Mac's
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    /**
     * Requests game info from the Board Game Geek API, creates new objects if needed and persists the results appropriately.
     *
     * @param string $gameId
     * @param $client
     * @return false|void
     */
    public function getAndSaveGameInfo(string $gameId, $client)
    {
        $response = $client->request("GET", 'boardgame/' . $gameId);

        echo "\n";
        echo $response->getStatusCode();
        echo "\n";

        $body = $response->getBody()->getContents();
        $response = $this->xmlToJson($body);

        $responseBoardgame = $response["boardgame"];
        $essentialColumns = ["name", "boardgamepublisher", "yearpublished"];

        foreach ($essentialColumns as $essentialColumn) {
            if ($this->gameKeyChecker($responseBoardgame, $essentialColumn) and is_array($responseBoardgame[$essentialColumn])) {
                $responseBoardgame[$essentialColumn] = $responseBoardgame[$essentialColumn][0];
            } else if (!$this->gameKeyChecker($responseBoardgame, $essentialColumn)) {
                $this->notice("Essential game information missing. Skipping to next game.");
                return false;
            }
        }

        $saveNeeded = false;
        $gameKey = GameModel::gameKey($responseBoardgame['name'], $responseBoardgame['boardgamepublisher'], $responseBoardgame['yearpublished']);
        $game = SqlQueries::searchForData(GameModel, 'game_key', $gameKey);
//        $game = GameModel::where('game_key', $gameKey)->get();

        if (count($game) === 0) {
            $game = new GameModel();
            $game->game_key = $gameKey;
            $this->createNewGame($responseBoardgame, $game);
            $saveNeeded = true;
        } else {
            $this->alert("Game already exists. Checking for updates");
            $game = null;
        }

        $this->saveGameData($responseBoardgame, $gameId, $game, $saveNeeded);
    }

    /**
     * Creates new BGG game instance, populates the data, and save the primary entity and source game data.
     *
     * @param $response
     * @param $gameId
     * @param $game
     * @param $saveNeeded
     * @return void
     */
    public function saveGameData($response, $gameId, $game, $saveNeeded)
    {
        $bggColumns = [
            "playingtime" => "average_playingtime",
            "age" => "for_player_ages",
            "boardgamemechanic" => "boardgame_mechanic",
            "thumbnail" => "thumbnail",
            "image" => "image",
            "boardgamefamily" => "boardgame_family",
            "boardgamecategory" => "boardgame_category",
            "boardgamedesigner" => "boardgame_designer",
            "boardgameversion" => "boardgame_version",
            "comment" => "comments"
        ];

        $gameId = explode("?", $gameId)[0];
        $existing_model = BoardGameGeekModel::where('bgg_foreign_id', $gameId)->get();
        $boardgamegeek = new BoardGameGeekModel();

        $boardgamegeek->bgg_foreign_id = $gameId;
        $this->createNewBoardGameGeek($response, $bggColumns, $boardgamegeek);

        if (count($existing_model) !== 0) {
            $existing_model = $existing_model[0];
            $saveNeeded = true;
        } else {
            $existing_model = null;
        }

        $saveNeeded ? $this->save($boardgamegeek, $game, $existing_model, $bggColumns) : null;
    }

    /**
     * Checks to make sure essential info is not null.
     *
     * @param $response
     * @param $column
     * @return bool
     */
    public function gameKeyChecker($response, $column): bool
    {
        if (!Arr::get($response, $column)
            or $response[$column] === "0"
            or $response[$column] === null) {
            $this->notice("Essential info not provided. Skipping to next game");
            return false;
        }
        return true;
    }

    /**
     * Populates game table.
     *
     * @param $response
     * @param $game
     * @return void
     */
    public function createNewGame($response, $game){
        $gameColumns = [
            "name" => "name",
            "boardgamepublisher" => "boardgame_publisher",
            "yearpublished" => "year_published",
            "description" => "description",
            "boardgameartist" => "boardgame_artist"
        ];

        foreach ($gameColumns as $key => $value) {
            if (Arr::get($response, $key)) {
                $this->populateInfo($response[$key], $game, $value);
            }
        }
    }

    /**
     * If null values do not exist, sets key and value for a aiven table even if value is an array.
     *
     * @param $gamesInfo
     * @param object $table
     * @param string $columnName
     * @return string|void|null
     */
    public function populateInfo($gamesInfo, object $table, string $columnName)
    {
        if ($gamesInfo === "0" or $gamesInfo === null) {
            $this->alert("{$columnName} not provided for {$table}");
            return $table->$columnName = null;
        } else if (is_array($gamesInfo)) {
            //@todo Array to String conversion error exits here
            $response = implode("; ", $gamesInfo);
            return $table->$columnName = $response;
        } else {
            $table->$columnName = $gamesInfo;
        }
    }

    /**
     * Populates boardgamegeek table.
     *
     * @param $response
     * @param $bggColumns
     * @param $boardgamegeek
     * @return void
     */
    public function createNewBoardGameGeek($response, $bggColumns, $boardgamegeek){
        foreach ($bggColumns as $key => $value) { //todo lookup how to write key and values
            if (Arr::get($response, $key)) { //todo use gameColumn key
                $this->populateInfo($response[$key], $boardgamegeek, $value);
            }
        }

        if (Arr::get($response, 'minplayers') and Arr::get($response, 'maxplayers')) {
            $boardgamegeek->number_of_players =
                $this->playerCount($response["minplayers"], $response["maxplayers"], $boardgamegeek);
        } else {
            $this->alert("Number of players not provided for BGG foreign id: {$boardgamegeek -> bgg_foreign_id}");
            $boardgamegeek->number_of_players = null;
        }

        if (Arr::get($response, 'age')) {
            $boardgamegeek->for_player_ages .= "+";
        }

        $rank = Arr::get($response, 'statistics.ratings.ranks.rank');
        if (Arr::get($rank, '@attributes.value')) {
            if (Arr::get($rank, '@attributes.value') !== null and Arr::get($rank, '@attributes.value') !== "0") {
                $boardgamegeek->boardgame_rank = Arr::get($rank, '@attributes.value');
            } else {
                $this->alert("Boardgame rank not provided for BGG foreign id: {$boardgamegeek -> bgg_foreign_id}");
                $boardgamegeek->boardgame_rank = "Not ranked";
            }
        } else if (Arr::get($rank[0], '@attributes.value')) {
            if (Arr::get($rank[0], '@attributes.value') !== null and Arr::get($rank[0], '@attributes.value') !== "0") {
                $boardgamegeek->boardgame_rank = Arr::get($rank[0], '@attributes.value');
            } else {
                $this->alert("Boardgame rank not provided for BGG foreign id: {$boardgamegeek -> bgg_foreign_id}");
                $boardgamegeek->boardgame_rank = "Not ranked";
            }
        }
    }

    /**
     * String displaying how many players can play.
     *
     * @param $min
     * @param $max
     * @param $source
     * @return string
     */
    public function playerCount($min, $max, $source): string
    {
        if ($min === null || $max === null) {
            $this->alert("Player age not provided. For BGG foreign id: {$source -> bgg_foreign_id}");
            $source->number_of_players = null;
        }
        return $max === $min ? "$max players" : "$min to $max players";
    }

    public static function getPrimaryEntityJoinColumnName()
    {
        return "game_id";
    }

    /**
     * @inheritDoc
     */
    static public function getKey(): string
    {
        return "BoardGameGeek";
    }

    /**
     * @inheritDoc
     */
    static public function getModelClass(): string
    {
        return BoardGameGeekModel::class;
    }

    /**
     * @inheritDoc
     */
    public static function getMigrations(): array
    {
        return [
            BoardGameGeekMigration::class
        ];
    }
}