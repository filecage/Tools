<?php

    /**
     * Opens a noblock stdin stream and listens to input; also executes a tick for single-thread asynchronous processing
     *
     * @author David Beuchert
     * @package Services
     */

    namespace Tools;

    class CliHandler {

        /**
         * The stdin stream
         * @var Resource
         */
        protected $stdin;

        /**
         * A shortname that is shown on each cli input line
         * @var String
         */
        protected $shortname = '';

        /**
         * Amount of ticks
         * @var int
         */
        protected $ticks = 0;

        /**
         * Input buffering
         * @var string
         */
        protected $buffer = '';

        /**
         * Whether the CliHandler is waiting on commands or not
         * @var bool
         */
        protected $listening = true;

        /**
         * Whether incoming input should
         */

        /**
         * Opens a new stdin stream
         *
         * @return \Tools\CliHandler
         */
        public function __construct() {
            $this->stdin = fopen('php://stdin', 'r');
            echo $this->shortname . "> ";
        }

        /**
         * Checks if there is data from stdin and ticks
         *
         * @return void
         */
        public function work() {

            while ($this->stdin !== false) {

                if ($this->streamHasInput()) {
                    $this->handleInput();
                }

                $this->ticks++;
                $this->tick();

                usleep(60000);

            }

        }

        /**
         * Returns true if there is something more to read on our stream
         *
         * @return bool
         */
        protected function streamHasInput() {
            // Use variables as socket_select() expects references
            $read   = Array($this->stdin);
            $write  = null;
            $except = null;

            return (stream_select($read, $write, $except, 0) > 0 && $this->listening);
        }

        /**
         * Reads and parses data from stdin and calls CliHandler::handle() with parsed data
         *
         * @return bool
         */
        protected function handleInput() {

            $input = fread($this->stdin, 1);

            while ($input != "\n") {
                $this->buffer .= $input;

                // Check if there is more stuff to read to prevent from going into blockmode (fgetc works in a blocking way)
                if ($this->streamHasInput()) {
                    $input = fread($this->stdin, 1);
                }

            }

            // If input is a linebreak, clear buffer and proceed input line
            $line = trim($this->buffer);
            $this->buffer = '';

            // Extract parameter but stick contents inside of quotes together
            $data   = Array();
            $sticky = '';
            $quotes = false;

            foreach (str_split($line) as $char) {

                if ($char == '"' && $quotes) {
                    $data[] = $sticky;
                    $sticky = '';
                    $quotes = false;
                    continue;
                }
                elseif ($char == '"') {
                    $quotes = true;
                    continue;
                }

                if ($char == ' ' && !$quotes) {
                    if (!empty($sticky)) {
                        $data[] = $sticky;
                    }
                    $sticky = '';
                    continue;
                }

                $sticky .= $char;

            }

            // Add last sticky to data array
            if (!empty($sticky)) {
                $data[] = $sticky;
            }

            if ($this->handle(strtoupper($data[0]), $data) === false) {
                $this->log('ERROR: Command "' . strtoupper($data[0]) . '" not found');
            }

            echo "\r" . $this->shortname . "> ";

        }

        /**
         * Handles data
         * If this method returns FALSE, a command-not-found message will be written to output automatically
         *
         * @param string $command The first word of the input in upper
         * @param Array $data
         * @return bool
         */
        protected function handle($command, $data) {

            return false;

        }

        /**
         * Handles a single tick
         *
         * @return void
         */
        protected function tick() {
            $this->ticks = 0;
        }

        /**
         * Closes the stream and the process
         *
         * @return void
         */
        protected function terminate() {
            fclose($this->stdin);
            die("\r" . str_repeat(' ', (strlen($this->shortname) + 1)));
        }

        /**
         * Outputs a log message
         *
         * @param string $msg
         * @return \Tools\CliHandler
         */
        protected function log($msg) {
            echo $this->shortname . "> " . $msg . "\n";
            return $this;
        }

        /**
         * Sets listening to true or false
         *
         * @param bool $state
         * @return \Tools\CliHandler
         */
        protected function setListening($state) {
            $this->listening = $state;
            return $this;
        }

    }