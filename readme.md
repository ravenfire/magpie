# Magpie
Simple framework to collect and store data from various sources. Named magpie because [Magpies](https://en.wikipedia.org/wiki/Magpie) collect shiny things. Data can be shiny, too 😉

> **NOTE:** Not ready for production use. Still in very early stages.

## Desired Features
- Data agnostic framework. The user defines all sources and data structures.
- Intuitive command line interface.
- Scriptable and extensible for automation.
- Easy to integrate into cron jobs and other schedulers.
- Non destructive data persistance. See the full history of the data from all sources.
- Exhaustive audit trails and logging, all centralized, for deep analysis of data.
- Collection of tools to make gathering data easier:
  - API helpers, web scraping utilities, etc
- Store and execute sql scripts to inspect data.
- NO simple interface to view data. Use an IDE or other SQL tool.
- Probably a lot more.

## Concepts
(To be expanded as development continues)

Since we are building this for our own purposes (to collect game data), we will use games as an example. The following may not all be implemented.

- **Primary Entity**: Each application has a primary "kind" of data. This would be the `Game`. This entity holds the minimum data needed to uniquely identify what a game is. The primary entity record should be immutable and will not be updated by magpie, once a record is created
- **Source**: The place you want to gather data about a game. In our case, the first source is BoardGameGeek. Multiple sources are encouraged. Each source can be anything (excel sheets, ftp servers, apis, web scrapers, etc). Each source defines its own logic to parse the data and populates an `Eloquent` model, which is then handed to Magpie to store.
- **Model**: The Primary Entity and each Source has at least one `Eloquent` model which you populate. Magpie handles the `save()`ing.
- **Migration**: Using Laravel's Blueprints, you define the table structures as migrations, which Magpie will manage for you at the appropriate times.
- **Audit Record/Trail**: Each time a source processes some data, it populates a model and passes it to Magpie. Magpie compares that data to what already exists for that source and saves the changed data. The `Audit` record is created to describe just what changed and when.
- **Log**: When you log in magpie, the log record is stored alongside the source data and the audit and job records. Logging is inteded to tell you about broken data or other process-oriented alerts. You can customize the logging.
- **Job**: A job is kicked off anytime you run the `run` command. The job is the process of collecting and saving information. Each source record, log, and audit record is associated with a job so you can track what changed on a specific run.
- **Command**: You can (optionally) create additional commands for each source which will execute your arbitrary code.
- **SQL Scripts**: Eventually these will be prepared statements, but for now they are boilerplate scripts with the goal
  to add functionality for the user to add their own scripts as needed.

## Workflow
This section is incomplete as we are still developing the architecture. The intention is that the user will:

1. Create a class that extends `AbstractPrimaryEntity` and implement its methods
2. Create a class for each source (preferable in its own namespace) for each source that must extend `AbstractSource`. Implement the methods. Notable, the `execute()` method is what does the bulk of the work.
3. Create a migration that extends the `AbstractMigration` class and define your source and Primary Entity tables.
4. Create a model for each table that extends the Eloquent `Model`
5. Create a main entry file, configure the `Magpie` instance and register the primary entity and sources.

## Current State
Ravenfire is creating this as a mono repository for now, developing the sources and entities we are interested in. It is in an incomplete state as we build features out as we need them.

Before v1.0 we will extract our sources and entities and replace them with very simple examples. We will then open source Magpie as a framework. We may continue to open source our sources as well.

## License
MIT. See Liscence File



## Contributors

- Michael <https://github.com/electricjones>
- James <https://github.com/JamesRichards07>
