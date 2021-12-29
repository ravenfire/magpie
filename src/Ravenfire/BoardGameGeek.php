<?php

namespace Ravenfire\Magpie\Ravenfire;

use Ravenfire\Magpie\Sources\AbstractSource;

class BoardGameGeek extends AbstractSource
{

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
        //@todo from scratch file
        $responses = []; //@todo get from Board Game Geek
        foreach($responses as $response)
        {
            //@todo create logs to store pieces of data that don't work as expected.
            //@todo learn how to find game
            //@todo learn how to check if it exists from games table
            //@todo learn how to create bgg instance
            //@todo learn how to create games instance
            $this->save(BoardGameGeek, Games);
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
    }

    public static function getMigrations(): array
    {
        return [
            BoardGameGeekMigration::class
        ];
    }
}